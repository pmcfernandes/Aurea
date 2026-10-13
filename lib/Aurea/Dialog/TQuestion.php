<?php
declare(strict_types=1);

namespace Aurea\Dialog;

use Aurea\Core\TAction;
use Aurea\Core\TComponent;


/**
 * TQuestion — diálogo de confirmação com duas ações.
 * Uso no controller: (new TQuestion('Excluir?', new TAction([...])))->show();
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TQuestion extends TComponent
{
    /**
     * Cria um diálogo de confirmação com ações sim/não.
     *
     * @param string $message Pergunta a exibir
     * @param TAction $yesAction Ação do botão Sim
     * @param ?TAction $noAction Ação do botão Não ou null
     */
    public function __construct(string $message, TAction $yesAction, ?TAction $noAction = null)
    {
        parent::__construct('div');
        $this->addClass('alert alert-warning d-flex align-items-center justify-content-between');
        $this->add('<span>' . htmlspecialchars($message) . '</span>');
        $group = new TComponent('span');
        $group->addClass('d-flex gap-2');
        $yes = new TComponent('a');
        $yes->addClass('btn btn-sm btn-danger');
        $yes->setProperty('href', $yesAction->toHref());
        $yes->add('Sim');
        $group->add($yes);
        $no = new TComponent('a');
        $no->addClass('btn btn-sm btn-secondary');
        $no->setProperty('href', $noAction ? $noAction->toHref() : 'javascript:history.back()');
        $no->add('Não');
        $group->add($no);
        $this->add($group);
    }
}
