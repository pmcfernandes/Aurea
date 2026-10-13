<?php
declare(strict_types=1);

namespace Aurea\Validator;

/**
 * Valida o preenchimento obrigatório.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TRequiredValidator implements TValidatorInterface
{
    /**
     * Valida se o campo foi preenchido.
     *
     * @param string $label Rótulo do campo
     * @param mixed $value Valor a validar
     * @throws \Exception Se o valor estiver vazio
     */
    public function validate(string $label, $value): void
    {
        if ($value === null || $value === '' || (is_array($value) && !$value)) {
            throw new \Exception("O campo {$label} é obrigatório.");
        }
    }
}
