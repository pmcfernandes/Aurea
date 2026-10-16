<?php
declare(strict_types=1);

namespace App\Control;

use App\Model\CustomerActivity;
use Aurea\Base\TStandardList;
use Aurea\Core\TAction;
use Aurea\Core\TComponent;
use Aurea\Database\TCriteria;
use Aurea\Database\TFilter;
use Aurea\Form\TEntry;
use Aurea\Form\TForm;
use Aurea\Form\TLabel;
use Aurea\Grid\TColumn;
use Aurea\Grid\TDataGrid;

/** Lista de actividades, com filtro opcional por cliente. */
class CustomerActivityList extends TStandardList
{
    public function __construct()
    {
        parent::__construct();
        $this->setDefaultOrder('scheduled_at', 'desc');
        $this->setOrderFields(['scheduled_at', 'type', 'subject', 'status']);
    }

    protected function getListTitle(): string
    {
        return 'Actividades';
    }

    protected function getDatabase(): string
    {
        return 'app';
    }

    protected function getActiveRecord(): string
    {
        return CustomerActivity::class;
    }

    protected function getFilterFields(): array
    {
        return ['subject', 'type', 'status'];
    }

    protected function buildCriteria(array $param): TCriteria
    {
        $criteria = parent::buildCriteria($param);
        if (isset($param['customer_id']) && ctype_digit((string) $param['customer_id'])) {
            $criteria->add(new TFilter('customer_id', '=', (int) $param['customer_id']));
        }
        return $criteria;
    }

    protected function countCriteria(array $param): TCriteria
    {
        $criteria = parent::countCriteria($param);
        if (isset($param['customer_id']) && ctype_digit((string) $param['customer_id'])) {
            $criteria->add(new TFilter('customer_id', '=', (int) $param['customer_id']));
        }
        return $criteria;
    }

    protected function buildFilter(): TForm
    {
        $form = new TForm('customer_activity_filter');
        $form->addClass('row g-3 align-items-end mb-4');
        $subject = (new TEntry('subject'))->setPlaceholder('Assunto');
        $form->addField($subject);
        $column = new TComponent('div');
        $column->addClass('col-12 col-md-4');
        $label = new TLabel('Assunto');
        $label->setProperty('for', (string) $subject->getProperty('id'));
        $column->add($label);
        $column->add($subject);
        $form->add($column);
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
            ['scheduled_at', 'Data'],
            ['type', 'Tipo'],
            ['subject', 'Assunto'],
            ['status', 'Estado'],
        ] as [$field, $label]) {
            $grid->addColumn((new TColumn($field, $label))->setOrderable());
        }
        $grid->addAction('Editar', new TAction(['CustomerActivityForm', 'onEdit']), 'fa-pen');
        $grid->addAction('Eliminar', new TAction([$this, 'onDelete']), 'fa-trash', 'btn-outline-danger');
        return $grid;
    }
}
