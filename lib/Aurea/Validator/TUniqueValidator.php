<?php
declare(strict_types=1);

namespace Aurea\Validator;

use Aurea\Database\TTransaction;

/**
 * Valida a unicidade no banco de dados.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TUniqueValidator implements TValidatorInterface
{
    protected ?int $exceptId = null;

    /**
     * Cria o validador de unicidade na base de dados.
     *
     * @param string $database Nome da base de dados (config do TTransaction)
     * @param string $model Classe do Active Record a consultar
     * @param string $field Campo a verificar
     */
    public function __construct(
        protected string $database,
        protected string $model,
        protected string $field
    ) {}

    /**
     * Define o id a ignorar (o próprio registro na edição).
     *
     * @param int|null $id Identificador a ignorar ou null
     * @return static
     */
    public function setExceptId(?int $id): static
    {
        $this->exceptId = $id;
        return $this;
    }

    /**
     * Valida se o valor ainda não existe na tabela (vazio passa).
     *
     * @param string $label Rótulo do campo
     * @param mixed $value Valor a validar
     * @throws \Exception Se o valor já existir noutro registro
     */
    public function validate(string $label, $value): void
    {
        if ($value === null || $value === '' || (is_array($value) && !$value)) {
            return;
        }
        TTransaction::open($this->database);
        $model = $this->model;
        $found = $model::where($this->field, '=', $value)->first();
        if ($found !== null) {
            $id = $found->getPrimaryKeyValue();
            if ($this->exceptId === null || (int) $id !== $this->exceptId) {
                throw new \Exception("O campo {$label} já está em uso.");
            }
        }
    }
}
