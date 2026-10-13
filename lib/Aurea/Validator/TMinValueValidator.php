<?php
declare(strict_types=1);

namespace Aurea\Validator;

/**
 * Valida que o valor numérico não é inferior ao mínimo.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TMinValueValidator implements TValidatorInterface
{
    /**
     * Cria o validador com o valor mínimo.
     *
     * @param float $min Valor mínimo aceite
     */
    public function __construct(private float $min) {}

    /**
     * Valida o valor mínimo.
     *
     * @param string $label Rótulo do campo
     * @param mixed $value Valor a validar
     * @throws \Exception Se o valor for inferior ao mínimo
     */
    public function validate(string $label, $value): void
    {
        if ($value === null || $value === '') {
            return;
        }
        $num = (float) str_replace(',', '.', (string) $value);
        if (!is_numeric(str_replace(',', '.', (string) $value)) || $num < $this->min) {
            throw new \Exception("O campo {$label} deve ser no mínimo {$this->min}.");
        }
    }
}
