<?php
declare(strict_types=1);

namespace App\Control;

use App\Lib\CrmDemoDatabase;
use App\Model\CrmCliente;
use Aurea\Base\TStandardList;
use Aurea\Core\TAction;
use Aurea\Core\TComponent;
use Aurea\Form\TEntry;
use Aurea\Form\TForm;
use Aurea\Grid\TColumn;
use Aurea\Grid\TDataGrid;

/**
 * Listagem de clientes do CRM demo.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class CrmClienteList extends TStandardList
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

    protected function getFilterFields(): array
    {
        return ['nome', 'empresa', 'cidade'];
    }
    protected function buildFilter(): TForm
    {
        $form = new TForm('crm_filter');
        $form->addClass('row g-3 align-items-end mb-4');
        foreach (['nome' => 'Nome', 'empresa' => 'Empresa', 'cidade' => 'Cidade'] as $name => $label) {
            $field = (new TEntry($name))->setPlaceholder($label);
            $form->addField($field);
            $column = new TComponent('div');
            $column->addClass('col-12 col-md-3');
            $column->addHtml('<label class="form-label" for="field_' . $name . '">' . $label . '</label>');
            $column->add($field);
            $form->add($column);
        }
        $actions = new TComponent('div');
        $actions->addClass('col-12 col-md-auto');
        $actions->add($form->addAction('Pesquisar', new TAction([$this, 'onReload']), 'fa-solid fa-search'));
        $form->add($actions);
        return $form;
    }

    protected function buildGrid(): TDataGrid
    {
        $grid = new TDataGrid;
        foreach ([
            ['nome', 'Nome'],
            ['empresa', 'Empresa'],
            ['email', 'E-mail'],
            ['cidade', 'Cidade'],
            ['status', 'Estado'],
        ] as [$field, $label]) {
            $grid->addColumn((new TColumn($field, $label))->setOrderable());
        }
        $grid->addAction('Editar', new TAction(['CrmClienteForm', 'onEdit']), 'fa-pen');
        $grid->addAction('Eliminar', new TAction([$this, 'onDelete']), 'fa-trash', 'btn-outline-danger');
        return $grid;
    }
}
