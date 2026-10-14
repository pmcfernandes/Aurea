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
    protected string $tabsId;
    protected int $currentTab = 0;
    /** @var array<int,callable|null> */
    protected array $lazyLoaders = [];

    /**
     * Cria o contentor de abas Bootstrap.
     */
    public function __construct()
    {
        parent::__construct('div');
        try {
            $this->tabsId = 'tabs_' . bin2hex(random_bytes(5));
        } catch (\Throwable) {
            $this->tabsId = 'tabs_' . uniqid();
        }
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
     * Anexa uma aba cujo conteúdo só é construído ao renderizar a aba atual.
     * O callback recebe o índice da aba e devolve conteúdo ou componente.
     */
    public function appendLazyTab(string $title, callable $loader): static
    {
        $this->tabs[] = [$title, null];
        $this->lazyLoaders[array_key_last($this->tabs)] = $loader;
        return $this;
    }

    /** Define a aba inicialmente visível (índice iniciado em zero). */
    public function setCurrentTab(int $index): static
    {
        $this->currentTab = max(0, $index);
        return $this;
    }

    /**
     * Renderiza o HTML das abas Bootstrap.
     *
     * @return string HTML das abas
     */
    public function getContents(): string
    {
        $id = $this->tabsId;
        $activeTab = min($this->currentTab, max(0, count($this->tabs) - 1));
        $nav = "<ul class='nav nav-tabs' role='tablist' aria-label='Abas'>";
        $body = "<div class='tab-content border border-top-0 p-3 bg-white'>";
        foreach ($this->tabs as $i => [$title, $content]) {
            $tabId = "{$id}_tab_{$i}";
            $panelId = "{$id}_panel_{$i}";
            $active = $i === $activeTab ? ' active' : '';
            $selected = $i === $activeTab ? 'true' : 'false';
            $nav .= "<li class='nav-item' role='presentation'><button class='nav-link{$active}' id='{$tabId}' data-bs-toggle='tab' data-bs-target='#{$panelId}' type='button' role='tab' aria-controls='{$panelId}' aria-selected='{$selected}'>"
                   . htmlspecialchars($title) . "</button></li>";
            if ($i === $activeTab && isset($this->lazyLoaders[$i])) {
                $content = ($this->lazyLoaders[$i])($i);
            }
            $html = $content instanceof TComponent ? $content->getContents() : (string) $content;
            $show = $i === $activeTab ? ' show active' : '';
            $body .= "<div class='tab-pane fade{$show}' id='{$panelId}' role='tabpanel' aria-labelledby='{$tabId}' tabindex='0'>{$html}</div>";
        }
        return "<div id='{$id}'>{$nav}</ul>{$body}</div></div>";
    }
}
