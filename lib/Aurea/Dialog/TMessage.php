<?php
declare(strict_types=1);

namespace Aurea\Dialog;

use Aurea\Core\TAction;
use Aurea\Core\TComponent;


/**
 * TMessage — alerta Bootstrap.
 *   new TMessage('info', 'Registro salvo', $posAction);
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TMessage extends TComponent
{
    /**
     * Cria um alerta Bootstrap com botão de fechar.
     *
     * @param string $type Tipo (info, success, warning, error)
     * @param string $text Texto da mensagem
     * @param ?TAction $posAction Ação do botão OK ou null
     */
    public function __construct(string $type, string $text, ?TAction $posAction = null)
    {
        parent::__construct('div');
        $map = ['info' => 'info', 'success' => 'success', 'warning' => 'warning', 'error' => 'danger'];
        $bs = $map[$type] ?? 'info';
        $this->addClass("alert alert-{$bs} alert-dismissible fade show");
        $this->setProperty('role', 'alert');
        $icon = match ($bs) {
            'success' => 'fa-circle-check',
            'danger' => 'fa-circle-exclamation',
            'warning' => 'fa-triangle-exclamation',
            default => 'fa-circle-info',
        };
        $this->add("<i class='fa-solid {$icon}'></i> " . htmlspecialchars($text));
        if ($posAction) {
            $a = new TComponent('a');
            $a->addClass('btn btn-sm btn-outline-dark ms-3');
            $a->setProperty('href', $posAction->toHref());
            $a->add('OK');
            $this->add($a);
        }
        $btn = new TComponent('button');
        $btn->addClass('btn-close');
        $btn->setProperty('type', 'button');
        $btn->setProperty('data-bs-dismiss', 'alert');
        $this->add($btn);
    }
}
