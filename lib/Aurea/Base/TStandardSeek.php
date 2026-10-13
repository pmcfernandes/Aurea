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
use Aurea\Form\TEntry;
use Aurea\Form\TForm;
use Aurea\Grid\TColumn;
use Aurea\Grid\TDataGrid;

/**
 * TStandardSeek — pesquisa para seleção em popup (lookup).
 *
 * Uso: abra a página com window.open('index.php?class=XPTOSeek&method=onShow')
 * a partir do formulário; no opener defina
 * window.__aureaSeekReturn = function(field, key, label) { ... } para receber
 * a linha escolhida. Os nomes dos campos de destino definem-se com
 * setReturnField()/setReturnLabel(). Cada linha tem um botão "Selecionar"
 * que devolve field/key/label via data-attributes e fecha o popup.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
abstract class TStandardSeek extends TPage
{
    protected TForm $filter;
    protected TDataGrid $grid;
    /** @var array<object|array> */
    protected array $seekItems = [];
    protected string $returnField = 'id';
    protected string $returnLabel = 'nome';

    /**
     * Obtém o nome da config em config/.
     *
     * @return string Nome da conexão
     */
    abstract protected function getDatabase(): string;

    /**
     * Obtém a classe do Active Record pesquisado.
     *
     * @return string Classe que estende TRecord
     */
    abstract protected function getActiveRecord(): string;

    /**
     * Obtém os campos pesquisáveis (filtro LIKE + colunas da grid).
     *
     * @return array Nomes dos campos de pesquisa
     */
    abstract protected function getSearchFields(): array;

    /**
     * Obtém o campo chave devolvido ao opener.
     *
     * @return string Nome do campo chave
     */
    abstract protected function getKeyField(): string;

    /**
     * Obtém o campo de exibição devolvido ao opener.
     *
     * @return string Nome do campo de exibição
     */
    abstract protected function getDisplayField(): string;

    /**
     * Constrói a pesquisa (filtro + grid + ajuda de uso em popup).
     */
    public function __construct()
    {
        parent::__construct();

        $this->filter = new TForm('seek_filter');
        foreach ($this->getSearchFields() as $field) {
            $entry = new TEntry($field);
            $entry->setPlaceholder('Buscar por ' . $field);
            $this->filter->addField($entry);
            $this->filter->add($entry);
        }
        $this->filter->add($this->filter->addAction('Buscar', new TAction([$this, 'onSearch'])));

        $this->grid = new TDataGrid;
        foreach ($this->seekColumns() as $field) {
            $this->grid->addColumn(new TColumn($field, ucfirst($field)));
        }

        $panel = new TPanel((new \ReflectionClass($this))->getShortName());
        $panel->addToBody($this->seekHelp());
        $panel->addToBody($this->filter);

        $wrap = new TComponent('div');
        $wrap->setProperty('id', 'seek-grid');
        $wrap->add($this->grid);
        $panel->addToBody($wrap);

        $box = new TVBox;
        $box->add($panel);
        parent::add($box);
    }

    /**
     * Define o nome do campo de destino da chave no opener.
     *
     * @param string $field Nome do campo no formulário de origem
     * @return static
     */
    public function setReturnField(string $field): static
    {
        $this->returnField = $field;
        return $this;
    }

    /**
     * Define o nome do campo de destino do rótulo no opener.
     *
     * @param string $label Nome do campo no formulário de origem
     * @return static
     */
    public function setReturnLabel(string $label): static
    {
        $this->returnLabel = $label;
        return $this;
    }

    /**
     * Exibe a pesquisa (atalho para onSearch).
     *
     * @param array $param Requisição (filtros)
     */
    public function onShow(array $param = []): void
    {
        $this->onSearch($param);
    }

    /**
     * Pesquisa os registros e preenche a grid.
     *
     * @param array $param Requisição (filtros)
     */
    public function onSearch(array $param = []): void
    {
        try {
            TTransaction::open($this->getDatabase());
            $criteria = new TCriteria;
            foreach ($this->getSearchFields() as $field) {
                if (!empty($param[$field])) {
                    $criteria->add(new TFilter($field, 'LIKE', '%' . $param[$field] . '%'));
                }
            }
            $criteria->setProperty('order', $this->getDisplayField());
            $this->seekItems = (new TRepository($this->getActiveRecord()))->load($criteria);
            TTransaction::close();
            $this->grid->setItems($this->seekItems);
            $this->filter->setData($param);
        } catch (\Throwable $e) {
            TTransaction::rollback();
            (new TMessage('error', $e->getMessage()))->show();
        }
    }

    /**
     * Renderiza a página com o script de seleção do popup.
     *
     * @return string HTML da página mais o script de seleção
     */
    public function getContents(): string
    {
        return parent::getContents() . $this->renderSeekScript();
    }

    /**
     * Obtém as colunas da grid (chave + exibição + pesquisa, sem repetir).
     *
     * @return array Nomes dos campos das colunas
     */
    protected function seekColumns(): array
    {
        return array_values(array_unique(array_merge(
            [$this->getKeyField(), $this->getDisplayField()],
            $this->getSearchFields()
        )));
    }

    /**
     * Monta o texto de ajuda sobre o uso em popup.
     *
     * @return TComponent Parágrafo de ajuda
     */
    protected function seekHelp(): TComponent
    {
        $help = new TComponent('p');
        $help->addClass('text-muted small');
        $help->add('Pesquisa para seleção: abra esta página como popup com window.open e defina '
            . 'window.__aureaSeekReturn(field, key, label) no formulário de origem para receber '
            . 'a linha escolhida. Use setReturnField()/setReturnLabel() para indicar os campos de destino.');
        return $help;
    }

    /**
     * Renderiza o script que injeta o botão Selecionar em cada linha.
     *
     * Os botões levam field/key/label em data-attributes e chamam
     * window.opener.__aureaSeekReturn(field, key, label) antes de fechar.
     *
     * @return string Tag script ou vazio sem itens
     */
    protected function renderSeekScript(): string
    {
        if (!$this->seekItems) {
            return '';
        }
        $keyField = $this->getKeyField();
        $displayField = $this->getDisplayField();
        $rows = [];
        foreach ($this->seekItems as $item) {
            $arr = $item instanceof TRecord ? $item->toArray() : (array) $item;
            $rows[] = [
                'key' => (string) ($arr[$keyField] ?? ''),
                'label' => (string) ($arr[$displayField] ?? ''),
            ];
        }
        $json = json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $field = addslashes($this->returnField);
        $label = addslashes($this->returnLabel);
        return "<script>(function(){"
            . "var rows={$json};"
            . "var field='{$field}';var labelField='{$label}';"
            . "var tbody=document.querySelector('#seek-grid table tbody');"
            . "if(!tbody){return;}"
            . "Array.prototype.forEach.call(tbody.rows,function(tr,i){"
            . "var d=rows[i];if(!d){return;}"
            . "var td=document.createElement('td');"
            . "var b=document.createElement('button');"
            . "b.type='button';b.className='btn btn-sm btn-primary';b.textContent='Selecionar';"
            . "b.setAttribute('data-field',field);b.setAttribute('data-key',d.key);b.setAttribute('data-label',d.label);"
            . "b.setAttribute('data-labelfield',labelField);"
            . "b.onclick=function(){"
            . "if(window.opener&&window.opener.__aureaSeekReturn){"
            . "window.opener.__aureaSeekReturn(this.dataset.field,this.dataset.key,this.dataset.label);}"
            . "window.close();};"
            . "td.appendChild(b);tr.appendChild(td);});})();</script>";
    }
}
