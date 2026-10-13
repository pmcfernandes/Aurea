<?php
declare(strict_types=1);

namespace Aurea\Container;

use Aurea\Core\TComponent;

/**
 * TExpander colapsável com details/summary.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TExpander extends TComponent
{
    protected string $title;

    /**
     * Cria um bloco colapsável com o título indicado.
     *
     * @param string $title Título visível no cabeçalho
     */
    public function __construct(string $title)
    {
        parent::__construct('details');
        $this->title = $title;
    }

    /**
     * Define se o bloco começa aberto ou fechado.
     *
     * @param bool $open True para começar aberto
     * @return static
     */
    public function setOpen(bool $open): static
    {
        if ($open) {
            $this->setProperty('open', 'open');
        } else {
            $this->setProperty('open', null);
        }
        return $this;
    }

    /**
     * Renderiza o HTML do bloco colapsável.
     *
     * @return string HTML do bloco
     */
    public function getContents(): string
    {
        $attrs = $this->renderAttributes();
        $title = htmlspecialchars($this->title, ENT_QUOTES);
        return "<details{$attrs}><summary>{$title}</summary>" . $this->renderChildren() . '</details>';
    }
}
