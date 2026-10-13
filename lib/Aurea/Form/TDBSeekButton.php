<?php
declare(strict_types=1);

namespace Aurea\Form;

use Aurea\Database\TCriteria;
use Aurea\Database\TRepository;
use Aurea\Database\TTransaction;


/**
 * Botão de pesquisa com sugestões carregadas de um Active Record.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TDBSeekButton extends TSeekButton
{
    protected ?TCriteria $criteria = null;

    /**
     * Cria o botão de pesquisa com origem no banco de dados.
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
     * Obtém o HTML do campo.
     *
     * @return string HTML do campo
     */
    public function getContents(): string
    {
        if (!$this->items) {
            TTransaction::open($this->database);
            try {
                foreach ((new TRepository($this->model))->load($this->criteria) as $obj) {
                    $arr = $obj->toArray();
                    $this->items[$arr[$this->key]] = "[{$arr[$this->key]}] " . ($arr[$this->display] ?? '');
                }
                TTransaction::close();
            } catch (\Throwable) {
                TTransaction::rollback();
            }
        }
        return parent::getContents();
    }
}
