<?php
declare(strict_types=1);

namespace Aurea\Form;

use Aurea\Core\TComponent;
use Aurea\Validator\TValidatorInterface;


/**
 * Caixa de verificação única com rótulo.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TCheckButton extends TField
{
    /**
     * Cria a caixa de seleção única com rótulo.
     *
     * @param string $name Nome do campo
     * @param string|null $label Rótulo da caixa
     */
    public function __construct(string $name, ?string $label = null)
    {
        parent::__construct($name);
        if ($label !== null) {
            $this->setLabel($label);
        }
    }

    /**
     * Define o valor da caixa (verdadeiro grava "1", falso grava null).
     *
     * @param mixed $value Verdadeiro para marcar a caixa
     * @return static
     */
    public function setValue(mixed $value): static
    {
        $this->value = $value ? '1' : null;
        return $this;
    }

    /**
     * Obtém o HTML do campo.
     *
     * @return string HTML do campo
     */
    public function getContents(): string
    {
        $chk = $this->value ? ' checked' : '';
        return "<label class='form-check-label'><input type='checkbox' name='{$this->getName()}' value='1'{$chk}> "
             . htmlspecialchars($this->getLabel()) . '</label>';
    }
}
