<?php
declare(strict_types=1);

namespace Aurea\Container;

use Aurea\Core\TComponent;

/**
 * Contentor com barra de scroll vertical.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TScroll extends TComponent
{
    protected int $height;

    /**
     * Cria um contentor com altura fixa e scroll.
     *
     * @param int $height Altura em pixels
     */
    public function __construct(int $height = 300)
    {
        parent::__construct('div');
        $this->height = $height;
        $this->applyStyle();
    }

    /**
     * Define a altura do contentor em pixels.
     *
     * @param int $height Altura em pixels
     * @return static
     */
    public function setHeight(int $height): static
    {
        $this->height = $height;
        $this->applyStyle();
        return $this;
    }

    /**
     * Aplica o estilo de scroll e altura ao elemento.
     */
    protected function applyStyle(): void
    {
        $this->setStyle("overflow:auto;height:{$this->height}px");
    }
}
