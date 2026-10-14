<?php
declare(strict_types=1);

namespace Aurea\Container;

use Aurea\Core\TAction;
use Aurea\Core\TComponent;
use Aurea\Grid\TDataGrid;

/**
 * TDataGridWrapper — envolve um TDataGrid num card com título, busca e botões.
 *
 *   $wrapper = new TDataGridWrapper($grid, 'Clientes');
 *   $wrapper->setSearchAction(new TAction(['ClienteList', 'onSearch']));
 *   $wrapper->addHeaderAction('Novo', new TAction(['ClienteForm', 'onEdit']), 'fa-plus');
 *   $wrapper->show();
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TDataGridWrapper extends TComponent
{
    protected TDataGrid $grid;
    protected string $title;
    protected ?TAction $searchAction = null;
    protected string $searchParam = 'q';
    /** @var array<int,array{label:string,action:TAction,icon:string}> */
    protected array $headerActions = [];
    /** @var array<int,TComponent|string> */
    protected array $headerWidgets = [];
    protected string $searchPlaceholder = 'Pesquisar...';
    protected ?string $height = null;

    /**
     * Cria o contentor do TDataGrid com título opcional.
     *
     * @param TDataGrid $grid Grade a envolver
     * @param string $title Título do cartão
     */
    public function __construct(TDataGrid $grid, string $title = '')
    {
        parent::__construct('div');
        $this->grid = $grid;
        $this->title = $title;
        $this->addClass('card mb-3');
    }

    /**
     * Define a ação da busca rápida (input + botão que navega para URL+param).
     *
     * @param TAction $action Ação de destino da busca
     * @param string $param Nome do parâmetro da busca
     * @return static
     */
    public function setSearchAction(TAction $action, string $param = 'q'): static
    {
        $this->searchAction = $action;
        $this->searchParam = $param;
        return $this;
    }

    /**
     * Adiciona um botão ao cabeçalho do cartão.
     *
     * @param string $label Rótulo do botão
     * @param TAction $action Ação do botão
     * @param string $icon Ícone FontAwesome
     * @return static
     */
    public function addHeaderAction(string $label, TAction $action, string $icon = ''): static
    {
        $this->headerActions[] = ['label' => $label, 'action' => $action, 'icon' => $icon];
        return $this;
    }

    /** Adiciona conteúdo adicional ao cabeçalho do card. */
    public function addHeaderWidget(TComponent|string $widget): static
    {
        $this->headerWidgets[] = $widget;
        return $this;
    }

    /** Define o texto de ajuda do campo de busca. */
    public function setSearchPlaceholder(string $placeholder): static
    {
        $this->searchPlaceholder = $placeholder;
        return $this;
    }

    /** Define a altura máxima da área rolável da grelha. */
    public function setHeight(string|int $height): static
    {
        $this->height = is_int($height) ? $height . 'px' : $height;
        return $this;
    }

    /**
     * Renderiza o formulário de busca rápida.
     *
     * @return string HTML do formulário de busca
     */
    protected function renderSearch(): string
    {
        if ($this->searchAction === null) {
            return '';
        }
        $query = parse_url($this->searchAction->serialize(), PHP_URL_QUERY) ?: '';
        parse_str($query, $parts);
        $hidden = '';
        foreach ($parts as $key => $value) {
            if ($key === $this->searchParam) {
                continue;
            }
            $hidden .= "<input type='hidden' name='" . htmlspecialchars((string) $key, ENT_QUOTES)
                . "' value='" . htmlspecialchars((string) $value, ENT_QUOTES) . "'>";
        }
        $param = htmlspecialchars($this->searchParam, ENT_QUOTES);
        $current = htmlspecialchars((string) ($_GET[$this->searchParam] ?? ''), ENT_QUOTES);
        $inputId = 'grid_search_' . substr(md5($this->searchParam . spl_object_id($this)), 0, 8);
        $placeholder = htmlspecialchars($this->searchPlaceholder, ENT_QUOTES);
        return "<form method='get' action='index.php' class='d-flex gap-2' role='search'>{$hidden}"
            . "<label class='visually-hidden' for='{$inputId}'>Pesquisar</label>"
            . "<input id='{$inputId}' type='search' class='form-control form-control-sm' name='{$param}' value='{$current}' placeholder='{$placeholder}'>"
            . "<button type='submit' class='btn btn-sm btn-outline-secondary' aria-label='Pesquisar'><i class='fa-solid fa-search' aria-hidden='true'></i></button></form>";
    }

    /**
     * Renderiza o HTML do cartão com cabeçalho, busca, botões e grade.
     *
     * @return string HTML do contentor
     */
    public function getContents(): string
    {
        $title = htmlspecialchars($this->title, ENT_QUOTES);
        $buttons = '';
        foreach ($this->headerActions as $item) {
            $icon = $item['icon'] !== '' ? "<i class='fa-solid " . htmlspecialchars($item['icon'], ENT_QUOTES) . "'></i> " : '';
            $buttons .= "<a class='btn btn-sm btn-primary' href='" . $item['action']->toHref() . "'>{$icon}"
                . htmlspecialchars($item['label'], ENT_QUOTES) . '</a>';
        }
        $widgets = '';
        foreach ($this->headerWidgets as $widget) {
            $widgets .= $widget instanceof TComponent ? $widget->getContents() : (string) $widget;
        }
        $search = $this->renderSearch();
        $head = "<div class='card-header d-flex flex-wrap gap-2 align-items-center justify-content-between'>"
            . "<span class='fw-bold'>{$title}</span>"
            . "<div class='d-flex gap-2 align-items-center'>{$search}{$widgets}{$buttons}</div></div>";
        $attrs = $this->renderAttributes();
        $style = $this->height === null ? '' : " style='max-height:" . htmlspecialchars($this->height, ENT_QUOTES) . ";overflow:auto'";
        return "<div{$attrs}>{$head}<div class='card-body p-0'{$style}>{$this->grid->getContents()}</div></div>";
    }
}
