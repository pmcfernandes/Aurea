<?php
declare(strict_types=1);

namespace Aurea\Database;

use PDO;

/**
 * TSqlDelete — construtor de instruções DELETE com prepared statements.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TSqlDelete
{
    /** @var string Nome da tabela */
    protected string $entity = '';

    /** @var TCriteria|null Critério do WHERE (obrigatório) */
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
     * Devolve a cláusula WHERE do critério.
     *
     * @return string Cláusula WHERE
     * @throws \RuntimeException Se não houver critério (evita DELETE sem WHERE)
     */
    protected function whereClause(): string
    {
        if ($this->criteria === null) {
            throw new \RuntimeException('DELETE sem WHERE não é permitido. Utilize setCriteria().');
        }
        [$where] = $this->criteria->dump();
        if ($where === '') {
            throw new \RuntimeException('DELETE sem WHERE não é permitido. Adicione filtros ao critério.');
        }
        return $where;
    }

    /**
     * Devolve a instrução SQL com placeholders.
     *
     * @return string Instrução DELETE
     * @throws \RuntimeException Se faltar a tabela ou o critério
     */
    public function getSql(): string
    {
        if ($this->entity === '') {
            throw new \RuntimeException('Tabela não definida em SqlDelete. Utilize setEntity().');
        }
        return "DELETE FROM {$this->entity} {$this->whereClause()}";
    }

    /**
     * Devolve os parâmetros do prepared statement.
     *
     * @return array Parâmetros na ordem dos placeholders
     * @throws \RuntimeException Se não houver critério
     */
    public function getParams(): array
    {
        $this->whereClause();
        [, $params] = $this->criteria->dump();
        return $params;
    }

    /**
     * Executa a eliminação e devolve as linhas afectadas.
     *
     * @param PDO $conn Ligação PDO
     * @return int Linhas afectadas
     * @throws \RuntimeException Se não houver critério
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
