<?php
declare(strict_types=1);

namespace Aurea\Form;

use Aurea\Core\TAction;
use Aurea\Core\TComponent;


/**
 * TFormBuilder — monta formulários com layout Bootstrap em linhas
 *
 *   $form = new TFormBuilder('form_cliente');
 *   $form->setFormTitle('Cliente');
 *   $form->addFields([new TLabel('Nome')], [$nome]);
 *   $form->addAction('Salvar', new TAction(['ClienteForm','onSave']), 'fa:save');
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TFormBuilder extends TComponent
{
    protected TForm $form;
    protected TComponent $body;
    protected TComponent $footer;
    protected ?string $title = null;
    /** @var string[]|null */
    protected ?array $columnClasses = null;
    protected bool $clientValidation = false;
    /** @var array<int,array{label:string,action:TAction,icon:string}> */
    protected array $headerActions = [];

    /**
     * Cria o construtor de formulário com o nome informado.
     *
     * @param string $name Nome do formulário
     */
    public function __construct(string $name = 'form')
    {
        parent::__construct('div');
        $this->addClass('card');
        $this->form = new TForm($name);
        $this->body = new TComponent('div');
        $this->body->addClass('card-body');
        $this->footer = new TComponent('div');
        $this->footer->addClass('card-footer d-flex gap-2');
        $this->form->add($this->body);
        $this->form->add($this->footer);
        $this->add($this->form);
    }

    /**
     * Define o título do formulário.
     *
     * @param string $title Título do formulário
     * @return static
     */
    public function setFormTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Obtém o formulário interno.
     *
     * @return TForm Formulário interno
     */
    public function getForm(): TForm
    {
        return $this->form;
    }

    /**
     * Define as classes Bootstrap das colunas usadas em addFields (em ciclo).
     *
     * @param array $classes Classes Bootstrap das colunas
     * @return static
     */
    public function setColumnClasses(array $classes): static
    {
        $this->columnClasses = array_values($classes);
        return $this;
    }

    /**
     * Ativa a validação do navegador no submit (checkValidity/reportValidity).
     *
     * @param bool $on True para ativar, false para desativar
     * @return static
     */
    public function setClientValidation(bool $on = true): static
    {
        $this->clientValidation = $on;
        return $this;
    }

    /**
     * Adiciona um link de ação ao cabeçalho do cartão.
     *
     * @param string $label Rótulo do link
     * @param TAction $action Ação a executar
     * @param string $icon Ícone do link
     * @return TComponent Link criado
     */
    public function addHeaderActionLink(string $label, TAction $action, string $icon = ''): TComponent
    {
        $a = new TComponent('a');
        $a->addClass('btn btn-sm btn-outline-secondary');
        $a->setProperty('href', $action->toHref());
        $a->add(($icon ? "<i class='fa-solid {$icon}'></i> " : '') . $label);
        $this->headerActions[] = ['label' => $label, 'action' => $action, 'icon' => $icon];
        return $a;
    }

    /** Uma linha: cada par [labels, campos] vira colunas */

    public function addFields(array $labels, array $fields, ?array $sizes = null): static
    {
        $row = new TComponent('div');
        $row->addClass('row mb-3');
        $cols = max(count($labels), count($fields));
        foreach ($fields as $i => $field) {
            $col = new TComponent('div');
            if ($this->columnClasses) {
                $col->addClass($this->columnClasses[$i % count($this->columnClasses)]);
            } else {
                $col->addClass('col-md-' . (int) (12 / max($cols, 1)));
            }
            if (isset($labels[$i])) {
                $lab = $labels[$i] instanceof TComponent ? $labels[$i] : new TLabel((string) $labels[$i]);
                $col->add($lab);
            }
            if ($field instanceof TField) {
                $this->form->addField($field);
            }
            $col->add($field);
            $row->add($col);
        }
        $this->body->add($row);
        return $this;
    }

    /**
     * Adiciona um conteúdo ao corpo do formulário.
     *
     * @param TComponent|string $content Conteúdo a adicionar
     * @return static
     */
    public function addContent(TComponent|string $content): static
    {
        $this->body->add($content);
        return $this;
    }

    /**
     * Adiciona um botão de ação ao rodapé.
     *
     * @param string $label Rótulo do botão
     * @param TAction $action Ação a executar
     * @param string $icon Ícone do botão
     * @return TComponent Botão criado
     */
    public function addAction(string $label, TAction $action, string $icon = ''): TComponent
    {
        $btn = new TButton(($icon ? "<i class='fa-solid {$icon}'></i> " : '') . $label);
        $btn->setProperty('formaction', $action->serialize());
        // submit do form para a URL do evento (a action carrega params do form)
        $btn->setProperty('onclick', "this.form.action='" . $action->serialize() . "';");
        $this->footer->add($btn);
        return $btn;
    }

    /**
     * Adiciona um link de ação ao rodapé.
     *
     * @param string $label Rótulo do link
     * @param TAction $action Ação a executar
     * @param string $icon Ícone do link
     * @return TComponent Link criado
     */
    public function addActionLink(string $label, TAction $action, string $icon = ''): TComponent
    {
        $a = new TComponent('a');
        $a->addClass('btn btn-outline-secondary');
        $a->setProperty('href', $action->toHref());
        $a->add(($icon ? "<i class='fa-solid {$icon}'></i> " : '') . $label);
        $this->footer->add($a);
        return $a;
    }

    /**
     * Preenche os campos a partir de objeto ou vetor.
     *
     * @param object|array $data Dados a atribuir aos campos
     * @return static
     */
    public function setData(object|array $data): static
    {
        $this->form->setData($data);
        return $this;
    }

    /**
     * Limpa os valores de todos os campos.
     *
     * @return static
     */
    public function clear(): static
    {
        $this->form->clear();
        return $this;
    }

    /**
     * Lê os valores dos campos a partir do POST.
     *
     * @param string|null $class Classe do objeto de retorno
     * @return object Objeto com os valores dos campos
     */
    public function getData(?string $class = null): object
    {
        return $this->form->getData($class);
    }

    /**
     * Valida os valores dos campos.
     *
     * @throws \Exception Quando alguma validação falha
     */
    public function validate(): void
    {
        $this->form->validate();
    }

    /**
     * Obtém o HTML do formulário.
     *
     * @return string HTML do formulário
     */
    public function getContents(): string
    {
        $headerLinks = '';
        foreach ($this->headerActions as $item) {
            $icon = $item['icon'] !== '' ? "<i class='fa-solid {$item['icon']}'></i> " : '';
            $headerLinks .= "<a class='btn btn-sm btn-outline-secondary' href='" . $item['action']->toHref() . "'>{$icon}"
                . htmlspecialchars($item['label']) . '</a>';
        }
        $hasHeader = $this->title || $headerLinks !== '';
        $title = $hasHeader
            ? "<div class='card-header fw-bold d-flex gap-2 align-items-center justify-content-between'><span>"
            . htmlspecialchars((string) $this->title) . '</span>'
            . ($headerLinks !== '' ? "<span class='d-flex gap-2'>{$headerLinks}</span>" : '') . '</div>'
            : '';
        // injeta título antes do form body
        $formHtml = $this->form->getContents();
        if ($title) {
            $formHtml = preg_replace('/<div class="card-body"/', $title . '<div class="card-body"', $formHtml, 1) ?? $formHtml;
        }
        if ($this->clientValidation) {
            $formId = 'form_' . $this->form->getFormName();
            $formHtml = preg_replace(
                '/class="aurea-form"/',
                'class="aurea-form aurea-client-validation"',
                $formHtml,
                1
            ) ?? $formHtml;
            $formHtml .= "<script>(function(){var f=document.getElementById('{$formId}');"
                . 'if(f){f.addEventListener(\'submit\',function(e){'
                . 'if(!f.checkValidity()){e.preventDefault();e.stopPropagation();f.reportValidity();}});}})();</script>';
        }
        // o card wrapper já existe; devolve só o form com card interno
        return $formHtml;
    }
}
