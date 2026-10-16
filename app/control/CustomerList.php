<?php
declare(strict_types=1);

namespace App\Control;

use App\Model\Customer;
use Aurea\Base\TStandardList;
use Aurea\Core\TAction;
use Aurea\Core\TComponent;
use Aurea\Form\TEntry;
use Aurea\Form\TForm;
use Aurea\Form\TLabel;
use Aurea\Grid\TColumn;
use Aurea\Grid\TDataGrid;

/** Listagem pesquisável de clientes. */
class CustomerList extends TStandardList
{
    protected function getListTitle(): string
    {
        return '';
    }

    protected function getDatabase(): string
    {
        return 'app';
    }

    protected function getActiveRecord(): string
    {
        return Customer::class;
    }

    protected function getFilterFields(): array
    {
        return ['name', 'company', 'city'];
    }

    protected function buildFilter(): TForm
    {
        $form = new TForm('customer_filter');
        $form->addClass('row g-3 align-items-end mb-4');

        $header = new TComponent('div');
        $header->addClass('col-12 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-2');
        $heading = new TComponent('div');
        $title = new TComponent('h1');
        $title->addClass('h4 mb-1');
        $title->addText('Clientes');
        $description = new TComponent('p');
        $description->addClass('text-muted mb-0 small');
        $description->addText('Pesquise, consulte e gira a sua carteira de clientes.');
        $heading->add($title);
        $heading->add($description);
        $header->add($heading);

        $create = new TComponent('a');
        $create->addClass('btn btn-primary customer-create-action');
        $create->setProperty('href', (new TAction(['CustomerForm', 'onShow']))->toHref());
        $create->addHtml('<i class="fa-solid fa-user-plus" aria-hidden="true"></i> Novo cliente');
        $header->add($create);
        $form->add($header);

        foreach (['name' => 'Nome', 'company' => 'Empresa', 'city' => 'Cidade'] as $name => $label) {
            $field = (new TEntry($name))->setPlaceholder($label);
            $form->addField($field);
            $column = new TComponent('div');
            $column->addClass('col-12 col-md-3');
            $fieldLabel = new TLabel($label);
            $fieldLabel->setProperty('for', (string) $field->getProperty('id'));
            $column->add($fieldLabel);
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
        $grid = new TDataGrid();
        foreach ([
            ['name', 'Nome'],
            ['company', 'Empresa'],
            ['email', 'E-mail'],
            ['phone', 'Telefone'],
            ['city', 'Cidade'],
            ['status', 'Estado'],
        ] as [$field, $label]) {
            $grid->addColumn((new TColumn($field, $label))->setOrderable());
        }
        $grid->addAction('Editar', new TAction(['CustomerForm', 'onEdit']), 'fa-pen');
        $grid->addAction('Eliminar', new TAction([$this, 'onDelete']), 'fa-trash', 'btn-outline-danger');
        return $grid;
    }
}
