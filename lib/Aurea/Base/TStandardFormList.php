<?php
declare(strict_types=1);

namespace Aurea\Base;

use Aurea\Core\TPage;
use Aurea\Form\TForm;
use Aurea\Grid\TDataGrid;

/**
 * Base para páginas CRUD que apresentam formulário e listagem no mesmo ecrã.
 *
 * A subclasse configura a conexão, o Active Record, formulário, filtros e
 * grid. Os handlers onSave, onEdit, onDelete e onReload são fornecidos pelos
 * traits padrão; após gravar, a lista é recarregada.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
abstract class TStandardFormList extends TPage
{
    use TStandardFormTrait, TStandardListTrait {
        TStandardFormTrait::setDatabase insteadof TStandardListTrait;
        TStandardFormTrait::setActiveRecord insteadof TStandardListTrait;
        TStandardFormTrait::onSave as private persistForm;
    }

    protected TForm $filter;
    protected TDataGrid $grid;

    /** @return string Nome da conexão configurada. */
    abstract protected function getDatabase(): string;
    /** @return class-string<\Aurea\Database\TRecord> Classe do Active Record. */
    abstract protected function getActiveRecord(): string;
    /** @return string[] Campos aceites para filtragem. */
    abstract protected function getFilterFields(): array;
    /** @return TForm Formulário de criação/edição. */
    abstract protected function buildForm(): TForm;
    /** @return TForm Formulário de filtro da listagem. */
    abstract protected function buildFilter(): TForm;
    /** @return TDataGrid Grid de resultados. */
    abstract protected function buildGrid(): TDataGrid;

    /** Inicializa e compõe formulário, filtros e grid na página. */
    public function __construct()
    {
        parent::__construct();
        $this->setDatabase($this->getDatabase());
        $this->setActiveRecord($this->getActiveRecord());
        $this->setFilterFields($this->getFilterFields());
        $this->form = $this->buildForm();
        $this->filter = $this->buildFilter();
        $this->grid = $this->buildGrid();
        $this->add($this->form);
        $this->add($this->filter);
        $this->add($this->grid);
    }

    /**
     * Recarrega a grid ao abrir a página.
     * @param array<string,mixed> $param Parâmetros de filtro e paginação
     */
    public function onShow(array $param = []): void
    {
        $this->onReload($param);
    }

    /**
     * Persiste o formulário e atualiza a listagem.
     * @param array<string,mixed> $param Dados submetidos e parâmetros de navegação
     */
    public function onSave(array $param = []): void
    {
        $this->persistForm($param);
        $this->onReload($param);
    }
}
