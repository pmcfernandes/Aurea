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

    /**
     * Define a tabela da consulta.
     *
     * @param string $table Nome da tabela
     * @return static
     */
    public function setEntity(string $table): static
    {
        $this->entity = $table;
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
        $sql = "SELECT {$cols} FROM {$this->entity}";
        if ($this->criteria !== null) {
            [$where] = $this->criteria->dump();
            if ($where !== '') {
                $sql .= " {$where}";
            }
            $sql .= $this->criteria->orderClause();
            $sql .= $this->criteria->limitClause();
        }
        return $sql;
    }

    /**
     * Devolve os parâmetros do prepared statement.
     *
     * @return array Parâmetros na ordem dos placeholders
     */
    public function getParams(): array
    {
        if ($this->criteria === null) {
            return [];
        }
        [, $params] = $this->criteria->dump();
        return $params;
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
