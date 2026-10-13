<?php
declare(strict_types=1);

namespace Aurea\Validator;

/**
 * Valida valores numéricos.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TNumericValidator implements TValidatorInterface
{
    /**
     * Valida se o campo contém um valor numérico.
     *
     * @param string $label Rótulo do campo
     * @param mixed $value Valor a validar
     * @throws \Exception Se o valor não for numérico
     */
    public function validate(string $label, $value): void
    {
        if ($value !== null && $value !== '' && !is_numeric(str_replace([',', '.'], ['', ''], (string) $value))) {
            throw new \Exception("O campo {$label} deve ser numérico.");
        }
    }
}
