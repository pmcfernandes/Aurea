<?php
declare(strict_types=1);

namespace Aurea\Base;

use Aurea\Container\TPanel;
use Aurea\Container\TVBox;
use Aurea\Core\TAction;
use Aurea\Core\TComponent;
use Aurea\Core\TPage;
use Aurea\Database\TCriteria;
use Aurea\Database\TFilter;
use Aurea\Database\TRecord;
use Aurea\Database\TRepository;
use Aurea\Database\TTransaction;
use Aurea\Dialog\TMessage;
use Aurea\Form\TForm;
use Aurea\Grid\TColumn;
use Aurea\Grid\TDataGrid;
use Aurea\Grid\TPageNavigation;

/**
 * TStandardList — página de listagem a partir do StandardListTrait.
 *
 * A subclasse declara a base de dados, o Active Record, os campos de
 * filtro e a montagem do filtro e da grid; onReload/onDelete/onExportCSV
 * são genéricos. As propriedades $database/$activeRecord/$filterFields do
 * trait são preenchidas no construtor a partir dos métodos abstratos.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
abstract class TStandardList extends TPage
{
    use TStandardListTrait;

    protected TForm $filter;
    protected TDataGrid $grid;

    /**
     * Obtém o nome da config em config/.
     *
     * @return string Nome da conexão
     */
    abstract protected function getDatabase(): string;

    /**
     * Obtém a classe do Active Record da listagem.
     *
     * @return string Classe que estende TRecord
     */
    abstract protected function getActiveRecord(): string;

    /**
     * Obtém os campos usados no filtro de busca.
     *
     * @return array Nomes dos campos filtráveis
     */
    abstract protected function getFilterFields(): array;

    /**
     * Monta o formulário de filtro (campos registados e renderizados).
     *
     * @return TForm Formulário de filtro montado
     */
    abstract protected function buildFilter(): TForm;

    /**
     * Monta a grid de resultados (colunas e ações por linha).
     *
     * @return TDataGrid Grid montada
     */
    abstract protected function buildGrid(): TDataGrid;

    /**
     * Constrói a página (filtro + grid em painel + exportação CSV).
     */
    public function __construct()
    {
        parent::__construct();
        $this->setDatabase($this->getDatabase());
        $this->setActiveRecord($this->getActiveRecord());
        $this->setFilterFields($this->getFilterFields());
        $this->filter = $this->buildFilter();
        $this->grid = $this->buildGrid();

        $panel = new TPanel((new \ReflectionClass($this))->getShortName());
        $panel->addToBody($this->filter);
        $panel->addToBody($this->grid);

        $export = new TComponent('a');
        $export->addClass('btn btn-outline-secondary btn-sm mt-2');
        $export->setProperty('href', (new TAction([$this, 'onExportCSV'], $_GET))->toHref());
        $export->add("<i class='fa-solid fa-file-csv'></i> Exportar CSV");
        $panel->addToBody($export);

        $box = new TVBox;
        $box->add($panel);
        parent::add($box);
    }

    /**
     * Exibe a listagem (atalho para onReload).
     *
     * @param array $param Requisição (filtros, ordem, página)
     */
    public function onShow(array $param = []): void
    {
        $this->onReload($param);
    }

    /**
     * Recarrega a grid (busca, ordenação e paginação).
     *
     * @param array $param Requisição (filtros, order, direction, page)
     */
    public function onReload(array $param = []): void
    {
        try {
            TTransaction::open($this->database);
            $criteria = $this->buildCriteria($param);
            $items = (new TRepository($this->activeRecord))->load($criteria);
            if ($this->afterLoad !== null) {
                ($this->afterLoad)($items);
            }
            $total = (new TRepository($this->activeRecord))->count($this->countCriteria($param));

            $this->grid->setItems($items);
            $this->filter->setData($param);
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

    /**
     * Exporta a listagem filtrada para CSV (sem paginação).
     *
     * Não termina o script, para permitir testes e encadeamento.
     *
     * @param array $param Requisição (filtros correntes)
     */
    public function onExportCSV(array $param = []): void
    {
        try {
            TTransaction::open($this->database);
            $rows = (new TRepository($this->activeRecord))->load($this->exportCriteria($param));
            TTransaction::close();
        } catch (\Throwable $e) {
            TTransaction::rollback();
            (new TMessage('error', $e->getMessage()))->show();
            return;
        }
        if (!headers_sent()) {
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="' . $this->exportFilename() . '"');
        }
        echo $this->buildCsv($rows);
    }

    /**
     * Monta o critério de contagem (só filtros, sem paginação).
     *
     * @param array $param Requisição (filtros)
     * @return TCriteria Critério só com filtros
     */
    protected function countCriteria(array $param): TCriteria
    {
        $criteria = new TCriteria;
        foreach ($this->filterFields as $field) {
            if (!empty($param[$field])) {
                $criteria->add(new TFilter($field, 'LIKE', '%' . $param[$field] . '%'));
            }
        }
        return $criteria;
    }

    /**
     * Monta o critério de exportação (filtros, sem paginação).
     *
     * @param array $param Requisição (filtros)
     * @return TCriteria Critério sem limite/deslocamento
     */
    protected function exportCriteria(array $param): TCriteria
    {
        $criteria = $this->buildCriteria($param);
        $criteria->resetProperties();
        return $criteria;
    }

    /**
     * Gera o conteúdo CSV das linhas (cabeçalho = rótulos das colunas).
     *
     * @param array $rows Registros ou vetores a exportar
     * @return string Conteúdo CSV com BOM
     */
    protected function buildCsv(array $rows): string
    {
        $columns = $this->gridColumns();
        $out = fopen('php://temp', 'w');
        $header = array_map(fn(TColumn $col) => $col->getLabel(), $columns);
        fputcsv($out, $header, ';');
        foreach ($rows as $row) {
            $arr = $row instanceof TRecord ? $row->toArray() : (array) $row;
            $line = [];
            foreach ($columns as $col) {
                $line[] = (string) ($arr[$col->getName()] ?? '');
            }
            fputcsv($out, $line, ';');
        }
        rewind($out);
        $csv = (string) stream_get_contents($out);
        fclose($out);
        return "\xEF\xBB\xBF" . $csv;
    }

    /**
     * Obtém as colunas da grid para o cabeçalho do CSV.
     *
     * @return TColumn[] Colunas da grid
     */
    protected function gridColumns(): array
    {
        $prop = new \ReflectionProperty(TDataGrid::class, 'columns');
        $prop->setAccessible(true);
        $columns = $prop->getValue($this->grid);
        return is_array($columns) ? $columns : [];
    }

    /**
     * Monta o nome do ficheiro CSV a partir do Active Record.
     *
     * @return string Nome do ficheiro CSV
     */
    protected function exportFilename(): string
    {
        $parts = explode('\\', $this->activeRecord);
        return strtolower((string) end($parts)) . '.csv';
    }
}
