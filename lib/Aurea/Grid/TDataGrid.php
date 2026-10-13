<?php
declare(strict_types=1);

namespace Aurea\Grid;

use Aurea\Core\TAction;
use Aurea\Core\TComponent;
use Aurea\Database\TRecord;

/**
 * TDataGrid — tabela de listagem com ações.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TDataGrid extends TComponent
{
    /** @var TColumn[] */
    protected array $columns = [];
    /** @var array<object|array> */
    protected array $items = [];
    /** @var array{label:string,action:TAction,icon:string,class:string,display:?callable}[] */
    protected array $rowActions = [];
    protected ?TAction $orderAction = null;
    protected string $order = '';
    protected string $direction = 'asc';
    /** @var TRowActionGroup[] */
    protected array $actionGroups = [];
    protected bool $selectable = false;
    protected string $keyField = 'id';

    /**
     * Cria a grade de listagem responsiva.
     */
    public function __construct()
    {
        parent::__construct('div');
        $this->addClass('table-responsive bg-white rounded border');
    }

    /**
     * Adiciona uma coluna à grade.
     *
     * @param TColumn $column Coluna a adicionar
     * @return static
     */
    public function addColumn(TColumn $column): static
    {
        $this->columns[] = $column;
        return $this;
    }

    /**
     * Adiciona um botão de ação por linha da grade.
     *
     * @param string $label Rótulo do botão
     * @param TAction $action Ação do botão
     * @param string $icon Ícone FontAwesome
     * @param string $btnClass Classe CSS do botão
     * @param ?callable $display Função fn(array $linha): bool que decide se a ação aparece
     * @return static
     */
    public function addAction(string $label, TAction $action, string $icon = 'fa-edit', string $btnClass = 'btn-outline-primary', ?callable $display = null): static
    {
        $this->rowActions[] = ['label' => $label, 'action' => $action, 'icon' => $icon, 'btnClass' => $btnClass, 'display' => $display];
        return $this;
    }

    /**
     * Adiciona um grupo dropdown de ações por linha da grade.
     *
     * @param TRowActionGroup $group Grupo de ações a adicionar
     * @return static
     */
    public function addActionGroup(TRowActionGroup $group): static
    {
        $this->actionGroups[] = $group;
        return $this;
    }

    /**
     * Ativa a coluna de seleção em massa por checkboxes.
     *
     * @param string $keyField Campo usado como valor do checkbox
     * @return static
     */
    public function enableSelection(string $keyField = 'id'): static
    {
        $this->selectable = true;
        $this->keyField = $keyField;
        return $this;
    }

    /**
     * Obtém os ids selecionados via checkboxes da grade.
     *
     * @return array Lista de ids enviados em $_POST['_grid_sel']
     */
    public static function getSelectedIds(): array
    {
        $sel = $_POST['_grid_sel'] ?? [];
        if (!is_array($sel)) {
            return [];
        }
        return array_values(array_filter($sel, fn($v) => is_scalar($v)));
    }

    /**
     * Adiciona um item (registro ou array) à grade.
     *
     * @param object|array $item Item a exibir
     * @return static
     */
    public function addItem(object|array $item): static
    {
        $this->items[] = $item;
        return $this;
    }

    /** @param object[]|array[] $items */

    public function setItems(array $items): static
    {
        $this->items = $items;
        return $this;
    }

    /**
     * Remove todos os itens da grade.
     *
     * @return static
     */
    public function clear(): static
    {
        $this->items = [];
        return $this;
    }

    /**
     * Ativa ordenação por clique no cabeçalho.
     * $action é o evento de reload (ex: new TAction([$this, 'onReload'], $param)).
     */
    public function setOrder(string $order, string $direction, TAction $action): static
    {
        $this->order = $order;
        $this->direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';
        $this->orderAction = $action;
        return $this;
    }

    /**
     * Renderiza a célula de cabeçalho com link de ordenação.
     *
     * @param TColumn $col Coluna do cabeçalho
     * @return string HTML da célula th
     */
    protected function headerCell(TColumn $col): string
    {
        $label = htmlspecialchars($col->getLabel());
        if ($this->orderAction && $col->isOrderable()) {
            $next = ($this->order === $col->getName() && $this->direction === 'asc') ? 'desc' : 'asc';
            $arrow = $this->order === $col->getName()
                ? ($this->direction === 'asc' ? ' ▲' : ' ▼')
                : '';
            $href = (clone $this->orderAction)
                ->setParameter('order', $col->getName())
                ->setParameter('direction', $next)
                ->toHref();
            return "<th><a href='{$href}'>{$label}{$arrow}</a></th>";
        }
        return "<th>{$label}</th>";
    }

    /**
     * Renderiza o HTML da tabela de listagem.
     *
     * @return string HTML da grade
     */
    public function getContents(): string
    {
        $thead = '<tr>';
        if ($this->selectable) {
            $thead .= '<th><input type="checkbox" title="Selecionar todos" onclick="var t=this.closest(\'table\');var c=this.checked;t.querySelectorAll(\'input[type=checkbox]\').forEach(function(i){i.checked=c;});"></th>';
        }
        foreach ($this->columns as $col) {
            $thead .= $this->headerCell($col);
        }
        if ($this->rowActions) {
            $thead .= '<th style="width:160px">Ações</th>';
        }
        foreach ($this->actionGroups as $group) {
            $thead .= '<th>' . htmlspecialchars($group->getLabel()) . '</th>';
        }
        $thead .= '</tr>';

        $tbody = '';
        foreach ($this->items as $item) {
            $tbody .= '<tr>';
            $arr = $item instanceof TRecord ? $item->toArray() : (array) $item;
            if ($this->selectable) {
                $key = (string) ($arr[$this->keyField] ?? '');
                $tbody .= '<td><input type="checkbox" name="_grid_sel[]" value="' . htmlspecialchars($key) . '"></td>';
            }
            foreach ($this->columns as $col) {
                $tbody .= $col->renderCell($item);
            }
            if ($this->rowActions) {
                $tbody .= '<td><div class="btn-group btn-group-sm">';
                foreach ($this->rowActions as $ra) {
                    $display = $ra['display'] ?? null;
                    if ($display !== null && !$display($arr)) {
                        continue;
                    }
                    $a = clone $ra['action'];
                    if (isset($arr['id'])) {
                        $a->setParameter('key', $arr['id']);
                    }
                    $tbody .= "<a class='btn {$ra['btnClass']}' title='" . htmlspecialchars($ra['label']) . "' href='" . $a->toHref() . "'>"
                            . "<i class='fa-solid {$ra['icon']}'></i></a>";
                }
                $tbody .= '</div></td>';
            }
            foreach ($this->actionGroups as $group) {
                $tbody .= $group->renderCell($arr);
            }
            $tbody .= '</tr>';
        }
        if (!$tbody) {
            $span = count($this->columns) + ($this->rowActions ? 1 : 0) + count($this->actionGroups) + ($this->selectable ? 1 : 0);
            $tbody = "<tr><td colspan='{$span}' class='text-center text-muted p-4'>Nenhum registro encontrado</td></tr>";
        }
        $tfoot = $this->renderFooter();
        return "<table class='table table-striped table-hover mb-0'><thead class='table-light'>{$thead}</thead><tbody>{$tbody}</tbody>{$tfoot}</table>";
    }

    /**
     * Renderiza o rodapé com os totais das colunas (vazio se sem totais).
     *
     * @return string HTML do tfoot ou vazio
     */
    protected function renderFooter(): string
    {
        $hasTotal = false;
        foreach ($this->columns as $col) {
            if ($col->hasTotal()) {
                $hasTotal = true;
                break;
            }
        }
        if (!$hasTotal || !$this->items) {
            return '';
        }
        $out = '<tr>';
        if ($this->selectable) {
            $out .= '<td></td>';
        }
        foreach ($this->columns as $col) {
            $out .= $col->hasTotal()
                ? "<td class='fw-bold text-{$col->getAlign()}'>{$col->getTotalValue($this->items)}</td>"
                : '<td></td>';
        }
        if ($this->rowActions) {
            $out .= '<td></td>';
        }
        foreach ($this->actionGroups as $group) {
            $out .= '<td></td>';
        }
        return "<tfoot class='table-light'>{$out}</tfoot>";
    }
}
