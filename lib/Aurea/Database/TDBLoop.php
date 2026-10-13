<?php
declare(strict_types=1);

namespace Aurea\Database;

/**
 * TDBLoop — iteração de resultados como Iterator/Countable.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
final class TDBLoop implements \Iterator, \Countable
{
    /** @var array<int,mixed> */
    private array $rows;
    private int $position = 0;

    /**
     * Cria o iterador sobre linhas (arrays ou objetos).
     *
     * @param array $rows Linhas de resultado
     */
    public function __construct(array $rows = [])
    {
        $this->rows = array_values($rows);
    }

    /**
     * Cria o iterador executando SQL com prepared statements.
     *
     * @param \PDO $conn Conexão PDO
     * @param string $sql Instrução SELECT
     * @param array $params Parâmetros vinculados
     * @return self Iterador sobre as linhas
     */
    public static function fromQuery(\PDO $conn, string $sql, array $params = []): self
    {
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return new self($stmt->fetchAll(\PDO::FETCH_ASSOC));
    }

    /**
     * Volta à primeira posição.
     */
    public function rewind(): void
    {
        $this->position = 0;
    }

    /**
     * Obtém a linha corrente.
     *
     * @return mixed Linha corrente ou null
     */
    public function current(): mixed
    {
        return $this->rows[$this->position] ?? null;
    }

    /**
     * Obtém ou define a posição.
     *
     * @param int|null $position Nova posição (omissão = só ler)
     * @return int Posição atual
     */
    public function key(?int $position = null): int
    {
        if ($position !== null) {
            $this->position = $position;
        }
        return $this->position;
    }

    /**
     * Avança para a próxima linha.
     */
    public function next(): void
    {
        $this->position++;
    }

    /**
     * Indica se a posição corrente é válida.
     *
     * @return bool Verdadeiro se há linha corrente
     */
    public function valid(): bool
    {
        return isset($this->rows[$this->position]);
    }

    /**
     * Conta as linhas.
     *
     * @return int Número de linhas
     */
    public function count(): int
    {
        return count($this->rows);
    }

    /**
     * Lista as colunas (chaves da primeira linha).
     *
     * @return string[] Nomes das colunas
     */
    public function listColumns(): array
    {
        $first = $this->rows[0] ?? null;
        if (is_array($first)) {
            return array_keys($first);
        }
        if (is_object($first)) {
            return array_keys(get_object_vars($first));
        }
        return [];
    }

    /**
     * Devolve todas as linhas.
     *
     * @return array Lista de linhas
     */
    public function toArray(): array
    {
        return $this->rows;
    }
}
