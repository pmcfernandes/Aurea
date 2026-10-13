<?php
declare(strict_types=1);

namespace Aurea\Container;

use Aurea\Core\TAction;
use Aurea\Core\TComponent;

/**
 * TSidebar — menu lateral escuro com marca, secções e itens.
 *
 *   $bar = new TSidebar('Aurea');
 *   $bar->addSection('Principal');
 *   $bar->addItem('Painel', new TAction(['HomeView', 'onShow']), 'fa-house', true);
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TSidebar extends TComponent
{
    protected string $brand;
    /** @var array<int,array<string,mixed>> */
    protected array $blocks = [];

    /**
     * Cria a barra lateral com a marca informada.
     *
     * @param string $brand Nome da marca
     */
    public function __construct(string $brand = '')
    {
        parent::__construct('div');
        $this->brand = $brand;
        $this->addClass('sidebar');
    }

    /**
     * Adiciona uma secção ao menu lateral.
     *
     * @param string $title Título da secção
     * @return static
     */
    public function addSection(string $title): static
    {
        $this->blocks[] = ['type' => 'section', 'title' => $title];
        return $this;
    }

    /**
     * Adiciona um item ao menu lateral.
     *
     * @param string $label Rótulo do item
     * @param TAction|null $action Acção de destino do item
     * @param string $icon Classe do ícone do item
     * @param bool $active Indica se o item está ativo
     * @return static
     */
    public function addItem(string $label, ?TAction $action = null, string $icon = 'fa-circle', bool $active = false): static
    {
        $this->blocks[] = [
            'type' => 'item',
            'label' => $label,
            'action' => $action,
            'icon' => $icon,
            'active' => $active,
        ];
        return $this;
    }

    /**
     * Obtém o HTML do menu lateral escuro.
     *
     * @return string HTML da barra lateral
     */
    public function getContents(): string
    {
        $attrs = $this->renderAttributes();
        $brand = htmlspecialchars($this->brand, ENT_QUOTES, 'UTF-8');

        $html = "<nav{$attrs}><div class='sidebar-brand'>{$brand}</div><ul class='nav flex-column'>";
        foreach ($this->blocks as $block) {
            if (($block['type'] ?? '') === 'section') {
                $title = htmlspecialchars((string) $block['title'], ENT_QUOTES, 'UTF-8');
                $html .= "<li class='nav-item sidebar-section'><span class='text-muted text-uppercase small'>{$title}</span></li>";
                continue;
            }
            $label = htmlspecialchars((string) $block['label'], ENT_QUOTES, 'UTF-8');
            $icon = htmlspecialchars((string) $block['icon'], ENT_QUOTES, 'UTF-8');
            $href = isset($block['action']) && $block['action'] instanceof TAction
                ? htmlspecialchars($block['action']->serialize(), ENT_QUOTES, 'UTF-8')
                : '#';
            $active = !empty($block['active']) ? ' active' : '';
            $html .= "<li class='nav-item'><a class='nav-link{$active}' href='{$href}'><i class='{$icon}'></i> {$label}</a></li>";
        }
        $html .= '</ul></nav>';
        return $html;
    }
}
