<?php
declare(strict_types=1);

namespace Aurea\Validator;

/**
 * Interface de validador de campo.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
interface TValidatorInterface
{
    /** @throws \Exception se inválido */

    public function validate(string $label, $value): void;
}
