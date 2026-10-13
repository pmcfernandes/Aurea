<?php
declare(strict_types=1);

namespace Aurea\Form;

use Aurea\Core\TAction;
use Aurea\Database\TCriteria;
use Aurea\Database\TRepository;
use Aurea\Database\TTransaction;


/**
 * Campos com origem no banco de dados.
 */

/**
 * TCombo carregado de um Active Record (chave => descrição).
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TDBCombo extends TCombo
{
    protected ?TCriteria $criteria = null;

    /**
     * Cria o combo com origem no banco de dados.
     *
     * @param string $name Nome do campo
     * @param string $database Nome do banco de dados
     * @param string $model Classe do modelo
     * @param string $key Coluna usada como chave
     * @param string $display Coluna usada como descrição
     * @param string|null $orderColumn Coluna de ordenação
     */
    public function __construct(
        string $name,
        protected string $database,
        protected string $model,
        protected string $key,
        protected string $display,
        protected ?string $orderColumn = null
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
            if ($this->orderColumn && !$criteria->getProperty('order')) {
                $criteria->setProperty('order', $this->orderColumn);
            }
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

    /**
     * Indica se os itens já foram carregados.
     *
     * @return bool Verdadeiro se os itens já foram carregados
     */
    protected function getItemsLoaded(): bool
    {
        $ref = new \ReflectionProperty(TCombo::class, 'items');
        return (bool) $ref->getValue($this);
    }
}
