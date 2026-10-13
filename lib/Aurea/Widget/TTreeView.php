<?php
declare(strict_types=1);

namespace Aurea\Widget;

use Aurea\Core\TAction;
use Aurea\Core\TComponent;

/**
 * TTreeView — árvore de itens com expandir/colapsar (sem dependências externas).
 *
 *   $tree = new TTreeView;
 *   $tree->addItem('a', null, 'Raiz');
 *   $tree->addItem('b', 'a', 'Filho', new TAction(['Pagina', 'onVer'], ['id' => 1]));
 *   $tree->setSelected('b');
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TTreeView extends TComponent
{
    protected static int $counter = 0;
    protected string $treeId;
    /** @var array<string,array{parent:?string,label:string,action:?TAction}> */
    protected array $items = [];
    protected ?string $selected = null;

    /**
     * Cria uma árvore vazia.
     */
    public function __construct()
    {
        parent::__construct('div');
        self::$counter++;
        $this->treeId = 'treeview_' . self::$counter;
        $this->addClass('treeview');
    }

    /**
     * Adiciona um item à árvore.
     *
     * @param string $id Identificador do item
     * @param string|null $parentId Identificador do item pai (null para raiz)
     * @param string $label Rótulo visível do item
     * @param TAction|null $action Ação executada ao clicar no item
     * @return static
     */
    public function addItem(string $id, ?string $parentId, string $label, ?TAction $action = null): static
    {
        $this->items[$id] = ['parent' => $parentId, 'label' => $label, 'action' => $action];
        return $this;
    }

    /**
     * Marca um item como selecionado.
     *
     * @param string $id Identificador do item
     * @return static
     */
    public function setSelected(string $id): static
    {
        $this->selected = $id;
        return $this;
    }

    /**
     * Obtém o HTML da árvore com as listas aninhadas e os toggles.
     *
     * @return string HTML da árvore
     */
    public function getContents(): string
    {
        $children = [];
        foreach ($this->items as $id => $item) {
            $children[$item['parent'] ?? ''][] = $id;
        }
        $attrs = $this->renderAttributes();
        $out = "<div id='{$this->treeId}'{$attrs}>" . $this->renderBranch(null, $children, 0) . '</div>';
        $out .= "<script>(function(){var t=document.getElementById('{$this->treeId}');"
             . "t.addEventListener('click',function(e){"
             . "var tg=e.target.closest('[data-tree-toggle]');"
             . "if(!tg){return;}"
             . "var li=tg.closest('li');var ul=li.querySelector(':scope > ul');"
             . "if(!ul){return;}"
             . "var open=ul.style.display!=='none';"
             . "ul.style.display=open?'none':'';"
             . "tg.textContent=open?'▸':'▾';});})();</script>";
        return $out;
    }

    /**
     * Renderiza um ramo da árvore e os seus descendentes.
     *
     * @param string|null $parentId Identificador do item pai
     * @param array $children Mapa de pai para lista de filhos
     * @param int $level Nível de profundidade do ramo
     * @return string HTML do ramo
     */
    protected function renderBranch(?string $parentId, array $children, int $level): string
    {
        $key = $parentId ?? '';
        if (empty($children[$key])) {
            return '';
        }
        $html = "<ul data-tree-level='{$level}'>";
        foreach ($children[$key] as $id) {
            $item = $this->items[$id];
            $hasKids = !empty($children[$id]);
            $toggle = $hasKids ? "<span data-tree-toggle='1' style='cursor:pointer'>▾</span> " : '';
            $label = htmlspecialchars($item['label'], ENT_QUOTES);
            if ($item['action'] !== null) {
                $href = htmlspecialchars($item['action']->serialize(), ENT_QUOTES);
                $label = "<a href='{$href}'>{$label}</a>";
            }
            $sel = ($this->selected === $id) ? " data-selected='1' class='selected'" : '';
            $html .= "<li data-tree-id='" . htmlspecialchars($id, ENT_QUOTES) . "'{$sel}>"
                  . "{$toggle}{$label}" . $this->renderBranch($id, $children, $level + 1) . '</li>';
        }
        return $html . '</ul>';
    }
}
