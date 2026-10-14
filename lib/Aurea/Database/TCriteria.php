<?php
declare(strict_types=1);

namespace Aurea\Database;

/**
 * TCriteria — conjunto de filtros + ordenação + paginação.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TCriteria
{
    /** @var array<int,array{0:TFilter,1:string}> Pares [filtro, operador] */
    protected array $filters = [];
    protected array $properties = [];

    /**
     * Adiciona um filtro ao critério.
     *
     * @param TFilter $filter Filtro campo/operador/valor
     * @param string $operator Operador lógico (AND ou OR)
     * @return static
     * @throws \InvalidArgumentException Se o operador for inválido
     */
    public function add(TFilter $filter, string $operator = 'AND'): static
    {
        $op = strtoupper(trim($operator));
        if ($op !== 'AND' && $op !== 'OR') {
            throw new \InvalidArgumentException("Operador lógico inválido: {$operator}. Utilize AND ou OR.");
        }
        $this->filters[] = [$filter, $op];
        return $this;
    }

    /**
     * Adiciona um filtro com o operador AND.
     *
     * @param string $field Campo da tabela
     * @param string $op Operador de comparação (=, LIKE, ...)
     * @param mixed $value Valor de comparação
     * @return static
     */
    public function andWhere(string $field, string $op, $value): static
    {
        return $this->add(new TFilter($field, $op, $value), 'AND');
    }

    /**
     * Adiciona um filtro com o operador OR.
     *
     * @param string $field Campo da tabela
     * @param string $op Operador de comparação (=, LIKE, ...)
     * @param mixed $value Valor de comparação
     * @return static
     */
    public function orWhere(string $field, string $op, $value): static
    {
        return $this->add(new TFilter($field, $op, $value), 'OR');
    }

    /**
     * Define uma propriedade do critério (order, limit, ...).
     *
     * @param string $name Nome da propriedade
     * @param mixed $value Valor da propriedade
     * @return static
     */
    public function setProperty(string $name, $value): static
    {
        $this->properties[$name] = $value;
        return $this;
    }

    /**
     * Obtém uma propriedade do critério.
     *
     * @param string $name Nome da propriedade
     * @return mixed Valor da propriedade ou null
     */
    public function getProperty(string $name)
    {
        return $this->properties[$name] ?? null;
    }

    /**
     * Define várias propriedades do critério de uma vez.
     *
     * @param array $props Pares nome/valor de propriedades
     * @return static
     */
    public function setProperties(array $props): static
    {
        $this->properties = array_merge($this->properties, $props);
        return $this;
    }

    /**
     * Devolve a cláusula WHERE e os parâmetros.
     *
     * @return array{0:string,1:array} Par [where, params]
     */
    public function dump(): array
    {
        $params = [];
        if (!$this->filters) {
            return ['', $params];
        }
        // foreach (não array_map): TFilter::dump() recebe $params por referência
        $parts = [];
        foreach ($this->filters as $i => $pair) {
            if ($pair instanceof TFilter) {
                $filter = $pair;
                $operator = 'AND';
            } else {
                [$filter, $operator] = $pair;
            }
            $expr = $filter->dump($params);
            if ($i === 0) {
                $parts[] = $expr;
            } else {
                $parts[] = "{$operator} {$expr}";
            }
        }
        return ['WHERE ' . implode(' ', $parts), $params];
    }

    /**
     * Monta a cláusula ORDER BY a partir das propriedades.
     *
     * @return string Cláusula ORDER BY ou string vazia
     */
    public function orderClause(): string
    {
        $order = $this->properties['order'] ?? null;
        if (!$order) {
            return '';
        }
        $dir = strtoupper((string) ($this->properties['direction'] ?? 'ASC')) === 'DESC' ? 'DESC' : 'ASC';
        $order = preg_replace('/[^A-Za-z0-9_,\. ]/', '', (string) $order);
        return " ORDER BY {$order} {$dir}";
    }

    /**
     * Monta a cláusula LIMIT/OFFSET a partir das propriedades.
     *
     * @return string Cláusula LIMIT ou string vazia
     */
    public function limitClause(): string
    {
        if (isset($this->properties['limit'])) {
            $limit = (int) $this->properties['limit'];
            $offset = (int) ($this->properties['offset'] ?? 0);
            return " LIMIT {$limit} OFFSET {$offset}";
        }
        return '';
    }

    /**
     * Limpa todas as propriedades do critério.
     */
    public function resetProperties(): void
    {
        $this->properties = [];
    }

    /**
     * Define GROUP BY.
     */
    public function setGroup(string|array $group): static
    {
        $this->properties['group'] = is_array($group) ? implode(', ', $group) : $group;
        return $this;
    }

    /**
     * Define HAVING bruto (com params opcionais via setProperty('having_params')).
     */
    public function setHaving(string $having, array $params = []): static
    {
        $this->properties['having'] = $having;
        $this->properties['having_params'] = $params;
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
        $this->properties['distinct'] = $distinct;
        return $this;
    }

    /**
     * Colunas selecionadas (usado por repositórios/SELECT).
     */
    public function setColumns(string|array $columns): static
    {
        $this->properties['columns'] = is_array($columns) ? $columns : [$columns];
        return $this;
    }

    /**
     * Obtém as colunas selecionadas do critério.
     *
     * @return string[]|null Lista de colunas ou null
     */
    public function getColumns(): ?array
    {
        return $this->properties['columns'] ?? null;
    }

    /**
     * Monta a cláusula GROUP BY a partir das propriedades.
     *
     * @return string Cláusula GROUP BY ou string vazia
     */
    public function groupClause(): string
    {
        $group = $this->properties['group'] ?? null;
        if (!$group) {
            return '';
        }
        $group = preg_replace('/[^A-Za-z0-9_,\. ]/', '', (string) $group);
        return " GROUP BY {$group}";
    }

    /**
     * @return array{0:string,1:array} Par [having, params]
     */
    public function havingClause(): array
    {
        $having = $this->properties['having'] ?? '';
        $params = $this->properties['having_params'] ?? [];
        if ($having === '') {
            return ['', []];
        }
        return [' HAVING ' . $having, (array) $params];
    }
}
