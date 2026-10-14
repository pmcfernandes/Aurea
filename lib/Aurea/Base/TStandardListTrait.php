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
    /** @var string[] Campos permitidos para ordenação vinda da requisição */
    protected array $orderFields = [];
    protected int $limit = 10;
    protected bool $persistentFilters = false;
    /** @var callable|null fn(array<string,mixed>, TCriteria):void */
    protected $afterCriteria = null;
    /** @var callable|null fn(array<int,\Aurea\Database\TRecord>):void */
    protected $afterLoad = null;

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
     * Define os únicos campos que podem ser usados em ?order=.
     * Se não for chamado, são permitidos apenas o campo padrão e os filtros.
     *
     * @param string[] $fields
     */
    public function setOrderFields(array $fields): void
    {
        $this->orderFields = array_values(array_filter($fields, 'is_string'));
    }

    /** Define um callback para acrescentar filtros compostos ao critério final. */
    public function setAfterCriteria(?callable $callback): void
    {
        $this->afterCriteria = $callback;
    }

    /** Define um callback executado após carregar uma coleção. */
    public function setAfterLoad(?callable $callback): void
    {
        $this->afterLoad = $callback;
    }

    /**
     * Monta o critério de busca, ordenação e paginação.
     *
     * @param array $param Requisição (GET+POST)
     * @return \Aurea\Database\TCriteria Critério montado
     */
    protected function buildCriteria(array $param): \Aurea\Database\TCriteria
    {
        if ($this->persistentFilters) {
            $param = $this->mergePersistentFilters($param);
        }
        $criteria = new \Aurea\Database\TCriteria;
        foreach ($this->filterFields as $field) {
            if (!empty($param[$field])) {
                $criteria->add(new \Aurea\Database\TFilter($field, 'LIKE', '%' . $param[$field] . '%'));
            }
        }
        $allowedOrders = $this->orderFields ?: array_unique(array_merge([$this->defaultOrder], $this->filterFields));
        $order = (string) ($param['order'] ?? $this->defaultOrder);
        if (!in_array($order, $allowedOrders, true)) {
            $order = $this->defaultOrder;
        }
        $criteria->setProperty('order', $order);
        $criteria->setProperty('direction', $param['direction'] ?? $this->defaultDirection);
        $page = max(1, (int) ($param['page'] ?? 1));
        $criteria->setProperty('limit', $this->limit);
        $criteria->setProperty('offset', ($page - 1) * $this->limit);
        if ($this->afterCriteria !== null) {
            ($this->afterCriteria)($param, $criteria);
        }
        return $criteria;
    }

    /**
     * Ativa/desativa filtros persistentes em sessão.
     *
     * @param bool $on True para guardar e reaplicar filtros em sessão
     */
    public function setPersistentFilters(bool $on = true): void
    {
        $this->persistentFilters = $on;
    }

    /**
     * Chave de sessão usada para guardar os filtros da listagem.
     *
     * @return string Chave de sessão
     */
    protected function filterSessionKey(): string
    {
        return 'list_filters_' . static::class;
    }

    /**
     * Funde filtros do pedido com os guardados em sessão.
     */
    protected function mergePersistentFilters(array $param): array
    {
        if (!empty($param['clear_filters'])) {
            \Aurea\Core\TSession::clear($this->filterSessionKey());
            return $param;
        }
        $stored = \Aurea\Core\TSession::getValue($this->filterSessionKey(), []);
        if (!is_array($stored)) {
            $stored = [];
        }
        foreach ($this->filterFields as $field) {
            if (array_key_exists($field, $param) && $param[$field] !== '' && $param[$field] !== null) {
                $stored[$field] = $param[$field];
            } elseif (!array_key_exists($field, $param) && array_key_exists($field, $stored)) {
                $param[$field] = $stored[$field];
            }
        }
        \Aurea\Core\TSession::setValue($this->filterSessionKey(), $stored);
        return $param;
    }

    /**
     * Apaga os filtros guardados em sessão.
     */
    public function clearFilters(): void
    {
        \Aurea\Core\TSession::clear($this->filterSessionKey());
    }

    /**
     * Elimina vários registros de uma vez.
     *
     * @param int[] $ids Ids a eliminar
     * @return int Total eliminado
     */
    public function deleteCollection(array $ids): int
    {
        $total = 0;
        if (!$ids || $this->database === '' || $this->activeRecord === '') {
            return $total;
        }
        try {
            TTransaction::open($this->database);
            $class = $this->activeRecord;
            foreach ($ids as $id) {
                $obj = new $class((int) $id);
                $obj->delete();
                $total++;
            }
            TTransaction::close();
        } catch (\Throwable $e) {
            TTransaction::rollback();
            throw $e;
        }
        return $total;
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
