<?php
declare(strict_types=1);

namespace Aurea\Database;

use PDO;

/**
 * TRecord — Active Record base.
 *
 *   class Cliente extends TRecord {
 *       const TABLENAME = 'cliente';
 *       const PRIMARYKEY = 'id';
 *       const IDPOLICY = 'serial'; // ou 'max'
 *   }
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
abstract class TRecord
{
    const TABLENAME = '';
    const PRIMARYKEY = 'id';
    const IDPOLICY = 'serial';

    protected array $data = [];
    protected array $attributes = [];

    /**
     * Cria o registro a partir de array, id ou vazio.
     *
     * @param array|int|null $data Dados iniciais ou chave primária
     * @param bool $callObjectLoad Se chama onAfterLoad após carregar
     * @throws \Exception Se o registro não for encontrado
     */
    public function __construct(array|int|null $data = null, bool $callObjectLoad = true)
    {
        if (is_array($data)) {
            $this->data = $data;
            if ($callObjectLoad && method_exists($this, 'onAfterLoad')) {
                $this->onAfterLoad();
            }
        } elseif ($data !== null) {
            $this->load((int) $data);
        }
    }

    /**
     * Define um valor de atributo do registro.
     *
     * @param string $name Nome do atributo
     * @param mixed $value Valor do atributo
     */
    public function __set(string $name, $value): void
    {
        $this->data[$name] = $value;
    }

    /**
     * Obtém um valor de atributo do registro.
     *
     * @param string $name Nome do atributo
     * @return mixed Valor do atributo ou null
     */
    public function __get(string $name)
    {
        return $this->data[$name] ?? null;
    }

    /**
     * Indica se um atributo do registro está definido.
     *
     * @param string $name Nome do atributo
     * @return bool Verdadeiro se o atributo existe
     */
    public function __isset(string $name): bool
    {
        return isset($this->data[$name]);
    }

    /**
     * Regista um atributo como persistível na tabela.
     *
     * @param string $name Nome do atributo
     */
    protected function addAttribute(string $name): void
    {
        $this->attributes[] = $name;
    }

    /**
     * Preenche o registro a partir de um array.
     *
     * @param array $data Pares atributo/valor
     * @return static
     */
    public function fromArray(array $data): static
    {
        foreach ($data as $k => $v) {
            $this->data[$k] = $v;
        }
        return $this;
    }

    /**
     * Converte o registro para array.
     *
     * @return array Dados do registro
     */
    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * Obtém o valor da chave primária do registro.
     *
     * @return mixed Valor da chave primária ou null
     */
    public function getPrimaryKeyValue()
    {
        $pk = static::PRIMARYKEY;
        return $this->data[$pk] ?? null;
    }

    /**
     * Filtra os dados só com os atributos persistíveis.
     *
     * @return array Dados a gravar na tabela
     */
    protected function persistable(): array
    {
        if (empty($this->attributes)) {
            return $this->data;
        }
        return array_intersect_key($this->data, array_flip($this->attributes));
    }

    /**
     * Grava o registro (insere ou atualiza) na transação ativa.
     *
     * @throws \Exception Se não houver transação aberta
     */
    public function store(): void
    {
        $conn = TTransaction::get();
        $table = static::TABLENAME;
        $pk = static::PRIMARYKEY;
        $data = $this->persistable();
        unset($data[$pk]);

        $id = $this->data[$pk] ?? null;
        if (empty($id)) {
            if (static::IDPOLICY === 'max') {
                $stmt = $conn->query("SELECT COALESCE(MAX({$pk}),0)+1 FROM {$table}");
                $id = (int) $stmt->fetchColumn();
                $cols = array_merge([$pk], array_keys($data));
                $vals = array_merge([$id], array_values($data));
            } else {
                $cols = array_keys($data);
                $vals = array_values($data);
            }
            $ph = implode(',', array_fill(0, count($cols), '?'));
            $sql = "INSERT INTO {$table} (" . implode(',', $cols) . ") VALUES ({$ph})";
            TTransaction::log($sql, $vals);
            $stmt = $conn->prepare($sql);
            $stmt->execute($vals);
            if (static::IDPOLICY === 'serial') {
                $id = (int) $conn->lastInsertId();
            }
            $this->data[$pk] = $id;
        } else {
            $sets = implode(',', array_map(fn($c) => "{$c} = ?", array_keys($data)));
            $vals = array_values($data);
            $vals[] = $id;
            $sql = "UPDATE {$table} SET {$sets} WHERE {$pk} = ?";
            TTransaction::log($sql, $vals);
            $stmt = $conn->prepare($sql);
            $stmt->execute($vals);
        }
    }

    /**
     * Carrega o registro pela chave primária.
     *
     * @param int $id Chave primária do registro
     * @return static
     * @throws \Exception Se o registro não for encontrado
     */
    public function load(int $id): static
    {
        $conn = TTransaction::get();
        $table = static::TABLENAME;
        $pk = static::PRIMARYKEY;
        $sql = "SELECT * FROM {$table} WHERE {$pk} = ?";
        TTransaction::log($sql, [$id]);
        $stmt = $conn->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            throw new \RuntimeException("Registro {$id} não encontrado em {$table}");
        }
        $this->data = $row;
        if (method_exists($this, 'onAfterLoad')) {
            $this->onAfterLoad();
        }
        return $this;
    }

    /**
     * Apaga o registro da tabela.
     *
     * @param int|null $id Chave primária ou null para usar a do objeto
     * @throws \Exception Se não houver transação aberta
     */
    public function delete(?int $id = null): void
    {
        $conn = TTransaction::get();
        $table = static::TABLENAME;
        $pk = static::PRIMARYKEY;
        $id = $id ?? (int) ($this->data[$pk] ?? 0);
        $sql = "DELETE FROM {$table} WHERE {$pk} = ?";
        TTransaction::log($sql, [$id]);
        $stmt = $conn->prepare($sql);
        $stmt->execute([$id]);
    }

    /**
     * Localiza um registro pela chave primária.
     *
     * @param int $id Chave primária do registro
     * @return static Registro carregado
     * @throws \Exception Se o registro não for encontrado
     */
    public static function find(int $id): static
    {
        return new static($id);
    }

    /** @return static[] */

    public static function all(?TCriteria $criteria = null): array
    {
        return (new TRepository(static::class))->load($criteria);
    }

    /**
     * Inicia um construtor de consulta encadeável filtrado.
     *
     * @param string $field Campo do filtro inicial
     * @param string $op Operador do filtro inicial
     * @param mixed $value Valor do filtro inicial
     * @return object Construtor com where/orderBy/take/get/first/count/delete
     */
    public static function where(string $field, string $op, mixed $value): object
    {
        // mini query-builder encadeável: Cliente::where(...)->first()/get()/delete()
        return new class(static::class, $field, $op, $value) {
            private TCriteria $c;
            private string $class;

            /**
             * Cria o construtor de consulta com o filtro inicial.
             *
             * @param string $class Classe do Active Record
             * @param string $f Campo do filtro
             * @param string $o Operador do filtro
             * @param mixed $v Valor do filtro
             */
            public function __construct(string $class, string $f, string $o, mixed $v)
            {
                $this->class = $class;
                $this->c = new TCriteria;
                $this->c->add(new TFilter($f, $o, $v));
            }

            /**
             * Adiciona um filtro à consulta.
             *
             * @param string $f Campo do filtro
             * @param string $o Operador do filtro
             * @param mixed $v Valor do filtro
             * @return self
             */
            public function where(string $f, string $o, mixed $v): self
            {
                $this->c->add(new TFilter($f, $o, $v));
                return $this;
            }

            /**
             * Define a ordenação da consulta.
             *
             * @param string $o Campo de ordenação
             * @param string $d Direção (ASC ou DESC)
             * @return self
             */
            public function orderBy(string $o, string $d = 'ASC'): self
            {
                $this->c->setProperty('order', $o);
                $this->c->setProperty('direction', $d);
                return $this;
            }

            /**
             * Limita a quantidade de registros da consulta.
             *
             * @param int $l Limite de registros
             * @param int $o Deslocamento inicial
             * @return self
             */
            public function take(int $l, int $o = 0): self
            {
                $this->c->setProperty('limit', $l);
                $this->c->setProperty('offset', $o);
                return $this;
            }

            /**
             * Carrega os registros da consulta.
             *
             * @return array Lista de registros encontrados
             * @throws \Exception Se não houver transação aberta
             */
            public function get(): array
            {
                return (new TRepository($this->class))->load($this->c);
            }

            /**
             * Retorna o primeiro registro da consulta.
             *
             * @return ?TRecord Primeiro registro ou null
             * @throws \Exception Se não houver transação aberta
             */
            public function first(): ?TRecord
            {
                $this->c->setProperty('limit', 1);
                $rows = (new TRepository($this->class))->load($this->c);
                return $rows[0] ?? null;
            }

            /**
             * Conta os registros da consulta.
             *
             * @return int Total de registros
             * @throws \Exception Se não houver transação aberta
             */
            public function count(): int
            {
                return (new TRepository($this->class))->count($this->c);
            }

            /**
             * Apaga os registros da consulta.
             *
             * @return int Quantidade de registros apagados
             * @throws \Exception Se não houver transação aberta
             */
            public function delete(): int
            {
                return (new TRepository($this->class))->delete($this->c);
            }
        };
    }
}
