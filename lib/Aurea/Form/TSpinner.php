<?php
declare(strict_types=1);

namespace Aurea\Form;

use Aurea\Core\TComponent;
use Aurea\Validator\TValidatorInterface;


/**
 * Campo numérico com botões de incremento.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TSpinner extends TField
{
    /**
     * Cria o campo numérico com botões de incremento.
     *
     * @param string $name Nome do campo
     */
    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->setProperty('type', 'number');
    }

    /**
     * Define o valor mínimo do campo.
     *
     * @param float $min Valor mínimo permitido
     * @return static
     */
    public function setMin(float $min): static
    {
        return $this->setProperty('min', (string) $min);
    }

    /**
     * Define o valor máximo do campo.
     *
     * @param float $max Valor máximo permitido
     * @return static
     */
    public function setMax(float $max): static
    {
        return $this->setProperty('max', (string) $max);
    }

    /**
     * Define o passo do campo.
     *
     * @param float $step Incremento de cada passo
     * @return static
     */
    public function setStep(float $step): static
    {
        return $this->setProperty('step', (string) $step);
    }

    /**
     * Obtém o HTML do campo.
     *
     * @return string HTML do campo
     */
    public function getContents(): string
    {
        $this->sync();
        $attrs = $this->renderAttributes();
        $id = $this->getProperty('id') ?? 'field_' . $this->getName();
        $input = "<input{$attrs}>";
        $dec = "<button type='button' onclick=\"document.getElementById('{$id}').stepDown()\">−</button>";
        $inc = "<button type='button' onclick=\"document.getElementById('{$id}').stepUp()\">+</button>";
        return "<div class='input-group spinner'>{$dec}{$input}{$inc}</div>";
    }
}
