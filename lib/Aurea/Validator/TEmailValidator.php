<?php
declare(strict_types=1);

namespace Aurea\Validator;

/**
 * Valida endereços de e-mail.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TEmailValidator implements TValidatorInterface
{
    /**
     * Valida se o campo contém um e-mail válido.
     *
     * @param string $label Rótulo do campo
     * @param mixed $value Valor a validar
     * @throws \Exception Se o e-mail for inválido
     */
    public function validate(string $label, $value): void
    {
        if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new \Exception("O campo {$label} deve ser um e-mail válido.");
        }
    }
}
