<?php
declare(strict_types=1);

namespace Aurea\Form;

use Aurea\Core\TAction;
use Aurea\Core\TComponent;
use Aurea\Core\TCsrf;

/**
 * TForm — contentor <form> com campos nomeados.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TForm extends TComponent
{
    protected string $formName;
    /** @var TField[] */
    protected array $fields = [];
    /** @var array<string,TAction> */
    protected array $actions = [];
    protected bool $csrf = false;

    /**
     * Cria o formulário com o nome informado.
     *
     * @param string $name Nome do formulário
     */
    public function __construct(string $name = 'form')
    {
        parent::__construct('form');
        $this->formName = $name;
        $this->setProperty('name', $name);
        $this->setProperty('id', 'form_' . $name);
        $this->setProperty('method', 'post');
        $this->addClass('aurea-form');
    }

    /**
     * Obtém o nome do formulário.
     *
     * @return string Nome do formulário
     */
    public function getFormName(): string
    {
        return $this->formName;
    }

    /**
     * Adiciona um campo ao formulário.
     *
     * @param TField $field Campo a adicionar
     * @return static
     */
    public function addField(TField $field): static
    {
        $this->fields[$field->getName()] = $field;
        return $this;
    }

    /** @return TField[] */

    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * Obtém um campo pelo nome.
     *
     * @param string $name Nome do campo
     * @return TField|null Campo encontrado ou nulo
     */
    public function getField(string $name): ?TField
    {
        return $this->fields[$name] ?? null;
    }

    /**
     * Adiciona um botão de ação ao formulário.
     *
     * @param string $label Rótulo do botão
     * @param TAction $action Ação a executar
     * @param string $icon Ícone do botão
     * @return TComponent Botão criado
     */
    public function addAction(string $label, TAction $action, string $icon = ''): TComponent
    {
        $btn = new TButton(($icon ? "<i class='{$icon}'></i> " : '') . $label);
        $btn->setProperty('formaction', $action->serialize());
        $this->actions[$label] = $action;
        return $btn;
    }

    /** Preenche os campos a partir de objeto/array */

    public function setData(object|array $data): static
    {
        $arr = (array) $data;
        foreach ($this->fields as $name => $field) {
            if (array_key_exists($name, $arr)) {
                $field->setValue($arr[$name]);
            }
        }
        return $this;
    }

    /** Lê os campos a partir de $_POST */

    public function getData(?string $class = null): object
    {
        $obj = $class ? new $class : new \stdClass;
        foreach ($this->fields as $name => $field) {
            $value = $_POST[$name] ?? null;
            $field->setValue($value);
            $obj->$name = $value;
        }
        return $obj;
    }

    /** @throws \Exception */

    public function validate(): void
    {
        if ($this->csrf && !TCsrf::validate($_POST[TCsrf::FIELD] ?? null)) {
            throw new \Exception('Sessão expirada. Submeta o formulário novamente.');
        }
        foreach ($this->fields as $field) {
            $field->validate();
        }
    }

    /**
     * Ativa a exigência de token CSRF neste formulário (opt-in).
     * O campo hidden é injetado no render e validate() rejeita sem token válido.
     *
     * @param bool $enable Se ativa a proteção
     * @return static
     */
    public function enableCsrf(bool $enable = true): static
    {
        $this->csrf = $enable;
        return $this;
    }

    /**
     * Renderiza o form, injetando o campo CSRF quando ativo.
     *
     * @return string HTML do formulário
     */
    public function getContents(): string
    {
        $html = parent::getContents();
        if ($this->csrf && !str_contains($html, 'name="' . TCsrf::FIELD . '"')) {
            $html = preg_replace('/<\/form>\s*$/', TCsrf::field() . '</form>', $html) ?? $html;
        }
        return $html;
    }

    /**
     * Limpa os valores de todos os campos.
     *
     * @return static
     */
    public function clear(): static
    {
        foreach ($this->fields as $field) {
            $field->setValue(null);
        }
        return $this;
    }
}
