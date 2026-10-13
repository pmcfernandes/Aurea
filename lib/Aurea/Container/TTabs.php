<?php
declare(strict_types=1);

namespace Aurea\Container;

use Aurea\Core\TComponent;


/**
 * TTabs Bootstrap simples.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TTabs extends TComponent
{
    protected array $tabs = [];

    /**
     * Cria o contentor de abas Bootstrap.
     */
    public function __construct()
    {
        parent::__construct('div');
    }

    /**
     * Anexa uma aba com título e conteúdo.
     *
     * @param string $title Título da aba
     * @param mixed $content Componente ou texto da aba
     * @return static
     */
    public function appendTab(string $title, $content): static
    {
        $this->tabs[] = [$title, $content];
        return $this;
    }

    /**
     * Renderiza o HTML das abas Bootstrap.
     *
     * @return string HTML das abas
     */
    public function getContents(): string
    {
        $id = 'tabs_' . substr(md5((string) mt_rand()), 0, 8);
        $nav = "<ul class='nav nav-tabs' role='tablist'>";
        $body = "<div class='tab-content border border-top-0 p-3 bg-white'>";
        foreach ($this->tabs as $i => [$title, $content]) {
            $active = $i === 0 ? ' active' : '';
            $nav .= "<li class='nav-item'><button class='nav-link{$active}' data-bs-toggle='tab' data-bs-target='#{$id}_{$i}' type='button'>"
                  . htmlspecialchars($title) . "</button></li>";
            $html = $content instanceof TComponent ? $content->getContents() : (string) $content;
            $show = $i === 0 ? ' show active' : '';
            $body .= "<div class='tab-pane fade{$show}' id='{$id}_{$i}'>{$html}</div>";
        }
        return "<div id='{$id}'>{$nav}</ul>{$body}</div></div>";
    }
}
