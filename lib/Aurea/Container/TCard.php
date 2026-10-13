<?php
declare(strict_types=1);

namespace Aurea\Container;

use Aurea\Core\TAction;
use Aurea\Core\TComponent;

/**
 * TCard Bootstrap com imagem, título, texto e botões.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TCard extends TComponent
{
    protected ?TComponent $image = null;
    protected TComponent $titleEl;
    protected TComponent $textEl;
    protected TComponent $body;
    protected bool $built = false;

    /**
     * Cria o cartão com o título informado.
     *
     * @param string $title Título do cartão
     */
    public function __construct(string $title = '')
    {
        parent::__construct('div');
        $this->addClass('card');
        $this->body = new TComponent('div');
        $this->body->addClass('card-body');
        $this->titleEl = new TComponent('h5');
        $this->titleEl->addClass('card-title');
        if ($title !== '') {
            $this->titleEl->add($title);
        }
        $this->textEl = new TComponent('p');
        $this->textEl->addClass('card-text');
        $this->body->add($this->titleEl);
        $this->body->add($this->textEl);
        parent::add($this->body);
        $this->built = true;
    }

    /**
     * Adiciona conteúdo ao corpo do cartão.
     *
     * @param mixed $content Componente ou texto a adicionar
     * @return static
     */
    public function add($content): static
    {
        // após construído, filhos vão para o corpo (padrão TPanel)
        if ($this->built && $content !== $this->body) {
            $this->body->add($content);
            return $this;
        }
        return parent::add($content);
    }

    /**
     * Define a imagem de topo do cartão.
     *
     * @param string $url URL da imagem
     * @return static
     */
    public function setImage(string $url): static
    {
        if ($this->image === null) {
            $this->image = new TComponent('img');
            $this->image->addClass('card-img-top');
            $this->setChildren([$this->image, $this->body]);
        }
        $this->image->setProperty('src', $url);
        return $this;
    }

    /**
     * Define o texto do cartão.
     *
     * @param string $text Texto do cartão
     * @return static
     */
    public function setText(string $text): static
    {
        $this->textEl->setChildren([$text]);
        return $this;
    }

    /**
     * Define um conteúdo extra no corpo do cartão.
     *
     * @param mixed $content Componente ou texto a adicionar
     * @return static
     */
    public function setContent($content): static
    {
        $this->body->add($content);
        return $this;
    }

    /**
     * Adiciona um botão ao corpo do cartão.
     *
     * @param string $label Rótulo do botão
     * @param TAction|null $action Ação do botão ou null
     * @param string $class Classe CSS do botão
     * @return static
     */
    public function addButton(string $label, ?TAction $action = null, string $class = 'btn-primary'): static
    {
        $btn = new TComponent('a');
        $btn->addClass('btn ' . $class);
        $btn->setProperty('href', $action ? $action->toHref() : '#');
        $btn->add($label);
        $this->body->add($btn);
        return $this;
    }
}
