<?php
declare(strict_types=1);

namespace Aurea\Form;

use Aurea\Core\TComponent;
use Aurea\Validator\TValidatorInterface;


/**
 * Botão de rádio único com rótulo.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TRadioButton extends TField
{
    protected string $optionValue;
    protected string $optionLabel;

    /**
     * Cria o botão de opção único (item de grupo).
     *
     * @param string $name Nome do grupo
     * @param string $value Valor deste botão
     * @param string|null $label Rótulo do botão
     */
    public function __construct(string $name, string $value, ?string $label = null)
    {
        parent::__construct($name);
        $this->optionValue = $value;
        $this->optionLabel = $label ?? $value;
    }

    /**
     * Define o valor marcado do grupo.
     *
     * @param mixed $value Valor marcado do grupo
     * @return static
     */
    public function setGroupValue(mixed $value): static
    {
        return $this->setValue($value);
    }

    /**
     * Obtém o HTML do campo.
     *
     * @return string HTML do campo
     */
    public function getContents(): string
    {
        $chk = ((string) $this->value === $this->optionValue) ? ' checked' : '';
        return "<label class='form-check-label'><input type='radio' name='{$this->getName()}' value='"
             . htmlspecialchars($this->optionValue, ENT_QUOTES) . "'{$chk}> "
             . htmlspecialchars($this->optionLabel) . '</label>';
    }
}
