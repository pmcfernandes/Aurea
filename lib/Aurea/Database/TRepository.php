<?php
declare(strict_types=1);

namespace Aurea\Database;

use PDO;

/**
 * TRepository — carrega coleções de Active Records.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TRepository
{
    protected string $class;

    /**
     * Cria o repositório para uma classe de Active Record.
     *
     * @param string $class Classe que estende TRecord
     */
    public function __construct(string $class)
    {
        if (!is_subclass_of($class, TRecord::class)) {
            throw new \InvalidArgumentException("{$class} deve estender Record");
        }
        $this->class = $class;
    }

    /** @return TRecord[] */

    public function load(?TCriteria $criteria = null, bool $callObjectLoad = true, bool $withTrashed = false): array
    {
        $conn = TTransaction::get();
        $table = $this->class::TABLENAME;
        $where = '';
        $params = [];
        $tail = '';
        if ($criteria) {
            [$where, $params] = $criteria->dump();
            $tail = $criteria->orderClause() . $criteria->limitClause();
        }
        if (!$withTrashed && $this->class::SOFT_DELETE && $this->class::DELETED_AT) {
            $clause = $this->class::DELETED_AT . ' IS NULL';
            $where = $where === '' ? 'WHERE ' . $clause : $where . ' AND ' . $clause;
        }
        $sql = "SELECT * FROM {$table} {$where} {$tail}";
        TTransaction::log($sql, $params);
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $out = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $out[] = new $this->class($row, $callObjectLoad);
        }
        return $out;
    }

    /**
     * Conta os registros do critério.
     *
     * @param TCriteria|null $criteria Critério de filtro ou null
     * @return int Total de registros
     * @throws \Exception Se não houver transação aberta
     */
    public function count(?TCriteria $criteria = null, bool $withTrashed = false): int
    {
        $conn = TTransaction::get();
        $table = $this->class::TABLENAME;
        $where = '';
        $params = [];
        if ($criteria) {
            [$where, $params] = $criteria->dump();
        }
        if (!$withTrashed && $this->class::SOFT_DELETE && $this->class::DELETED_AT) {
            $clause = $this->class::DELETED_AT . ' IS NULL';
            $where = $where === '' ? 'WHERE ' . $clause : $where . ' AND ' . $clause;
        }
        $sql = "SELECT COUNT(*) AS c FROM {$table} {$where}";
        TTransaction::log($sql, $params);
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Apaga os registros do critério.
     *
     * @param TCriteria|null $criteria Critério de filtro ou null
     * @return int Quantidade de registros apagados
     * @throws \Exception Se não houver transação aberta
     */
    public function delete(?TCriteria $criteria = null): int
    {
        $conn = TTransaction::get();
        $table = $this->class::TABLENAME;
        $where = '';
        $params = [];
        if ($criteria) {
            [$where, $params] = $criteria->dump();
        }
        if ($this->class::SOFT_DELETE && $this->class::DELETED_AT) {
            $column = $this->class::DELETED_AT;
            $where = $where === '' ? 'WHERE ' . $column . ' IS NULL' : $where . ' AND ' . $column . ' IS NULL';
            $sql = "UPDATE {$table} SET {$column} = ? {$where}";
            array_unshift($params, date('Y-m-d H:i:s'));
        } else {
            $sql = "DELETE FROM {$table} {$where}";
        }
        TTransaction::log($sql, $params);
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    /**
     * Devolve o primeiro registro do critério.
     *
     * @param TCriteria|null $criteria Critério de filtro ou null
     * @return TRecord|null Primeiro registro ou null
     */
    public function first(?TCriteria $criteria = null): ?TRecord
    {
        $criteria ??= new TCriteria;
        $criteria->setProperty('limit', 1);
        $rows = $this->load($criteria);
        return $rows[0] ?? null;
    }

    /**
     * Executa uma função de agregação SQL sobre uma coluna.
     *
     * @param string $fn Função SQL (SUM, AVG, MIN, MAX)
     * @param string $column Coluna agregada
     * @param TCriteria|null $criteria Critério de filtro ou null
     * @return float|int|null Resultado da agregação ou null
     */
    protected function aggregate(string $fn, string $column, ?TCriteria $criteria = null): float|int|null
    {
        $conn = TTransaction::get();
        $table = $this->class::TABLENAME;
        $where = '';
        $params = [];
        if ($criteria) {
            [$where, $params] = $criteria->dump();
        }
        if ($this->class::SOFT_DELETE && $this->class::DELETED_AT) {
            $clause = $this->class::DELETED_AT . ' IS NULL';
            $where = $where === '' ? 'WHERE ' . $clause : $where . ' AND ' . $clause;
        }
        $sql = "SELECT {$fn}({$column}) AS v FROM {$table} {$where}";
        TTransaction::log($sql, $params);
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $value = $stmt->fetchColumn();
        return $value === false || $value === null ? null : (is_numeric($value) && !str_contains((string) $value, '.') ? (int) $value : (float) $value);
    }

    /**
     * Soma os valores de uma coluna.
     *
     * @param string $column Coluna a somar
     * @param TCriteria|null $criteria Critério de filtro ou null
     * @return float|int|null Soma ou null
     */
    public function sum(string $column, ?TCriteria $criteria = null): float|int|null
    {
        return $this->aggregate('SUM', $column, $criteria);
    }

    /**
     * Média dos valores de uma coluna.
     *
     * @param string $column Coluna calculada
     * @param TCriteria|null $criteria Critério de filtro ou null
     * @return float|int|null Média ou null
     */
    public function avg(string $column, ?TCriteria $criteria = null): float|int|null
    {
        return $this->aggregate('AVG', $column, $criteria);
    }

    /**
     * Valor mínimo de uma coluna.
     *
     * @param string $column Coluna pesquisada
     * @param TCriteria|null $criteria Critério de filtro ou null
     * @return float|int|null Mínimo ou null
     */
    public function min(string $column, ?TCriteria $criteria = null): float|int|null
    {
        return $this->aggregate('MIN', $column, $criteria);
    }

    /**
     * Valor máximo de uma coluna.
     *
     * @param string $column Coluna pesquisada
     * @param TCriteria|null $criteria Critério de filtro ou null
     * @return float|int|null Máximo ou null
     */
    public function max(string $column, ?TCriteria $criteria = null): float|int|null
    {
        return $this->aggregate('MAX', $column, $criteria);
    }

    /**
     * Paginação simples.
     *
     * @return array{data:TRecord[],total:int,page:int,per_page:int,last_page:int}
     */
    public function paginate(?TCriteria $criteria = null, int $page = 1, int $perPage = 10): array
    {
        $page = max(1, $page);
        $total = $this->count($criteria);
        $criteria = $criteria ? clone $criteria : new TCriteria;
        $perPage = max(1, $perPage);
        $criteria->setProperty('limit', $perPage);
        $criteria->setProperty('offset', ($page - 1) * $perPage);
        $data = $this->load($criteria);
        return [
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'last_page' => (int) max(1, ceil($total / max(1, $perPage))),
        ];
    }

    /**
     * Itera os registos sem carregá-los todos em memória.
     *
     * O cursor PDO permanece aberto até o gerador ser totalmente consumido.
     * Mantenha a transação aberta durante a iteração.
     *
     * @return \Generator<int,TRecord>
     */
    public function cursor(?TCriteria $criteria = null, bool $callObjectLoad = true, bool $withTrashed = false): \Generator
    {
        $conn = TTransaction::get();
        $table = $this->class::TABLENAME;
        $where = '';
        $params = [];
        $tail = '';
        if ($criteria) {
            [$where, $params] = $criteria->dump();
            $tail = $criteria->orderClause() . $criteria->limitClause();
        }
        if (!$withTrashed && $this->class::SOFT_DELETE && $this->class::DELETED_AT) {
            $clause = $this->class::DELETED_AT . ' IS NULL';
            $where = $where === '' ? 'WHERE ' . $clause : $where . ' AND ' . $clause;
        }
        $sql = "SELECT * FROM {$table} {$where} {$tail}";
        TTransaction::log($sql, $params);
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        while (($row = $stmt->fetch(PDO::FETCH_ASSOC)) !== false) {
            yield new $this->class($row, $callObjectLoad);
        }
        $stmt->closeCursor();
    }

    /**
     * Executa um callback por blocos de registos paginados.
     *
     * O callback recebe a lista do bloco e o número do bloco (iniciado em 1).
     * A consulta é repetida com LIMIT/OFFSET; para conjuntos em alteração,
     * prefira um critério ordenado por uma chave imutável ou cursor().
     *
     * @param callable(TRecord[],int):void $callback
     */
    public function chunk(int $size, callable $callback, ?TCriteria $criteria = null, bool $callObjectLoad = true, bool $withTrashed = false): void
    {
        if ($size < 1) {
            throw new \InvalidArgumentException('O tamanho do bloco deve ser maior que zero.');
        }
        $offset = 0;
        $page = 1;
        do {
            $chunkCriteria = $criteria ? clone $criteria : new TCriteria;
            $chunkCriteria->setProperty('limit', $size);
            $chunkCriteria->setProperty('offset', $offset);
            $rows = $this->load($chunkCriteria, $callObjectLoad, $withTrashed);
            if ($rows === []) {
                break;
            }
            $callback($rows, $page++);
            $offset += count($rows);
        } while (count($rows) === $size);
    }
}
