<?php
declare(strict_types=1);

namespace Aurea\Widget;

use Aurea\Core\TComponent;

/**
 * TProgressBar — barra de progresso acessível.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TProgressBar extends TComponent
{
    protected int $value = 0;
    protected int $min = 0;
    protected int $max = 100;
    protected string $color = '#0d6efd';

    /**
     * Cria a barra de progresso.
     *
     * @param int $value Valor inicial (0-100)
     */
    public function __construct(int $value = 0)
    {
        parent::__construct('div');
        $this->addClass('progress');
        $this->setValue($value);
    }

    /**
     * Define o valor do progresso (limitado a min/max).
     *
     * @param int $value Valor do progresso
     * @return static
     */
    public function setValue(int $value): static
    {
        $this->value = max($this->min, min($this->max, $value));
        return $this;
    }

    /**
     * Define a cor da barra.
     *
     * @param string $color Cor CSS da barra
     * @return static
     */
    public function setColor(string $color): static
    {
        $this->color = $color;
        return $this;
    }

    /**
     * Obtém o HTML acessível da barra de progresso.
     *
     * @return string HTML da barra
     */
    public function getContents(): string
    {
        $pct = $this->max > $this->min ? (int) round(($this->value - $this->min) * 100 / ($this->max - $this->min)) : 0;
        return '<div class="progress" role="progressbar" aria-valuenow="' . $pct . '" aria-valuemin="' . $this->min . '" aria-valuemax="' . $this->max . '">'
            . '<div class="progress-bar" style="width:' . $pct . '%;background-color:' . htmlspecialchars($this->color, ENT_QUOTES) . '">' . $pct . '%</div></div>';
    }
}
