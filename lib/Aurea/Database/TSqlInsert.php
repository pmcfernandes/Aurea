<?php
declare(strict_types=1);

namespace Aurea\Database;

use PDO;

/**
 * TSqlInsert — construtor de instruções INSERT com prepared statements.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TSqlInsert
{
    /** @var string Nome da tabela */
    protected string $entity = '';

    /** @var array Dados da linha (coluna => valor) */
    protected array $rowData = [];

    /**
     * Define a tabela da instrução.
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
     * Define os dados da linha a inserir.
     *
     * @param array $data Pares coluna/valor
     * @return static
     */
    public function setRowData(array $data): static
    {
        foreach (array_keys($data) as $column) {
            TIdentifier::assert((string) $column);
        }
        $this->rowData = $data;
        return $this;
    }

    /**
     * Adiciona um par coluna/valor aos dados da linha.
     *
     * @param string $col Nome da coluna
     * @param mixed $val Valor da coluna
     * @return static
     */
    public function addRowData(string $col, $val): static
    {
        TIdentifier::assert($col);
        $this->rowData[$col] = $val;
        return $this;
    }

    /**
     * Devolve a instrução SQL com placeholders.
     *
     * @return string Instrução INSERT
     * @throws \RuntimeException Se faltar a tabela ou os dados
     */
    public function getSql(): string
    {
        if ($this->entity === '') {
            throw new \RuntimeException('Tabela não definida em SqlInsert. Utilize setEntity().');
        }
        if (empty($this->rowData)) {
            throw new \RuntimeException('Dados não definidos em SqlInsert. Utilize setRowData().');
        }
        $cols = implode(', ', array_keys($this->rowData));
        $placeholders = implode(', ', array_fill(0, count($this->rowData), '?'));
        return "INSERT INTO {$this->entity} ({$cols}) VALUES ({$placeholders})";
    }

    /**
     * Devolve os parâmetros do prepared statement.
     *
     * @return array Valores na ordem das colunas
     */
    public function getParams(): array
    {
        return array_values($this->rowData);
    }

    /**
     * Executa a inserção e devolve o último id.
     *
     * @param PDO $conn Ligação PDO
     * @return string Último id inserido
     */
    public function execute(PDO $conn): string
    {
        $sql = $this->getSql();
        $params = $this->getParams();
        TTransaction::log($sql, $params);
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $conn->lastInsertId();
    }
}
