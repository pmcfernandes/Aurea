<?php
declare(strict_types=1);

namespace Aurea\Base;

use Aurea\Database\TTransaction;
use Aurea\Dialog\TMessage;
use Aurea\Core\TAction;


/**
 * TStandardFormTrait — CRUD padrão de formulário.
 *
 * O controller define:
 *   $this->setDatabase('app');
 *   $this->setActiveRecord(Cliente::class);
 * e ganha onSave()/onEdit() prontos.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
trait TStandardFormTrait
{
    protected string $database = '';
    protected string $activeRecord = '';
    protected $form;
    /** @var callable|null fn(object,array<string,mixed>):void */
    protected $beforeStore = null;
    /** @var callable|null fn(object,array<string,mixed>):void */
    protected $afterStore = null;

    /** Define callback executado antes de guardar o Active Record. */
    public function setBeforeStore(?callable $callback): void { $this->beforeStore = $callback; }
    /** Define callback executado depois de guardar o Active Record. */
    public function setAfterStore(?callable $callback): void { $this->afterStore = $callback; }

    /**
     * Define o banco de dados do formulário.
     *
     * @param string $database Nome da conexão
     */
    public function setDatabase(string $database): void
    {
        $this->database = $database;
    }

    /**
     * Define o Active Record do formulário.
     *
     * @param string $record Classe do Active Record
     */
    public function setActiveRecord(string $record): void
    {
        $this->activeRecord = $record;
    }

    /**
     * Salva o registro a partir dos dados do formulário.
     *
     * @param array $param Requisição (GET+POST)
     */
    public function onSave(array $param = []): void
    {
        try {
            TTransaction::open($this->database);
            // preenche os campos com o POST antes de validar (ciclo padrão)
            $this->form->setData($_POST + $param);
            $this->form->validate();
            $data = $this->form->getData();
            $class = $this->activeRecord;
            $object = new $class;
            $object->fromArray((array) $data);
            if ($this->beforeStore !== null) {
                ($this->beforeStore)($object, $param);
            }
            $object->store();
            if ($this->afterStore !== null) {
                ($this->afterStore)($object, $param);
            }
            $data->id = $object->id;
            $this->form->setData($data);
            TTransaction::close();
            (new TMessage('success', 'Registro salvo'))->show();
        } catch (\Throwable $e) {
            (new TMessage('error', $e->getMessage()))->show();
            try {
                $this->form->setData($this->form->getData());
            } catch (\Throwable) {
            }
            TTransaction::rollback();
        }
    }

    /**
     * Carrega o registro no formulário para edição.
     *
     * @param array $param Requisição (GET+POST)
     */
    public function onEdit(array $param = []): void
    {
        try {
            if (!isset($param['key'])) {
                $this->form->clear();
                return;
            }
            TTransaction::open($this->database);
            $class = $this->activeRecord;
            $object = new $class((int) $param['key']);
            $this->form->setData($object->toArray());
            TTransaction::close();
        } catch (\Throwable $e) {
            (new TMessage('error', $e->getMessage()))->show();
            TTransaction::rollback();
        }
    }

    /**
     * Limpa os campos do formulário.
     *
     * @param array $param Requisição (GET+POST)
     */
    public function onClear(array $param = []): void
    {
        $this->form->clear();
    }
}
