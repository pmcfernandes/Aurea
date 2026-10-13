<?php
declare(strict_types=1);

namespace Aurea\Validator;

use Aurea\Util\TStr;

/**
 * Valida o NIF português (9 dígitos com dígito de controlo).
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TNIFValidator implements TValidatorInterface
{
    /**
     * Valida o NIF (formato + dígito de controlo módulo 11).
     *
     * @param string $label Rótulo do campo
     * @param mixed $value Valor a validar
     * @throws \Exception Se o NIF for inválido
     */
    public function validate(string $label, $value): void
    {
        if ($value === null || $value === '') {
            return;
        }
        $nif = TStr::onlyNumbers((string) $value);
        if (!preg_match('/^\d{9}$/', $nif) || !in_array($nif[0], ['1', '2', '3', '5', '6', '8', '9'], true)) {
            throw new \Exception("O campo {$label} deve ser um NIF válido (9 dígitos).");
        }
        $sum = 0;
        for ($i = 0; $i < 8; $i++) {
            $sum += (int) $nif[$i] * (9 - $i);
        }
        $check = 11 - ($sum % 11);
        if ($check >= 10) {
            $check = 0;
        }
        if ((int) $nif[8] !== $check) {
            throw new \Exception("O campo {$label} tem dígito de controlo inválido.");
        }
    }
}
