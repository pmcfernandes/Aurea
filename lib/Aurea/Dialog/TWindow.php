<?php
declare(strict_types=1);

namespace Aurea\Dialog;

use Aurea\Core\TAction;
use Aurea\Core\TComponent;
use Aurea\Core\TScript;

/**
 * TWindow — janela modal Bootstrap.
 *
 *   $win = new TWindow('Detalhes', 600);
 *   $win->setContent($grid);
 *   $win->addButton('Fechar', new TAction(['ClienteList', 'onReload']));
 *   $win->show(); // imprime + abre via JS
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TWindow extends TComponent
{
    protected static int $counter = 0;
    protected string $modalId;
    protected TComponent $body;
    protected TComponent $footer;

    /**
     * Cria uma janela modal Bootstrap.
     *
     * @param string $title Título da janela
     * @param int $width Largura máxima em pixels
     */
    public function __construct(string $title, int $width = 500)
    {
        parent::__construct('div');
        self::$counter++;
        $this->modalId = 'aurea-modal-' . self::$counter;
        $this->addClass('modal fade');
        $this->setProperty('tabindex', '-1');

        $dialog = new TComponent('div');
        $dialog->addClass('modal-dialog');
        $dialog->setStyle("max-width:{$width}px");

        $content = new TComponent('div');
        $content->addClass('modal-content');

        $header = new TComponent('div');
        $header->addClass('modal-header');
        $h = new TComponent('h5');
        $h->addClass('modal-title');
        $h->add(htmlspecialchars($title));
        $close = new TComponent('button');
        $close->addClass('btn-close');
        $close->setProperty('type', 'button');
        $close->setProperty('data-bs-dismiss', 'modal');
        $header->add($h);
        $header->add($close);

        $this->body = new TComponent('div');
        $this->body->addClass('modal-body');
        $this->footer = new TComponent('div');
        $this->footer->addClass('modal-footer');

        $content->add($header);
        $content->add($this->body);
        $content->add($this->footer);
        $dialog->add($content);
        parent::add($dialog);
    }

    /**
     * Obtém o identificador do modal.
     *
     * @return string Id do modal
     */
    public function getModalId(): string
    {
        return $this->modalId;
    }

    /**
     * Define o conteúdo do corpo da janela.
     *
     * @param mixed $content Componente ou texto do corpo
     * @return static
     */
    public function setContent($content): static
    {
        $this->body->add($content);
        return $this;
    }

    /**
     * Adiciona um botão ao rodapé da janela.
     *
     * @param string $label Rótulo do botão
     * @param ?TAction $action Ação do botão ou null para só fechar
     * @param string $class Classe CSS do botão
     * @return static
     */
    public function addButton(string $label, ?TAction $action = null, string $class = 'btn-secondary'): static
    {
        $btn = new TComponent($action ? 'a' : 'button');
        $btn->addClass("btn {$class}");
        if ($action) {
            $btn->setProperty('href', $action->toHref());
        } else {
            $btn->setProperty('type', 'button');
            $btn->setProperty('data-bs-dismiss', 'modal');
        }
        $btn->add($label);
        $this->footer->add($btn);
        return $this;
    }

    /**
     * Exibe a janela modal e o script de abertura.
     */
    public function show(): void
    {
        $this->setProperty('id', $this->modalId);
        parent::show();
        TScript::showModal($this->modalId);
        echo TScript::render();
    }

    /**
     * Renderiza o HTML da janela modal.
     *
     * @return string HTML do modal
     */
    public function getContents(): string
    {
        $this->setProperty('id', $this->modalId);
        return parent::getContents();
    }
}
