<?php
declare(strict_types=1);

namespace Aurea\Container;

use Aurea\Core\TAction;
use Aurea\Core\TComponent;
use Aurea\Form\TField;

/**
 * TStepForm — assistente multi-passo com um único formulário.
 *
 *   $wizard = new TStepForm('cadastro');
 *   $wizard->addStep('Dados', [$nome, $email]);
 *   $wizard->addStep('Morada', [$cidade]);
 *   $wizard->setSaveAction(new TAction(['ClienteForm', 'onSave']));
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TStepForm extends TComponent
{
    protected string $formName;
    /** @var array<int,array{title:string,fields:TField[]}> */
    protected array $steps = [];
    /** @var TField[] */
    protected array $fields = [];
    protected ?TAction $saveAction = null;

    /**
     * Cria o assistente com o nome do formulário informado.
     *
     * @param string $name Nome do formulário
     */
    public function __construct(string $name)
    {
        parent::__construct('div');
        $this->formName = $name;
        $this->addClass('stepform');
    }

    /**
     * Adiciona um passo com título e campos ao assistente.
     *
     * @param string $title Título do passo
     * @param TField[] $fields Campos do passo
     * @return static
     */
    public function addStep(string $title, array $fields): static
    {
        $this->steps[] = ['title' => $title, 'fields' => $fields];
        foreach ($fields as $field) {
            $this->fields[] = $field;
        }
        return $this;
    }

    /**
     * Define a acção de gravação do assistente.
     *
     * @param TAction $action Acção de gravação
     * @return static
     */
    public function setSaveAction(TAction $action): static
    {
        $this->saveAction = $action;
        return $this;
    }

    /**
     * Obtém os dados submetidos dos campos registados.
     *
     * @return object Dados dos campos
     */
    public function getData(): object
    {
        $data = new \stdClass;
        foreach ($this->fields as $field) {
            $name = $field->getName();
            $data->{$name} = $_POST[$name] ?? null;
        }
        return $data;
    }

    /**
     * Obtém o HTML do assistente com indicador de passos e navegação.
     *
     * @return string HTML do assistente
     */
    public function getContents(): string
    {
        $attrs = $this->renderAttributes();
        $base = 'stepform_' . preg_replace('/[^a-zA-Z0-9_]/', '_', $this->formName);
        $formName = htmlspecialchars($this->formName, ENT_QUOTES, 'UTF-8');
        $total = count($this->steps);

        $indicator = "<ol class='stepform-steps d-flex gap-2 list-unstyled'>";
        foreach ($this->steps as $i => $step) {
            $title = htmlspecialchars($step['title'], ENT_QUOTES, 'UTF-8');
            $active = $i === 0 ? ' active fw-bold' : '';
            $indicator .= "<li class='stepform-step{$active}' data-step='{$i}'>" . ($i + 1) . ". {$title}</li>";
        }
        $indicator .= '</ol>';

        $panes = '';
        foreach ($this->steps as $i => $step) {
            $display = $i === 0 ? '' : " style='display:none'";
            $panes .= "<div class='stepform-pane' data-pane='{$i}'{$display}>";
            foreach ($step['fields'] as $field) {
                $label = htmlspecialchars($field->getLabel(), ENT_QUOTES, 'UTF-8');
                $fname = htmlspecialchars($field->getName(), ENT_QUOTES, 'UTF-8');
                $input = $field instanceof TComponent ? $field->getContents() : (string) $field;
                $panes .= "<div class='mb-3'><label class='form-label' for='field_{$fname}'>{$label}</label>{$input}</div>";
            }
            $prev = $i > 0
                ? "<button type='button' class='btn btn-secondary stepform-prev' data-goto='" . ($i - 1) . "'>Anterior</button>"
                : '';
            $next = $i < $total - 1
                ? "<button type='button' class='btn btn-primary stepform-next' data-goto='" . ($i + 1) . "'>Seguinte</button>"
                : '';
            $save = '';
            if ($i === $total - 1) {
                $formaction = $this->saveAction !== null
                    ? " formaction='" . htmlspecialchars($this->saveAction->serialize(), ENT_QUOTES, 'UTF-8') . "'"
                    : '';
                $save = "<button type='submit' class='btn btn-success stepform-save'{$formaction}>Gravar</button>";
            }
            $panes .= "<div class='d-flex gap-2'>{$prev}{$next}{$save}</div></div>";
        }

        $js = "<script>(function(){var root=document.getElementById('{$base}');if(!root)return;"
            . "function show(i){var panes=root.querySelectorAll('.stepform-pane');panes.forEach(function(p){p.style.display=(p.getAttribute('data-pane')==String(i))?'':'none';});"
            . "var steps=root.querySelectorAll('.stepform-step');steps.forEach(function(s){s.classList.toggle('active',s.getAttribute('data-step')==String(i));});}"
            . "root.addEventListener('click',function(e){var b=e.target.closest('.stepform-prev,.stepform-next');if(!b)return;show(b.getAttribute('data-goto'));});})();</script>";

        return "<div{$attrs} id='{$base}'>{$indicator}<form name='{$formName}' method='post'>{$panes}</form>{$js}</div>";
    }
}
