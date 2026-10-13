<?php
declare(strict_types=1);

namespace Aurea\Form;

use Aurea\Database\TCriteria;
use Aurea\Database\TRepository;
use Aurea\Database\TTransaction;


/**
 * Pesquisa com seleção múltipla carregada de um Active Record.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TDBMultiSearch extends TMultiSearch
{
    protected ?TCriteria $criteria = null;

    /**
     * Cria a pesquisa múltipla com origem no banco de dados.
     *
     * @param string $name Nome do campo
     * @param string $database Nome do banco de dados
     * @param string $model Classe do modelo
     * @param string $key Coluna usada como chave
     * @param string $display Coluna usada como descrição
     */
    public function __construct(
        string $name,
        protected string $database,
        protected string $model,
        protected string $key,
        protected string $display
    ) {
        parent::__construct($name);
    }

    /**
     * Define o critério de busca dos itens.
     *
     * @param TCriteria $criteria Critério de busca
     * @return static
     */
    public function setCriteria(TCriteria $criteria): static
    {
        $this->criteria = $criteria;
        return $this;
    }

    /**
     * Carrega os itens a partir do banco de dados.
     */
    protected function loadItems(): void
    {
        TTransaction::open($this->database);
        try {
            $items = [];
            foreach ((new TRepository($this->model))->load($this->criteria) as $obj) {
                $arr = $obj->toArray();
                $items[$arr[$this->key]] = $arr[$this->display] ?? $arr[$this->key];
            }
            $this->addItems($items);
            TTransaction::close();
        } catch (\Throwable) {
            TTransaction::rollback();
            throw new \RuntimeException("Falha ao carregar {$this->getName()} de {$this->model}");
        }
    }

    /**
     * Indica se os itens já foram carregados.
     *
     * @return bool Verdadeiro se os itens já foram carregados
     */
    protected function getItemsLoaded(): bool
    {
        $ref = new \ReflectionProperty(TMultiSearch::class, 'items');
        return (bool) $ref->getValue($this);
    }

    /**
     * Obtém o HTML do campo.
     *
     * @return string HTML do campo
     */
    public function getContents(): string
    {
        if (!$this->getItemsLoaded()) {
            $this->loadItems();
        }
        return parent::getContents();
    }
}
