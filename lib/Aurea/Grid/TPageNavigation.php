<?php
declare(strict_types=1);

namespace Aurea\Grid;

use Aurea\Core\TAction;
use Aurea\Core\TComponent;
use Aurea\Database\TRecord;


/**
 * Paginação simples (primeira/ant/prox).
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TPageNavigation extends TComponent
{
    /**
     * Cria a navegação de páginas da listagem.
     *
     * @param int $page Página atual
     * @param int $pages Total de páginas
     * @param ?TAction $action Ação de troca de página
     */
    public function __construct(
        protected int $page = 1,
        protected int $pages = 1,
        protected ?TAction $action = null
    ) {
        parent::__construct('nav');
    }

    /**
     * Renderiza o HTML da paginação.
     *
     * @return string HTML da paginação ou vazio
     */
    public function getContents(): string
    {
        if ($this->pages <= 1) {
            return '';
        }
        $out = "<ul class='pagination mt-3'>";
        for ($i = 1; $i <= $this->pages; $i++) {
            $active = $i === $this->page ? ' active' : '';
            $href = $this->action
                ? (clone $this->action)->setParameter('page', $i)->toHref()
                : "?page={$i}";
            $out .= "<li class='page-item{$active}'><a class='page-link' href='{$href}'>{$i}</a></li>";
        }
        return $out . '</ul>';
    }
}
