<?php
declare(strict_types=1);

namespace Aurea\Widget;

use Aurea\Core\TComponent;

/**
 * TTimeline — linha do tempo vertical com eventos.
 *
 *   $timeline = new TTimeline;
 *   $timeline->addEvent('2026-10-13', 'Lançamento', 'Versão 1.0 publicada.');
 *   $timeline->show();
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TTimeline extends TComponent
{
    /** @var array<int,array{date:string,title:string,body:string,icon:string,color:string}> */
    protected array $events = [];

    /**
     * Cria uma linha do tempo vazia.
     */
    public function __construct()
    {
        parent::__construct('div');
        $this->addClass('aurea-timeline');
    }

    /**
     * Adiciona um evento à linha do tempo.
     *
     * @param string $date Data do evento
     * @param string $title Título do evento
     * @param string $body Texto do corpo do evento
     * @param string $icon Classe do ícone FontAwesome
     * @param string $color Cor do ponto do evento
     * @return static
     */
    public function addEvent(string $date, string $title, string $body = '', string $icon = 'fa-circle', string $color = '#0d6efd'): static
    {
        $this->events[] = [
            'date' => $date,
            'title' => $title,
            'body' => $body,
            'icon' => $icon,
            'color' => $color,
        ];
        return $this;
    }

    /**
     * Renderiza o HTML da linha do tempo com estilos mínimos.
     *
     * @return string HTML da linha do tempo
     */
    public function getContents(): string
    {
        $css = '<style>.aurea-timeline{position:relative;margin:0;padding:0 0 0 28px;list-style:none}'
            . '.aurea-timeline::before{content:"";position:absolute;top:0;bottom:0;left:8px;width:2px;background:#dee2e6}'
            . '.aurea-timeline-item{position:relative;margin-bottom:1rem}'
            . '.aurea-timeline-dot{position:absolute;top:2px;left:-26px;width:14px;height:14px;border-radius:50%;border:2px solid #fff;box-shadow:0 0 0 1px #dee2e6}'
            . '.aurea-timeline-date{font-size:.8rem;color:#6c757d}</style>';
        $items = '';
        foreach ($this->events as $event) {
            $date = htmlspecialchars($event['date'], ENT_QUOTES);
            $title = htmlspecialchars($event['title'], ENT_QUOTES);
            $body = htmlspecialchars($event['body'], ENT_QUOTES);
            $icon = htmlspecialchars($event['icon'], ENT_QUOTES);
            $color = htmlspecialchars($event['color'], ENT_QUOTES);
            $items .= "<div class='aurea-timeline-item'>"
                . "<span class='aurea-timeline-dot' style='background:{$color}'></span>"
                . "<div class='aurea-timeline-date'>{$date}</div>"
                . "<div class='fw-bold'><i class='fa-solid {$icon}'></i> {$title}</div>";
            if ($event['body'] !== '') {
                $items .= "<div class='aurea-timeline-body'>{$body}</div>";
            }
            $items .= '</div>';
        }
        $attrs = $this->renderAttributes();
        return "{$css}<div{$attrs}>{$items}</div>";
    }
}
