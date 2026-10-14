<?php
declare(strict_types=1);

namespace Aurea\Widget;

use Aurea\Core\TAction;
use Aurea\Core\TComponent;

/**
 * TDropDown — botão Bootstrap com menu de ações.
 *
 *   $dd = new TDropDown('Opções', 'btn-primary');
 *   $dd->addAction('Editar', new TAction(['ClienteForm', 'onEdit'], ['key' => 1]), 'fa-pen');
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TDropDown extends TComponent
{
    protected string $label;
    protected string $btnClass;
    protected string $toggleId;
    protected static int $counter = 0;
    /** @var array<int,array{label:string,action:TAction,icon:string}> */
    protected array $actions = [];

    /**
     * Cria o botão com o rótulo e a classe informados.
     *
     * @param string $label Rótulo do botão
     * @param string $class Classe Bootstrap do botão
     */
    public function __construct(string $label, string $class = 'btn-secondary')
    {
        parent::__construct('div');
        $this->label = $label;
        $this->btnClass = $class;
        self::$counter++;
        $this->toggleId = 'dropdown_toggle_' . self::$counter;
        $this->addClass('dropdown');
    }

    /**
     * Adiciona uma ação ao menu do botão.
     *
     * @param string $label Rótulo do item de menu
     * @param TAction $action Ação executada pelo item
     * @param string $icon Classe do ícone do item
     * @return static
     */
    public function addAction(string $label, TAction $action, string $icon = ''): static
    {
        $this->actions[] = ['label' => $label, 'action' => $action, 'icon' => $icon];
        return $this;
    }

    /**
     * Obtém o HTML do botão com o menu de ações.
     *
     * @return string HTML do botão
     */
    public function getContents(): string
    {
        $items = '';
        foreach ($this->actions as $item) {
            $href = htmlspecialchars($item['action']->serialize(), ENT_QUOTES);
            $label = htmlspecialchars($item['label'], ENT_QUOTES);
            $icon = $item['icon'] !== '' ? "<i class='" . htmlspecialchars($item['icon'], ENT_QUOTES) . "'></i> " : '';
            $items .= "<li role='none'><a class='dropdown-item' role='menuitem' href='{$href}'>{$icon}{$label}</a></li>";
        }
        $attrs = $this->renderAttributes();
        $label = htmlspecialchars($this->label, ENT_QUOTES);
        $class = htmlspecialchars($this->btnClass, ENT_QUOTES);
        return "<div{$attrs}><button id='{$this->toggleId}' type='button' class='btn {$class} dropdown-toggle' data-bs-toggle='dropdown' aria-expanded='false' aria-haspopup='true'>"
             . "{$label}</button><ul class='dropdown-menu' role='menu' aria-labelledby='{$this->toggleId}'>{$items}</ul></div>";
    }
}
