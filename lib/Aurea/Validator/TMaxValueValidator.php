<?php
declare(strict_types=1);

namespace Aurea\Validator;

/**
 * Valida que o valor numérico não excede o máximo.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TMaxValueValidator implements TValidatorInterface
{
    /**
     * Cria o validador com o valor máximo.
     *
     * @param float $max Valor máximo aceite
     */
    public function __construct(private float $max) {}

    /**
     * Valida o valor máximo.
     *
     * @param string $label Rótulo do campo
     * @param mixed $value Valor a validar
     * @throws \Exception Se o valor exceder o máximo
     */
    public function validate(string $label, $value): void
    {
        if ($value === null || $value === '') {
            return;
        }
        $num = (float) str_replace(',', '.', (string) $value);
        if (!is_numeric(str_replace(',', '.', (string) $value)) || $num > $this->max) {
            throw new \Exception("O campo {$label} deve ser no máximo {$this->max}.");
        }
    }
}
