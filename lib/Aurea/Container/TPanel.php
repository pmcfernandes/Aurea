<?php
declare(strict_types=1);

namespace Aurea\Container;

use Aurea\Core\TComponent;


/**
 * Contentor Bootstrap em cartão.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TPanel extends TComponent
{
    protected TComponent $body;
    protected bool $built = false;

    /**
     * Cria um painel card com título opcional.
     *
     * @param string $title Título do cabeçalho
     */
    public function __construct(string $title = '')
    {
        parent::__construct('div');
        $this->addClass('card mb-3');
        if ($title !== '') {
            $head = new TComponent('div');
            $head->addClass('card-header fw-bold');
            $head->add($title);
            parent::add($head);
        }
        $this->body = new TComponent('div');
        $this->body->addClass('card-body');
        parent::add($this->body);
        $this->built = true;
    }

    /**
     * Adiciona conteúdo ao corpo do painel.
     *
     * @param mixed $content Componente ou texto a adicionar
     * @return static
     */
    public function add($content): static
    {
        // após construído, filhos vão para o body (como no TPanelGroup)
        if ($this->built && $content !== $this->body) {
            $this->body->add($content);
            return $this;
        }
        return parent::add($content);
    }

    /**
     * Adiciona conteúdo diretamente ao corpo do painel.
     *
     * @param mixed $content Componente ou texto a adicionar
     * @return static
     */
    public function addToBody($content): static
    {
        $this->body->add($content);
        return $this;
    }
}
