<?php
declare(strict_types=1);

namespace App\Control;

use App\Model\CustomerOpportunity;
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

/** Lista de oportunidades, com filtro opcional por cliente. */
class CustomerOpportunityList extends TStandardList
{
    protected function getDatabase(): string
    {
        return 'app';
    }

    protected function getActiveRecord(): string
    {
        return CustomerOpportunity::class;
    }

    protected function getFilterFields(): array
    {
        return ['title', 'stage', 'status'];
    }

    protected function buildCriteria(array $param): TCriteria
    {
        $criteria = parent::buildCriteria($param);

        return $this->customerFilter($criteria, $param);
    }

    protected function countCriteria(array $param): TCriteria
    {
        $criteria = parent::countCriteria($param);

        return $this->customerFilter($criteria, $param);
    }

    protected function buildFilter(): TForm
    {
        $form = new TForm('customer_opportunity_filter');
        $form->addClass('row g-3 align-items-end mb-4');
        $title = (new TEntry('title'))->setPlaceholder('Oportunidade');
        $form->addField($title);
        $column = new TComponent('div');
        $column->addClass('col-12 col-md-4');
        $label = new TLabel('Oportunidade');
        $label->setProperty('for', (string) $title->getProperty('id'));
        $column->add($label);
        $column->add($title);
        $form->add($column);

        $actions = new TComponent('div');
        $actions->addClass('col-12 col-md-auto');
        $actions->add($form->addAction('Pesquisar', new TAction([$this, 'onReload']), 'fa-search'));
        $form->add($actions);
        return $form;
    }

    protected function buildGrid(): TDataGrid
    {
        $grid = new TDataGrid();
        foreach ([
            ['title', 'Oportunidade'],
            ['amount', 'Valor'],
            ['stage', 'Fase'],
            ['expected_close_at', 'Fecho previsto'],
            ['status', 'Estado'],
        ] as [$field, $label]) {
            $grid->addColumn((new TColumn($field, $label))->setOrderable());
        }
        $grid->addAction('Editar', new TAction(['CustomerOpportunityForm', 'onEdit']), 'fa-pen');
        $grid->addAction('Eliminar', new TAction([$this, 'onDelete']), 'fa-trash', 'btn-outline-danger');
        return $grid;
    }

    private function customerFilter(TCriteria $criteria, array $param): TCriteria
    {
        if (isset($param['customer_id']) && ctype_digit((string) $param['customer_id'])) {
            $criteria->add(new TFilter('customer_id', '=', (int) $param['customer_id']));
        }
        return $criteria;
    }
}
