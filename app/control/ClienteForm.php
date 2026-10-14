<?php
declare(strict_types=1);

namespace App\Control;

use App\Model\Cliente;
use Aurea\Base\TStandardFormTrait;
use Aurea\Core\TAction;
use Aurea\Core\TPage;
use Aurea\Container\TVBox;
use Aurea\Form\TEntry;
use Aurea\Form\TEmail;
use Aurea\Form\TFormBuilder;
use Aurea\Form\TLabel;
use Aurea\Validator\TRequiredValidator;
use Aurea\Validator\TEmailValidator;

/**
 * ClienteForm — formulário de cadastro (padrão TStandardForm).
 * Demonstra: componentes + eventos (TAction) + Active Record + validação.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class ClienteForm extends TPage
{
    protected $form;
    use TStandardFormTrait;

    /**
     * Constrói o formulário de cliente (campos, validações e ações).
     */
    public function __construct()
    {
        parent::__construct();
        $this->setDatabase('app');
        $this->setActiveRecord(Cliente::class);

        $this->form = new TFormBuilder('form_cliente');
        $this->form->setFormTitle('Cliente');

        $id       = new TEntry('id');
        $nome     = new TEntry('nome');
        $email    = new TEmail('email');
        $telefone = new TEntry('telefone');
        $cidade   = new TEntry('cidade');

        $nome->addValidation('Nome', new TRequiredValidator);
        $email->addValidation('E-mail', new TRequiredValidator);
        $email->addValidation('E-mail', new TEmailValidator);

        $id->setEditable(false);
        $nome->setPlaceholder('Nome completo');
        $nome->setSize('100%');

        $this->form->addFields([new TLabel('Id')], [$id]);
        $this->form->addFields([new TLabel('Nome')], [$nome]);
        $this->form->addFields([new TLabel('E-mail')], [$email]);
        $this->form->addFields([new TLabel('Telefone')], [$telefone]);
        $this->form->addFields([new TLabel('Cidade')], [$cidade]);

        $this->form->addAction('Salvar', new TAction([$this, 'onSave']), 'fa-save');
        $this->form->addActionLink('Novo', new TAction([$this, 'onEdit']), 'fa-eraser');
        $this->form->addActionLink('Listar', new TAction(['ClienteList', 'onReload']), 'fa-list');

        $box = new TVBox;
        $box->add($this->form);
        parent::add($box);
    }

    /**
     * Evento que exibe o formulário (carrega o registro quando há 'key').
     *
     * @param array $param Requisição (opcional 'key' com o id)
     */
    public function onShow(array $param = []): void
    {
        $this->onEdit($param);
    }
}
