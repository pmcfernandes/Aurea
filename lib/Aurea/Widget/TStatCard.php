<?php
declare(strict_types=1);

namespace Aurea\Widget;

use Aurea\Core\TAction;
use Aurea\Core\TComponent;

/**
 * TStatCard — cartão de indicador (KPI) com título, valor, ícone e tendência.
 *
 *   $card = new TStatCard('Vendas', 1250);
 *   $card->setIcon('fa-cart-shopping')->setColor('#198754');
 *   $card->setTrend('+12%', 'up');
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TStatCard extends TComponent
{
    protected string $title;
    protected string $value;
    protected string $icon = 'fa-chart-line';
    protected string $color = '#0d6efd';
    protected string $trendText = '';
    protected string $trendDir = 'up';
    protected ?TAction $action = null;

    /**
     * Cria o cartão com o título e o valor informados.
     *
     * @param string $title Título do indicador
     * @param string|int|float $value Valor do indicador
     */
    public function __construct(string $title, string|int|float $value)
    {
        parent::__construct('div');
        $this->title = $title;
        $this->value = (string) $value;
        $this->addClass('card');
    }

    /**
     * Define o ícone do cartão.
     *
     * @param string $icon Classe do ícone
     * @return static
     */
    public function setIcon(string $icon = 'fa-chart-line'): static
    {
        $this->icon = $icon;
        return $this;
    }

    /**
     * Define a cor de destaque do cartão (faixa e ícone).
     *
     * @param string $color Cor em CSS
     * @return static
     */
    public function setColor(string $color = '#0d6efd'): static
    {
        $this->color = $color;
        return $this;
    }

    /**
     * Define o texto e a direcção da tendência do cartão.
     *
     * @param string $text Texto da tendência
     * @param string $dir Direcção da tendência (up ou down)
     * @return static
     */
    public function setTrend(string $text, string $dir = 'up'): static
    {
        $this->trendText = $text;
        $this->trendDir = $dir;
        return $this;
    }

    /**
     * Define a acção executada ao clicar no cartão inteiro.
     *
     * @param TAction|null $action Acção de destino ou null para remover
     * @return static
     */
    public function setAction(?TAction $action): static
    {
        $this->action = $action;
        return $this;
    }

    /**
     * Obtém o HTML do cartão de indicador.
     *
     * @return string HTML do cartão
     */
    public function getContents(): string
    {
        $attrs = $this->renderAttributes();
        $title = htmlspecialchars($this->title, ENT_QUOTES, 'UTF-8');
        $value = htmlspecialchars($this->value, ENT_QUOTES, 'UTF-8');
        $icon = htmlspecialchars($this->icon, ENT_QUOTES, 'UTF-8');
        $color = htmlspecialchars($this->color, ENT_QUOTES, 'UTF-8');

        $trend = '';
        if ($this->trendText !== '') {
            $up = strtolower($this->trendDir) !== 'down';
            $arrow = $up ? '▲' : '▼';
            $tcolor = $up ? '#198754' : '#dc3545';
            $text = htmlspecialchars($this->trendText, ENT_QUOTES, 'UTF-8');
            $trend = "<span style='color:{$tcolor}'>{$arrow} {$text}</span>";
        }

        $link = '';
        if ($this->action !== null) {
            $href = htmlspecialchars($this->action->serialize(), ENT_QUOTES, 'UTF-8');
            $link = "<a href='{$href}' class='stretched-link'></a>";
        }

        return "<div{$attrs}><div style='height:4px;background:{$color}'></div>"
             . "<div class='card-body position-relative'>"
             . "<div class='d-flex align-items-center gap-2'>"
             . "<i class='{$icon}' style='color:{$color}'></i>"
             . "<span class='text-muted'>{$title}</span></div>"
             . "<div class='fs-4 fw-bold'>{$value}</div>"
             . ($trend !== '' ? "<div class='small'>{$trend}</div>" : '')
             . "{$link}</div></div>";
    }
}
