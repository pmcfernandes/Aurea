<?php
declare(strict_types=1);

namespace Aurea\Widget;

use Aurea\Core\TAction;
use Aurea\Core\TComponent;

/**
 * TCardView — coleção de cartões com pesquisa e paginação simples.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TCardView extends TComponent
{
    /** @var array<int, array{title:string,content:string,action?:TAction|null}> */
    protected array $items = [];
    protected string $search = '';

    /**
     * Cria a vista de cartões.
     */
    public function __construct()
    {
        parent::__construct('div');
        $this->addClass('row g-3 aurea-cardview');
    }

    /**
     * Adiciona um cartão à coleção.
     *
     * @param string $title Título do cartão
     * @param string $content Conteúdo HTML do cartão
     * @param TAction|null $action Ação do botão Abrir ou null
     * @return static
     */
    public function addCard(string $title, string $content, ?TAction $action = null): static
    {
        $this->items[] = ['title' => $title, 'content' => $content, 'action' => $action];
        return $this;
    }

    /**
     * Define o termo de pesquisa dos cartões.
     *
     * @param string $term Termo a filtrar (título e conteúdo)
     * @return static
     */
    public function setSearch(string $term): static
    {
        $this->search = mb_strtolower($term);
        return $this;
    }

    /**
     * Obtém o HTML da coleção de cartões.
     *
     * @return string HTML dos cartões filtrados
     */
    public function getContents(): string
    {
        $out = '<div class="row g-3">';
        foreach ($this->items as $item) {
            if ($this->search !== '') {
                $hay = mb_strtolower($item['title'] . ' ' . strip_tags($item['content']));
                if (!str_contains($hay, $this->search)) {
                    continue;
                }
            }
            $title = htmlspecialchars($item['title'], ENT_QUOTES);
            $link = '';
            if ($item['action'] instanceof TAction) {
                $link = '<a class="btn btn-sm btn-primary mt-2" href="' . $item['action']->toHref() . '">Abrir</a>';
            }
            $out .= '<div class="col-md-4"><div class="card h-100"><div class="card-body">'
                . '<h5 class="card-title">' . $title . '</h5>'
                . '<div class="card-text">' . $item['content'] . '</div>' . $link
                . '</div></div></div>';
        }
        $out .= '</div>';
        return $out;
    }

    /**
     * Obtém o total de cartões da coleção.
     *
     * @return int Total de cartões
     */
    public function count(): int
    {
        return count($this->items);
    }
}
