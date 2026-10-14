<?php
declare(strict_types=1);

namespace App\Control;

use App\Model\Cliente;
use Aurea\Base\TStandardListTrait;
use Aurea\Container\TPanel;
use Aurea\Container\TVBox;
use Aurea\Core\TAction;
use Aurea\Core\TPage;
use Aurea\Database\TRepository;
use Aurea\Database\TTransaction;
use Aurea\Dialog\TMessage;
use Aurea\Form\TEntry;
use Aurea\Form\TForm;
use Aurea\Form\TLabel;
use Aurea\Grid\TColumn;
use Aurea\Grid\TDataGrid;
use Aurea\Grid\TPageNavigation;

/**
 * ClienteList — listagem com busca, ordenação, paginação e ações
 * (padrão TStandardList).
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class ClienteList extends TPage
{
    use TStandardListTrait;

    protected TForm $filterForm;
    protected TDataGrid $grid;

    /**
     * Constrói a listagem (filtro, grid ordenável, paginação e exportação).
     */
    public function __construct()
    {
        parent::__construct();
        $this->setDatabase('app');
        $this->setActiveRecord(Cliente::class);
        $this->setFilterFields(['nome', 'cidade']);
        $this->setDefaultOrder('nome', 'asc');

        // --- filtro (submit real: os valores vão no POST para onReload) ---
        $this->filterForm = new TForm('filter_cliente');
        $this->filterForm->setProperty('class', 'd-flex gap-2 align-items-end mb-3');
        $nome = new TEntry('nome');
        $nome->setPlaceholder('Buscar por nome');
        $cidade = new TEntry('cidade');
        $cidade->setPlaceholder('Cidade');
        $this->filterForm->addField($nome);
        $this->filterForm->addField($cidade);
        $this->filterForm->add(new TLabel(''));
        $this->filterForm->add($nome);
        $this->filterForm->add($cidade);
        $this->filterForm->add($this->filterForm->addAction('Buscar', new TAction([$this, 'onReload'])));

        // --- grid (colunas ordenáveis por clique) ---
        $this->grid = new TDataGrid;
        $this->grid->addColumn((new TColumn('id', 'Id', '60px'))->setOrderable());
        $this->grid->addColumn((new TColumn('nome', 'Nome'))->setOrderable());
        $this->grid->addColumn(new TColumn('email', 'E-mail'));
        $this->grid->addColumn((new TColumn('cidade', 'Cidade'))->setOrderable());
        $this->grid->addAction('Editar', new TAction(['ClienteForm', 'onEdit']), 'fa-pen', 'btn-outline-primary');
        $this->grid->addAction('Excluir', new TAction([$this, 'onDelete']), 'fa-trash', 'btn-outline-danger');

        $panel = new TPanel('Clientes');
        $panel->addToBody($this->filterForm);
        $panel->addToBody($this->grid);

        // exportação CSV (mantém os filtros correntes)
        $export = new \Aurea\Core\TComponent('a');
        $export->addClass('btn btn-outline-secondary btn-sm mt-2');
        $export->setProperty('href', (new TAction([$this, 'onExportCSV'], $_GET))->toHref());
        $export->add("<i class='fa-solid fa-file-csv'></i> Exportar CSV");
        $panel->addToBody($export);

        $box = new TVBox;
        $box->add($panel);
        parent::add($box);
    }

    /**
     * Evento que exibe a listagem (atalho para onReload).
     *
     * @param array $param Requisição (filtros, ordem, página)
     */
    public function onShow(array $param = []): void
    {
        $this->onReload($param);
    }

    /**
     * Evento que recarrega o grid (busca, ordenação e paginação).
     *
     * @param array $param Requisição (filtros, order, direction, page)
     */
    public function onReload(array $param = []): void
    {
        try {
            TTransaction::open($this->database);
            $criteria = $this->buildCriteria($param);
            $items = (new TRepository($this->activeRecord))->load($criteria);

            // total sem paginação (só filtros)
            $countCriteria = new \Aurea\Database\TCriteria;
            foreach ($this->filterFields as $field) {
                if (!empty($param[$field])) {
                    $countCriteria->add(new \Aurea\Database\TFilter($field, 'LIKE', '%' . $param[$field] . '%'));
                }
            }
            $total = (new TRepository($this->activeRecord))->count($countCriteria);

            $this->grid->setItems($items);
            $this->filterForm->setData($param);
            $this->grid->setOrder(
                $param['order'] ?? $this->defaultOrder,
                $param['direction'] ?? $this->defaultDirection,
                new TAction([$this, 'onReload'], $param)
            );

            $page = max(1, (int) ($param['page'] ?? 1));
            $pages = max(1, (int) ceil($total / $this->limit));
            parent::add(new TPageNavigation($page, $pages, new TAction([$this, 'onReload'], $param)));

            TTransaction::close();
        } catch (\Throwable $e) {
            (new TMessage('error', $e->getMessage()))->show();
            TTransaction::rollback();
        }
    }

    /** Exporta a listagem filtrada para CSV */

    public function onExportCSV(array $param = []): void
    {
        TTransaction::open($this->database);
        $criteria = $this->buildCriteria($param);
        $criteria->resetProperties(); // sem paginação: exporta tudo o que o filtro devolve
        $rows = (new TRepository($this->activeRecord))->load($criteria);
        TTransaction::close();

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="clientes.csv"');
        echo "\xEF\xBB\xBF"; // BOM para o Excel
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Id', 'Nome', 'E-mail', 'Telefone', 'Cidade'], ';');
        foreach ($rows as $row) {
            $a = $row->toArray();
            fputcsv($out, [$a['id'], $a['nome'], $a['email'], $a['telefone'], $a['cidade']], ';');
        }
        fclose($out);
        exit;
    }
}
