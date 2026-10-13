<?php
declare(strict_types=1);

namespace Aurea\Validator;

/**
 * Valida o comprimento mínimo.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TMinLengthValidator implements TValidatorInterface
{
    /**
     * Cria o validador de comprimento mínimo.
     *
     * @param int $min Mínimo de caracteres
     */
    public function __construct(private int $min) {}

    /**
     * Valida o comprimento mínimo do campo.
     *
     * @param string $label Rótulo do campo
     * @param mixed $value Valor a validar
     * @throws \Exception Se for menor que o mínimo
     */
    public function validate(string $label, $value): void
    {
        if (is_string($value) && mb_strlen(trim(strip_tags($value))) < $this->min) {
            throw new \Exception("O campo {$label} deve ter ao menos {$this->min} caracteres.");
        }
    }
}
