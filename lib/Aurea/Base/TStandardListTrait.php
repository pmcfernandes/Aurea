<?php
declare(strict_types=1);

namespace Aurea\Base;

use Aurea\Database\TTransaction;
use Aurea\Dialog\TMessage;
use Aurea\Core\TAction;


/**
 * TStandardListTrait — listagem padrão com busca, ordenação e paginação
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
trait TStandardListTrait
{
    protected string $database = '';
    protected string $activeRecord = '';
    protected array $filterFields = [];
    protected string $defaultOrder = 'id';
    protected string $defaultDirection = 'asc';
    protected int $limit = 10;

    /**
     * Define o banco de dados da listagem.
     *
     * @param string $database Nome da conexão
     */
    public function setDatabase(string $database): void
    {
        $this->database = $database;
    }

    /**
     * Define o Active Record da listagem.
     *
     * @param string $record Classe do Active Record
     */
    public function setActiveRecord(string $record): void
    {
        $this->activeRecord = $record;
    }

    /**
     * Define os campos usados no filtro de busca.
     *
     * @param array $fields Campos filtráveis
     */
    public function setFilterFields(array $fields): void
    {
        $this->filterFields = $fields;
    }

    /**
     * Define a ordenação padrão da listagem.
     *
     * @param string $order Campo de ordenação
     * @param string $direction Direção (asc|desc)
     */
    public function setDefaultOrder(string $order, string $direction = 'asc'): void
    {
        $this->defaultOrder = $order;
        $this->defaultDirection = $direction;
    }

    /**
     * Monta o critério de busca, ordenação e paginação.
     *
     * @param array $param Requisição (GET+POST)
     * @return \Aurea\Database\TCriteria Critério montado
     */
    protected function buildCriteria(array $param): \Aurea\Database\TCriteria
    {
        $criteria = new \Aurea\Database\TCriteria;
        foreach ($this->filterFields as $field) {
            if (!empty($param[$field])) {
                $criteria->add(new \Aurea\Database\TFilter($field, 'LIKE', '%' . $param[$field] . '%'));
            }
        }
        $criteria->setProperty('order', $param['order'] ?? $this->defaultOrder);
        $criteria->setProperty('direction', $param['direction'] ?? $this->defaultDirection);
        $page = max(1, (int) ($param['page'] ?? 1));
        $criteria->setProperty('limit', $this->limit);
        $criteria->setProperty('offset', ($page - 1) * $this->limit);
        return $criteria;
    }

    /**
     * Exclui o registro e recarrega a listagem.
     *
     * @param array $param Requisição (GET+POST)
     */
    public function onDelete(array $param = []): void
    {
        $key = $param['key'] ?? null;
        if ($key && empty($param['confirm'])) {
            $yes = new TAction([$this, 'onDelete'], ['key' => $key, 'confirm' => 1] + $param);
            (new \Aurea\Dialog\TQuestion('Deseja excluir o registro?', $yes))->show();
        } elseif ($key) {
            try {
                TTransaction::open($this->database);
                $class = $this->activeRecord;
                $obj = new $class((int) $key);
                $obj->delete();
                TTransaction::close();
                (new TMessage('success', 'Registro excluído'))->show();
            } catch (\Throwable $e) {
                (new TMessage('error', $e->getMessage()))->show();
                TTransaction::rollback();
            }
        }
        // recarrega a listagem após excluir (como no TStandardList)
        if (method_exists($this, 'onReload')) {
            $this->onReload($param);
        }
    }
}
