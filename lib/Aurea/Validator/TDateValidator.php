<?php
declare(strict_types=1);

namespace Aurea\Validator;

use Aurea\Util\TDateTimeUtil;

/**
 * Valida datas numa máscara (por omissão dd/mm/yyyy).
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TDateValidator implements TValidatorInterface
{
    /**
     * Cria o validador com a máscara esperada.
     *
     * @param string $mask Máscara (ex: dd/mm/yyyy)
     */
    public function __construct(private string $mask = 'dd/mm/yyyy') {}

    /**
     * Valida a data.
     *
     * @param string $label Rótulo do campo
     * @param mixed $value Valor a validar
     * @throws \Exception Se a data for inválida
     */
    public function validate(string $label, $value): void
    {
        if ($value === null || $value === '') {
            return;
        }
        if (!TDateTimeUtil::isValid((string) $value, $this->mask)) {
            throw new \Exception("O campo {$label} deve ser uma data válida ({$this->mask}).");
        }
    }
}
