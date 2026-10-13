<?php
declare(strict_types=1);

namespace Aurea\Validator;

use Aurea\Util\TDateTimeUtil;

/**
 * Valida horas numa máscara (por omissão hh:mi).
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TTimeValidator implements TValidatorInterface
{
    /**
     * Cria o validador com a máscara esperada.
     *
     * @param string $mask Máscara (ex: hh:mi, hh:mi:ss)
     */
    public function __construct(private string $mask = 'hh:mi') {}

    /**
     * Valida a hora.
     *
     * @param string $label Rótulo do campo
     * @param mixed $value Valor a validar
     * @throws \Exception Se a hora for inválida
     */
    public function validate(string $label, $value): void
    {
        if ($value === null || $value === '') {
            return;
        }
        if (!TDateTimeUtil::isValid((string) $value, $this->mask)) {
            throw new \Exception("O campo {$label} deve ser uma hora válida ({$this->mask}).");
        }
    }
}
