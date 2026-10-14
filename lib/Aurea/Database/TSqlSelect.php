<?php
declare(strict_types=1);

namespace Aurea\Database;

use PDO;

/**
 * TSqlSelect — construtor de instruções SELECT com prepared statements.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TSqlSelect
{
    /** @var string Nome da tabela */
    protected string $entity = '';

    /** @var string[] Colunas a seleccionar */
    protected array $columns = [];

    /** @var TCriteria|null Critério de filtro/ordenação */
    protected ?TCriteria $criteria = null;

    /** @var string[] Junções SQL brutas */
    protected array $joins = [];
    protected bool $distinct = false;
    protected string $group = '';
    protected string $having = '';
    protected array $havingParams = [];

    /**
     * Define a tabela da consulta.
     *
     * @param string $table Nome da tabela
     * @return static
     */
    public function setEntity(string $table): static
    {
        $this->entity = TIdentifier::assert($table);
        return $this;
    }

    /**
     * Adiciona uma coluna à lista de colunas.
     *
     * @param string $col Nome da coluna
     * @return static
     */
    public function addColumn(string $col): static
    {
        $this->columns[] = $col;
        return $this;
    }

    /**
     * Define o critério da consulta.
     *
     * @param TCriteria $c Critério com filtros/ordenação/limite
     * @return static
     */
    public function setCriteria(TCriteria $c): static
    {
        $this->criteria = $c;
        return $this;
    }

    /**
     * Devolve a instrução SQL com placeholders.
     *
     * @return string Instrução SELECT
     * @throws \RuntimeException Se a tabela não estiver definida
     */
    public function getSql(): string
    {
        if ($this->entity === '') {
            throw new \RuntimeException('Tabela não definida em SqlSelect. Utilize setEntity().');
        }
        $cols = $this->columns ? implode(', ', $this->columns) : '*';
        if ($this->criteria !== null && $this->criteria->getColumns() && !$this->columns) {
            $cols = implode(', ', $this->criteria->getColumns());
        }
        $distinct = ($this->distinct || ($this->criteria !== null && !empty($this->criteria->getProperty('distinct')))) ? 'DISTINCT ' : '';
        $sql = "SELECT {$distinct}{$cols} FROM {$this->entity}";
        if ($this->joins) {
            $sql .= ' ' . implode(' ', $this->joins);
        }
        if ($this->criteria !== null) {
            [$where] = $this->criteria->dump();
            if ($where !== '') {
                $sql .= " {$where}";
            }
            $sql .= $this->criteria->groupClause();
            [$having, ] = $this->criteria->havingClause();
            if ($having === '' && $this->having !== '') {
                $sql .= ' HAVING ' . $this->having;
            } else {
                $sql .= $having;
            }
            $sql .= $this->criteria->orderClause();
            $sql .= $this->criteria->limitClause();
        } else {
            if ($this->group !== '') {
                $sql .= " GROUP BY {$this->group}";
            }
            if ($this->having !== '') {
                $sql .= ' HAVING ' . $this->having;
            }
        }
        return $sql;
    }

    /**
     * Adiciona JOIN bruto (ex: "LEFT JOIN cidade c ON c.id = cliente.cidade_id").
     */
    public function addJoin(string $join): static
    {
        $this->joins[] = $join;
        return $this;
    }

    /**
     * Ativa SELECT DISTINCT.
     *
     * @param bool $distinct True para registos distintos
     * @return static
     */
    public function setDistinct(bool $distinct = true): static
    {
        $this->distinct = $distinct;
        return $this;
    }

    /**
     * Define o GROUP BY da consulta.
     *
     * @param string $group Colunas de agrupamento
     * @return static
     */
    public function setGroup(string $group): static
    {
        $this->group = $group;
        return $this;
    }

    /**
     * Define o HAVING da consulta.
     *
     * @param string $having Expressão HAVING com placeholders
     * @param array $params Parâmetros do HAVING
     * @return static
     */
    public function setHaving(string $having, array $params = []): static
    {
        $this->having = $having;
        $this->havingParams = $params;
        return $this;
    }

    /**
     * Devolve os parâmetros do prepared statement.
     *
     * @return array Parâmetros na ordem dos placeholders
     */
    public function getParams(): array
    {
        $params = [];
        if ($this->criteria !== null) {
            [, $params] = $this->criteria->dump();
            [, $havingParams] = $this->criteria->havingClause();
            $params = array_merge($params, $havingParams);
        }
        return array_merge($params, $this->havingParams);
    }

    /**
     * Executa a consulta e devolve todas as linhas.
     *
     * @param PDO $conn Ligação PDO
     * @return array Linhas encontradas
     */
    public function fetchAll(PDO $conn): array
    {
        $sql = $this->getSql();
        $params = $this->getParams();
        TTransaction::log($sql, $params);
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Executa a consulta e devolve a primeira linha.
     *
     * @param PDO $conn Ligação PDO
     * @return array|null Primeira linha ou null
     */
    public function fetch(PDO $conn): ?array
    {
        $sql = $this->getSql();
        $params = $this->getParams();
        TTransaction::log($sql, $params);
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row === false ? null : $row;
    }

    /**
     * Conta os registos do critério.
     *
     * @param PDO $conn Ligação PDO
     * @return int Total de registos
     */
    public function count(PDO $conn): int
    {
        if ($this->entity === '') {
            throw new \RuntimeException('Tabela não definida em SqlSelect. Utilize setEntity().');
        }
        $where = '';
        $params = [];
        if ($this->criteria !== null) {
            [$where, $params] = $this->criteria->dump();
        }
        $sql = "SELECT COUNT(*) FROM {$this->entity}" . ($where !== '' ? " {$where}" : '');
        TTransaction::log($sql, $params);
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }
}
