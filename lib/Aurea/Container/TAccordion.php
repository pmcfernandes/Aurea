<?php
declare(strict_types=1);

namespace Aurea\Container;

use Aurea\Core\TComponent;

/**
 * Painel expansível acessível, composto por secções Bootstrap collapse.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TAccordion extends TComponent
{
    private static int $counter = 0;
    /** @var array<int,array{title:string,content:mixed,open:bool}> */
    private array $sections = [];
    private string $id;

    /** Cria o contentor de secções expansíveis. */
    public function __construct()
    {
        parent::__construct('div');
        $this->addClass('accordion');
        $this->id = 'aurea-accordion-' . ++self::$counter;
        $this->setProperty('id', $this->id);
    }

    /**
     * Adiciona uma secção expansível.
     * @param string $title Título visível da secção
     * @param mixed $content Conteúdo ou componente
     * @param bool $open Indica se inicia expandida
     * @return static
     */
    public function addSection(string $title, mixed $content, bool $open = false): static
    {
        $this->sections[] = compact('title', 'content', 'open');
        return $this;
    }

    /** @return string HTML acessível do accordion. */
    public function getContents(): string
    {
        $html = '';
        foreach ($this->sections as $index => $section) {
            $heading = "{$this->id}-heading-{$index}";
            $collapse = "{$this->id}-collapse-{$index}";
            $show = $section['open'] ? ' show' : '';
            $expanded = $section['open'] ? 'true' : 'false';
            $content = $section['content'] instanceof TComponent ? $section['content']->getContents() : (string) $section['content'];
            $html .= "<div class='accordion-item'><h2 class='accordion-header' id='{$heading}'><button class='accordion-button" . ($section['open'] ? '' : ' collapsed') . "' type='button' data-bs-toggle='collapse' data-bs-target='#{$collapse}' aria-expanded='{$expanded}' aria-controls='{$collapse}'>" . htmlspecialchars($section['title']) . "</button></h2><div id='{$collapse}' class='accordion-collapse collapse{$show}' aria-labelledby='{$heading}' data-bs-parent='#{$this->id}'><div class='accordion-body'>{$content}</div></div></div>";
        }
        return '<div' . $this->renderAttributes() . ">{$html}</div>";
    }
}
