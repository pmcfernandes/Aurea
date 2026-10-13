<?php
declare(strict_types=1);

namespace Aurea\Form;

use Aurea\Core\TComponent;
use Aurea\Validator\TValidatorInterface;


/**
 * Controlo deslizante (input range).
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TSlider extends TField
{
    /**
     * Cria o controlo deslizante.
     *
     * @param string $name Nome do campo
     */
    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->setProperty('type', 'range');
        $this->setProperty('oninput', "document.getElementById('out_{$name}').textContent=this.value");
    }

    /**
     * Define o valor mínimo do controlo.
     *
     * @param float $min Valor mínimo permitido
     * @return static
     */
    public function setMin(float $min): static
    {
        return $this->setProperty('min', (string) $min);
    }

    /**
     * Define o valor máximo do controlo.
     *
     * @param float $max Valor máximo permitido
     * @return static
     */
    public function setMax(float $max): static
    {
        return $this->setProperty('max', (string) $max);
    }

    /**
     * Define o passo do controlo.
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
        $input = parent::getContents();
        $val = htmlspecialchars((string) ($this->value ?? ''), ENT_QUOTES);
        return $input . "<output id='out_{$this->getName()}'>{$val}</output>";
    }
}
