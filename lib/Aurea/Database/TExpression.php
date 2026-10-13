<?php
declare(strict_types=1);

namespace Aurea\Database;

/**
 * TExpression — expressão SQL com parâmetros vinculados (ex: UPPER(nome)).
 * Permite utilizar expressões SQL no lado esquerdo de um Filter.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TExpression
{
    /**
     * Cria uma expressão SQL com parâmetros opcionais.
     *
     * @param string $sql Trecho SQL da expressão (ex: UPPER(nome))
     * @param array $params Parâmetros vinculados da expressão
     */
    public function __construct(
        protected string $sql,
        protected array $params = []
    ) {
    }

    /**
     * Obtém o trecho SQL da expressão.
     *
     * @return string Trecho SQL da expressão
     */
    public function getSql(): string
    {
        return $this->sql;
    }

    /**
     * Obtém os parâmetros vinculados da expressão.
     *
     * @return array Parâmetros vinculados da expressão
     */
    public function getParams(): array
    {
        return $this->params;
    }
}
