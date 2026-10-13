<?php
declare(strict_types=1);

namespace Aurea\Validator;

/**
 * Valida o comprimento máximo ignorando tags HTML.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TStrippedMaxLengthValidator implements TValidatorInterface
{
    /**
     * Cria o validador de comprimento máximo sem etiquetas HTML.
     *
     * @param int $max Máximo de caracteres (contados sem etiquetas nem espaços extremos)
     */
    public function __construct(private int $max) {}

    /**
     * Valida o comprimento máximo do campo ignorando etiquetas HTML.
     *
     * @param string $label Rótulo do campo
     * @param mixed $value Valor a validar
     * @throws \Exception Se exceder o máximo
     */
    public function validate(string $label, $value): void
    {
        if (is_string($value) && mb_strlen(trim(strip_tags($value))) > $this->max) {
            throw new \Exception("O campo {$label} deve ter no máximo {$this->max} caracteres.");
        }
    }
}
