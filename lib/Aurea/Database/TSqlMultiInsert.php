<?php
declare(strict_types=1);

namespace Aurea\Database;

use PDO;

/**
 * Inserção parametrizada de várias linhas com colunas uniformes.
 * A execução deve ocorrer dentro de uma transação quando a operação precisar
 * ser atómica com outras alterações.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
final class TSqlMultiInsert
{
    private string $entity = '';
    /** @var array<int,array<string,mixed>> */
    private array $rows = [];

    /** @param string $table Tabela de destino (identificador validado). */
    public function setEntity(string $table): static { $this->entity = TIdentifier::assert($table); return $this; }

    /**
     * Define as linhas a inserir; todas devem ter as mesmas colunas e ordem.
     * @param array<int,array<string,mixed>> $rows
     * @throws \InvalidArgumentException Para linhas vazias ou incompatíveis
     */
    public function setRows(array $rows): static
    {
        if ($rows === []) { throw new \InvalidArgumentException('A inserção em lote requer pelo menos uma linha.'); }
        $columns = array_keys($rows[0]);
        foreach ($columns as $column) { TIdentifier::assert((string) $column); }
        foreach ($rows as $row) {
            if (array_keys($row) !== $columns) { throw new \InvalidArgumentException('Todas as linhas devem possuir as mesmas colunas e ordem.'); }
        }
        $this->rows = $rows;
        return $this;
    }

    /** @throws \RuntimeException Se tabela ou linhas não estiverem definidas. */
    public function getSql(): string
    {
        if ($this->entity === '' || $this->rows === []) { throw new \RuntimeException('Tabela e linhas são obrigatórias para inserção em lote.'); }
        $columns = array_keys($this->rows[0]);
        $row = '(' . implode(', ', array_fill(0, count($columns), '?')) . ')';
        return "INSERT INTO {$this->entity} (" . implode(', ', $columns) . ') VALUES ' . implode(', ', array_fill(0, count($this->rows), $row));
    }

    /** @return mixed[] Valores dos placeholders na ordem do SQL. */
    public function getParams(): array { return array_merge(...array_map('array_values', $this->rows)); }

    /** @return int Quantidade de linhas afetadas. */
    public function execute(PDO $connection): int
    {
        $sql = $this->getSql();
        $params = $this->getParams();
        TTransaction::log($sql, $params);
        $statement = $connection->prepare($sql);
        $statement->execute($params);
        return $statement->rowCount();
    }
}
