<?php
declare(strict_types=1);

namespace Aurea\Grid;

use Aurea\Core\TAction;

/**
 * TRowActionGroup — dropdown de ações por linha do DataGrid.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TRowActionGroup
{
    /** @var array{label:string,action:TAction,icon:string,display:?callable}[] */
    protected array $actions = [];

    /**
     * Cria o grupo dropdown de ações por linha.
     *
     * @param string $label Rótulo do botão do dropdown
     */
    public function __construct(protected string $label = 'Ações') {}

    /**
     * Obtém o rótulo do botão do dropdown.
     *
     * @return string Rótulo do grupo
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * Adiciona uma ação ao dropdown.
     *
     * @param string $label Rótulo do item
     * @param TAction $action Ação do item
     * @param string $icon Ícone do item
     * @param ?callable $display Função fn(array $linha): bool que decide se a ação aparece
     * @return static
     */
    public function addAction(string $label, TAction $action, string $icon = '', ?callable $display = null): static
    {
        $this->actions[] = ['label' => $label, 'action' => $action, 'icon' => $icon, 'display' => $display];
        return $this;
    }

    /**
     * Renderiza a célula do dropdown para uma linha de dados.
     *
     * @param array $arr Linha de dados (com a chave do registro)
     * @return string HTML da célula td
     */
    public function renderCell(array $arr): string
    {
        $out = '<div class="btn-group btn-group-sm">';
        $out .= '<button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">'
            . htmlspecialchars($this->label) . '</button>';
        $out .= '<ul class="dropdown-menu">';
        foreach ($this->actions as $item) {
            $display = $item['display'] ?? null;
            if ($display !== null && !$display($arr)) {
                continue;
            }
            $a = clone $item['action'];
            if (isset($arr['id'])) {
                $a->setParameter('key', $arr['id']);
            }
            $icon = $item['icon'] !== '' ? "<i class='" . htmlspecialchars($item['icon']) . "'></i> " : '';
            $out .= "<li><a class='dropdown-item' href='" . $a->toHref() . "'>{$icon}"
                . htmlspecialchars($item['label']) . '</a></li>';
        }
        $out .= '</ul></div>';
        return "<td>{$out}</td>";
    }
}
