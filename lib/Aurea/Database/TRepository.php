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

    public function load(?TCriteria $criteria = null, bool $callObjectLoad = true): array
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
    public function count(?TCriteria $criteria = null): int
    {
        $conn = TTransaction::get();
        $table = $this->class::TABLENAME;
        $where = '';
        $params = [];
        if ($criteria) {
            [$where, $params] = $criteria->dump();
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
        $sql = "DELETE FROM {$table} {$where}";
        TTransaction::log($sql, $params);
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }
}
