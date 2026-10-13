<?php
declare(strict_types=1);

namespace Aurea\Widget;

use Aurea\Core\TAction;
use Aurea\Core\TComponent;

/**
 * TKanban — quadro com colunas e cartões arrastáveis via SortableJS.
 *
 *   $kanban = new TKanban;
 *   $kanban->addColumn('todo', 'Por fazer');
 *   $kanban->addCard('todo', '1', 'Tarefa', 'Detalhes');
 *   $kanban->setMoveAction(new TAction(['TarefaList', 'onMoveCard']));
 *   $kanban->show();
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TKanban extends TComponent
{
    protected static int $counter = 0;
    protected static bool $libIncluded = false;
    protected string $boardId;
    /** @var array<string,string> */
    protected array $columns = [];
    /** @var array<string,array<int,array{id:string,title:string,body:string}>> */
    protected array $cards = [];
    protected ?TAction $moveAction = null;

    /**
     * Cria um quadro kanban vazio.
     */
    public function __construct()
    {
        parent::__construct('div');
        self::$counter++;
        $this->boardId = 'kanban_' . self::$counter;
        $this->addClass('d-flex gap-3');
    }

    /**
     * Adiciona uma coluna ao quadro.
     *
     * @param string $status Chave da coluna
     * @param string $title Título visível da coluna
     * @return static
     */
    public function addColumn(string $status, string $title): static
    {
        $this->columns[$status] = $title;
        $this->cards[$status] ??= [];
        return $this;
    }

    /**
     * Adiciona um cartão a uma coluna.
     *
     * @param string $status Chave da coluna de destino
     * @param string $id Identificador do cartão
     * @param string $title Título do cartão
     * @param string $body Texto do corpo do cartão
     * @return static
     */
    public function addCard(string $status, string $id, string $title, string $body = ''): static
    {
        if (!isset($this->columns[$status])) {
            $this->addColumn($status, $status);
        }
        $this->cards[$status][] = ['id' => $id, 'title' => $title, 'body' => $body];
        return $this;
    }

    /**
     * Define a ação executada ao mover um cartão entre colunas.
     *
     * @param TAction $action Ação que recebe key e status por POST
     * @return static
     */
    public function setMoveAction(TAction $action): static
    {
        $this->moveAction = $action;
        return $this;
    }

    /**
     * Renderiza o HTML do quadro com colunas, cartões e drag-and-drop.
     *
     * @return string HTML do quadro
     */
    public function getContents(): string
    {
        $lib = '';
        if (!self::$libIncluded) {
            $lib = '<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>';
            self::$libIncluded = true;
        }
        $html = '';
        foreach ($this->columns as $status => $title) {
            $cards = '';
            foreach ($this->cards[$status] ?? [] as $card) {
                $cards .= "<div class='card mb-2 kanban-card' data-id='"
                    . htmlspecialchars($card['id'], ENT_QUOTES) . "'><div class='card-body'><h6 class='card-title'>"
                    . htmlspecialchars($card['title'], ENT_QUOTES) . '</h6>';
                if ($card['body'] !== '') {
                    $cards .= "<p class='card-text'>" . htmlspecialchars($card['body'], ENT_QUOTES) . '</p>';
                }
                $cards .= '</div></div>';
            }
            $html .= "<div class='kanban-col card flex-fill' data-status='" . htmlspecialchars($status, ENT_QUOTES) . "'>"
                . "<div class='card-header fw-bold'>" . htmlspecialchars($title, ENT_QUOTES) . '</div>'
                . "<div class='card-body kanban-list' data-status='" . htmlspecialchars($status, ENT_QUOTES) . "'>{$cards}</div></div>";
        }
        $attrs = $this->renderAttributes();
        $out = "{$lib}<div id='{$this->boardId}'{$attrs}>{$html}</div>";
        if ($this->moveAction !== null) {
            $url = addslashes($this->moveAction->serialize());
            $out .= "<script>document.querySelectorAll('#{$this->boardId} .kanban-list').forEach(function(el){"
                . "new Sortable(el,{group:'kanban',animation:150,onEnd:function(evt){"
                . "var card=evt.item;var list=evt.to;"
                . "fetch('{$url}',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},"
                . "body:new URLSearchParams({key:card.getAttribute('data-id'),status:list.getAttribute('data-status')})});"
                . "}});});</script>";
        } else {
            $out .= "<script>document.querySelectorAll('#{$this->boardId} .kanban-list').forEach(function(el){"
                . "new Sortable(el,{group:'kanban',animation:150});});</script>";
        }
        return $out;
    }
}
