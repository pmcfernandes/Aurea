<?php
declare(strict_types=1);

namespace Aurea\Form;

use Aurea\Core\TComponent;
use Aurea\Validator\TValidatorInterface;


/**
 * Botão de formulário com ação de evento.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TButton extends TComponent
{
    /**
     * Cria o botão com o rótulo informado.
     *
     * @param string $label Rótulo do botão
     */
    public function __construct(string $label)
    {
        parent::__construct('button');
        $this->setProperty('type', 'submit');
        $this->addClass('btn btn-primary');
        $this->add($label);
    }

    /**
     * Define a ação executada pelo botão.
     *
     * @param \Aurea\Core\TAction $action Ação a executar
     * @param string|null $confirm Mensagem de confirmação
     * @return static
     */
    public function setAction(\Aurea\Core\TAction $action, ?string $confirm = null): static
    {
        // transforma em botão que navega para o evento
        $this->setProperty('type', 'button');
        $this->setProperty('onclick', $action->toOnClick($confirm));
        return $this;
    }
}
