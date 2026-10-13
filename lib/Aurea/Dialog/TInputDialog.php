<?php
declare(strict_types=1);

namespace Aurea\Dialog;

use Aurea\Core\TAction;
use Aurea\Core\TComponent;
use Aurea\Core\TScript;
use Aurea\Form\TField;

/**
 * TInputDialog — modal Bootstrap com formulário dentro.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TInputDialog extends TComponent
{
    protected static int $counter = 0;
    protected string $modalId = '';
    protected string $dialogTitle;
    protected int $dialogWidth;
    /** @var TField[] */
    protected array $fields = [];
    protected ?TAction $saveAction = null;
    protected string $cancelLabel = 'Cancelar';

    /**
     * Cria o diálogo de entrada com o título informado.
     *
     * @param string $title Título do modal
     * @param int $width Largura máxima em pixels
     */
    public function __construct(string $title, int $width = 500)
    {
        parent::__construct('div');
        $this->dialogTitle = $title;
        $this->dialogWidth = $width;
        $this->nextId();
    }

    /**
     * Gera um identificador único para o modal.
     */
    protected function nextId(): void
    {
        self::$counter++;
        $this->modalId = 'inputdlg-' . self::$counter;
        $this->setProperty('id', $this->modalId);
    }

    /**
     * Obtém o identificador do modal.
     *
     * @return string Id do modal
     */
    public function getModalId(): string
    {
        return $this->modalId;
    }

    /**
     * Adiciona um campo ao formulário do diálogo.
     *
     * @param TField $field Campo a registar para render e leitura
     * @return static
     */
    public function addField(TField $field): static
    {
        $this->fields[$field->getName()] = $field;
        return $this;
    }

    /**
     * Define a ação de gravação (destino do submit).
     *
     * @param TAction $action Ação para onde o formulário faz submit
     * @return static
     */
    public function setSaveAction(TAction $action): static
    {
        $this->saveAction = $action;
        return $this;
    }

    /**
     * Define o rótulo do botão de cancelamento.
     *
     * @param string $label Rótulo do botão Cancelar
     * @return static
     */
    public function setCancelLabel(string $label): static
    {
        $this->cancelLabel = $label;
        return $this;
    }

    /**
     * Lê os valores submetidos dos campos registados.
     *
     * @return object Objeto com os valores de $_POST por nome de campo
     */
    public function getData(): object
    {
        $data = new \stdClass;
        foreach ($this->fields as $name => $field) {
            $value = $_POST[$name] ?? null;
            $field->setValue($value);
            $data->$name = $value;
        }
        return $data;
    }

    /**
     * Renderiza o HTML do modal com o formulário.
     *
     * @return string HTML do modal
     */
    public function getContents(): string
    {
        $actionUrl = $this->saveAction ? $this->saveAction->serialize() : '';
        $out = '<div class="modal fade" id="' . htmlspecialchars($this->modalId, ENT_QUOTES) . '" tabindex="-1">';
        $out .= '<div class="modal-dialog" style="max-width:' . $this->dialogWidth . 'px">';
        $out .= '<div class="modal-content">';
        $out .= '<div class="modal-header">';
        $out .= '<h5 class="modal-title">' . htmlspecialchars($this->dialogTitle, ENT_QUOTES) . '</h5>';
        $out .= '<button type="button" class="btn-close" data-bs-dismiss="modal"></button>';
        $out .= '</div>';
        $out .= '<form method="post" action="' . htmlspecialchars($actionUrl, ENT_QUOTES) . '">';
        $out .= '<div class="modal-body">';
        foreach ($this->fields as $field) {
            $out .= '<div class="mb-3">';
            $out .= '<label class="form-label">' . htmlspecialchars($field->getLabel(), ENT_QUOTES) . '</label>';
            $out .= $field->getContents();
            $out .= '</div>';
        }
        $out .= '</div>';
        $out .= '<div class="modal-footer">';
        $out .= '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">'
            . htmlspecialchars($this->cancelLabel, ENT_QUOTES) . '</button>';
        $out .= '<button type="submit" class="btn btn-primary">Gravar</button>';
        $out .= '</div>';
        $out .= '</form>';
        $out .= '</div>';
        $out .= '</div>';
        $out .= '</div>';
        return $out;
    }

    /**
     * Imprime o modal e abre-o via JavaScript.
     */
    public function show(): void
    {
        $this->nextId();
        echo $this->getContents();
        TScript::showModal($this->modalId);
        echo TScript::render();
    }
}
