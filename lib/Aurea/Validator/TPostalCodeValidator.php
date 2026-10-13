<?php
declare(strict_types=1);

namespace Aurea\Validator;

/**
 * Valida o código postal português (formato 0000-000).
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TPostalCodeValidator implements TValidatorInterface
{
    /**
     * Valida o formato do código postal.
     *
     * @param string $label Rótulo do campo
     * @param mixed $value Valor a validar
     * @throws \Exception Se o formato for inválido
     */
    public function validate(string $label, $value): void
    {
        if ($value === null || $value === '') {
            return;
        }
        if (!preg_match('/^\d{4}-\d{3}$/', trim((string) $value))) {
            throw new \Exception("O campo {$label} deve ter o formato 0000-000.");
        }
    }
}
