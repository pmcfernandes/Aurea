<?php
declare(strict_types=1);

namespace Aurea\Database;

use PDO;

/**
 * TSqlUpdate — construtor de instruções UPDATE com prepared statements.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TSqlUpdate
{
    /** @var string Nome da tabela */
    protected string $entity = '';

    /** @var array Dados a actualizar (coluna => valor) */
    protected array $rowData = [];

    /** @var TCriteria|null Critério do WHERE */
    protected ?TCriteria $criteria = null;

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
     * Define os dados a actualizar.
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
     * Define o critério do WHERE.
     *
     * @param TCriteria $c Critério com filtros
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
     * @return string Instrução UPDATE
     * @throws \RuntimeException Se faltar a tabela ou os dados
     */
    public function getSql(): string
    {
        if ($this->entity === '') {
            throw new \RuntimeException('Tabela não definida em SqlUpdate. Utilize setEntity().');
        }
        if (empty($this->rowData)) {
            throw new \RuntimeException('Dados não definidos em SqlUpdate. Utilize setRowData().');
        }
        $sets = implode(', ', array_map(fn($c) => "{$c} = ?", array_keys($this->rowData)));
        $sql = "UPDATE {$this->entity} SET {$sets}";
        if ($this->criteria !== null) {
            [$where] = $this->criteria->dump();
            if ($where !== '') {
                $sql .= " {$where}";
            }
        }
        return $sql;
    }

    /**
     * Devolve os parâmetros (primeiro SET, depois WHERE).
     *
     * @return array Parâmetros na ordem dos placeholders
     */
    public function getParams(): array
    {
        $params = array_values($this->rowData);
        if ($this->criteria !== null) {
            [, $whereParams] = $this->criteria->dump();
            foreach ($whereParams as $p) {
                $params[] = $p;
            }
        }
        return $params;
    }

    /**
     * Executa a actualização e devolve as linhas afectadas.
     *
     * @param PDO $conn Ligação PDO
     * @return int Linhas afectadas
     */
    public function execute(PDO $conn): int
    {
        $sql = $this->getSql();
        $params = $this->getParams();
        TTransaction::log($sql, $params);
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }
}
