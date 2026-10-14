<?php
declare(strict_types=1);

namespace App\Control;

use App\Lib\CrmDemoDatabase;
use App\Model\CrmCliente;
use Aurea\Base\TStandardForm;
use Aurea\Core\TAction;
use Aurea\Form\TCombo;
use Aurea\Form\TEmail;
use Aurea\Form\TEntry;
use Aurea\Form\TFormBuilder;
use Aurea\Form\TLabel;
use Aurea\Validator\TEmailValidator;
use Aurea\Validator\TRequiredValidator;

/**
 * Formulário de cliente do CRM demo.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class CrmClienteForm extends TStandardForm
{
    protected function getDatabase(): string
    {
        CrmDemoDatabase::ensure();
        return 'crm';
    }

    protected function getActiveRecord(): string
    {
        return CrmCliente::class;
    }

    protected function buildForm(): TFormBuilder
    {
        $form = (new TFormBuilder('crm_cliente'))->setFormTitle('Cliente CRM');
        $id = (new TEntry('id'))->setEditable(false);
        $name = new TEntry('nome');
        $email = new TEmail('email');
        $company = new TEntry('empresa');
        $phone = new TEntry('telefone');
        $city = new TEntry('cidade');
        $status = (new TCombo('status'))->addItems([
            'lead' => 'Lead',
            'prospeccao' => 'Prospecção',
            'cliente' => 'Cliente',
        ]);

        $name->addValidation('Nome', new TRequiredValidator);
        $email->addValidation('E-mail', new TEmailValidator);

        $form->addFields([new TLabel('Id')], [$id]);
        $form->addFields([new TLabel('Nome'), new TLabel('Empresa')], [$name, $company]);
        $form->addFields([new TLabel('E-mail'), new TLabel('Telefone')], [$email, $phone]);
        $form->addFields([new TLabel('Cidade'), new TLabel('Estado')], [$city, $status]);
        $form->addAction('Guardar', new TAction([$this, 'onSave']), 'fa-save');
        $form->addActionLink('Clientes', new TAction(['CrmClienteList', 'onShow']), 'fa-list');

        return $form;
    }
}
