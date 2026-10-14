<?php
declare(strict_types=1);

namespace Aurea\Database;

/**
 * TFilter — um filtro campo/operador/valor.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TFilter
{
    /**
     * Cria um filtro campo/operador/valor.
     *
     * @param string|TExpression $field Campo da tabela ou expressão SQL
     * @param string $operator Operador (=, >, LIKE, IN, ...)
     * @param mixed $value Valor de comparação
     */
    public function __construct(
        public string|TExpression $field,
        public string $operator,
        public mixed $value
    ) {
        if (!$field instanceof TExpression) {
            TIdentifier::assert($field);
        }
    }

    /** @param array $params acumulador de prepared-statement */

    public function dump(array &$params): string
    {
        $op = strtoupper(trim($this->operator));
        $allowed = ['=', '!=', '<>', '>', '<', '>=', '<=', 'LIKE', 'ILIKE', 'IN', 'NOT IN', 'IS', 'IS NOT'];
        if (!in_array($op, $allowed, true)) {
            throw new \RuntimeException("Operador inválido: {$this->operator}");
        }
        $fieldSql = $this->field instanceof TExpression ? $this->field->getSql() : $this->field;
        $exprParams = $this->field instanceof TExpression ? $this->field->getParams() : [];
        if ($op === 'IN' || $op === 'NOT IN') {
            $values = (array) $this->value;
            $ph = implode(',', array_fill(0, count($values), '?'));
            foreach ($exprParams as $p) {
                $params[] = $p;
            }
            foreach ($values as $v) {
                $params[] = $v;
            }
            return "{$fieldSql} {$op} ({$ph})";
        }
        if (($op === 'IS' || $op === 'IS NOT') && $this->value === null) {
            foreach ($exprParams as $p) {
                $params[] = $p;
            }
            return "{$fieldSql} {$op} NULL";
        }
        foreach ($exprParams as $p) {
            $params[] = $p;
        }
        $params[] = $this->value;
        return "{$fieldSql} {$op} ?";
    }
}
