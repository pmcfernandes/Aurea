<?php
declare(strict_types=1);

namespace Aurea\Base;

use Aurea\Core\TPage;
use Aurea\Core\TComponent;
use Aurea\Dialog\TMessage;
use Aurea\Form\TFormBuilder;

/**
 * TStandardForm — página de formulário CRUD a partir do StandardFormTrait.
 *
 * A subclasse só declara a base de dados, o Active Record e a montagem
 * do formulário; onSave/onEdit/onClear vêm do trait.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
abstract class TStandardForm extends TPage
{
    use TStandardFormTrait;

    protected TComponent $messageArea;

    /**
     * Obtém o nome da config em config/.
     *
     * @return string Nome da conexão
     */
    abstract protected function getDatabase(): string;

    /**
     * Obtém a classe do Active Record do formulário.
     *
     * @return string Classe que estende TRecord
     */
    abstract protected function getActiveRecord(): string;

    /**
     * Monta o formulário da página (campos, validações e ações).
     *
     * @return TFormBuilder Formulário montado
     */
    abstract protected function buildForm(): TFormBuilder;

    /**
     * Constrói a página e liga o formulário ao trait.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setDatabase($this->getDatabase());
        $this->setActiveRecord($this->getActiveRecord());
        $this->form = $this->buildForm();
        $this->messageArea = new TComponent('div');
        $this->messageArea->addClass('form-message-area');
        $this->add($this->messageArea);
        $this->add($this->form);
    }

    /** Exibe a mensagem imediatamente antes do formulário. */
    protected function showFormMessage(TMessage $message): void
    {
        $this->messageArea->setChildren([$message]);
    }

    /**
     * Exibe o formulário (carrega o registro quando há 'key').
     *
     * @param array $param Requisição (opcional 'key' com o id)
     */
    public function onShow(array $param = []): void
    {
        $this->onEdit($param);
    }
}
