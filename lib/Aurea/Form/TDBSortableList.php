<?php
declare(strict_types=1);

namespace Aurea\Form;

use Aurea\Database\TCriteria;
use Aurea\Database\TRepository;
use Aurea\Database\TTransaction;

/**
 * TDBSortableList — lista ordenável carregada de um Active Record.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TDBSortableList extends TSortableList
{
    protected ?TCriteria $criteria = null;

    /**
     * Cria a lista ordenável com origem no banco de dados.
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
            $criteria = $this->criteria ?? new TCriteria;
            $items = [];
            foreach ((new TRepository($this->model))->load($criteria) as $obj) {
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
     * Obtém o HTML da lista, carregando os itens do banco se preciso.
     *
     * @return string HTML da lista
     */
    public function getContents(): string
    {
        if ($this->items === []) {
            $this->loadItems();
        }
        return parent::getContents();
    }
}
