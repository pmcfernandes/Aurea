<?php
declare(strict_types=1);

namespace App\Control;

use Aurea\Container\TPanel;
use Aurea\Container\TTabs;
use Aurea\Container\TVBox;
use Aurea\Core\TAction;
use Aurea\Core\TComponent;
use Aurea\Core\TPage;

/**
 * HomeView — página inicial de exemplo.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class HomeView extends TPage
{
    /**
     * Evento que monta a página inicial (painel, abas e atalhos).
     *
     * @param array $param Requisição
     */
    public function onShow(array $param = []): void
    {
        $panel = new TPanel('Bem-vindo à Aurea Framework');
        $box = new TVBox;

        $info = new TComponent('div');
        $info->addClass('alert alert-info');
        $info->add('Framework PHP <strong>baseada em Componentes e orientada a Eventos</strong>: '
            . 'telas são compostas por componentes (TForm, TEntry, TDataGrid, TPanel, …) e a interação acontece por '
            . 'eventos encapsulados em <code>TAction([Controller, método], params)</code> via Front Controller <code>?class=&method=</code>.');

        $tabs = new TTabs;
        $tabs->appendTab('Componentes', 'TEntry, TCombo, TTextArea, TDate, TCheckGroup, TFormBuilder, TDataGrid, TPanel, TVBox/HBox, TTabs, TMessage, TToast…');
        $tabs->appendTab('Eventos', 'new TAction([\'ClienteList\',\'onReload\']) → index.php?class=ClienteList&method=onReload. Botões, links e ações do grid disparam eventos.');
        $tabs->appendTab('Dados', 'TRecord (Active Record) + TRepository + TCriteria/TFilter + TTransaction.');

        $links = new TComponent('div');
        $links->addClass('d-flex gap-2 mt-3');
        foreach ([
            ['Novo Cliente', 'ClienteForm', 'onShow', 'btn-primary', 'fa-plus'],
            ['Listar Clientes', 'ClienteList', 'onReload', 'btn-outline-primary', 'fa-list'],
        ] as [$label, $class, $method, $btn, $icon]) {
            $a = new TComponent('a');
            $a->addClass("btn {$btn}");
            $a->setProperty('href', (new TAction([$class, $method]))->toHref());
            $a->add("<i class='fa-solid {$icon}'></i> {$label}");
            $links->add($a);
        }

        $box->add($info);
        $box->add($tabs);
        $box->add($links);
        $panel->addToBody($box);
        $this->add($panel);
    }
}
