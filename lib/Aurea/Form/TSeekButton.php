<?php
declare(strict_types=1);

namespace Aurea\Form;

use Aurea\Core\TAction;
use Aurea\Core\TComponent;
use Aurea\Validator\TValidatorInterface;


/**
 * Campo de texto com botão de pesquisa.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TSeekButton extends TField
{
    /** @var array<string,string> chave => rótulo */
    protected array $items = [];
    protected ?TAction $searchAction = null;
    protected string $searchParam = 'q';
    protected string $buttonLabel = '🔍';

    /**
     * Cria o campo de texto com botão de pesquisa.
     *
     * @param string $name Nome do campo
     */
    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->setProperty('type', 'text');
        $this->setProperty('list', 'list_' . $name);
        $this->setProperty('autocomplete', 'off');
    }

    /**
     * Define os itens da lista de sugestões.
     *
     * @param array $items Itens no formato chave => rótulo
     * @return static
     */
    public function addItems(array $items): static
    {
        $this->items = $items;
        return $this;
    }

    /**
     * Define a ação de pesquisa disparada pelo botão.
     *
     * @param TAction $action Ação de pesquisa
     * @param string $param Nome do parâmetro com o valor digitado
     * @return static
     */
    public function setSearchAction(TAction $action, string $param = 'q'): static
    {
        $this->searchAction = $action;
        $this->searchParam = $param;
        return $this;
    }

    /**
     * Define o rótulo do botão de pesquisa.
     *
     * @param string $label Rótulo do botão
     * @return static
     */
    public function setButtonLabel(string $label): static
    {
        $this->buttonLabel = $label;
        return $this;
    }

    /**
     * Obtém o HTML do campo.
     *
     * @return string HTML do campo
     */
    public function getContents(): string
    {
        $this->sync();
        $input = parent::getContents();
        $options = '';
        foreach ($this->items as $k => $label) {
            $options .= '<option value="' . htmlspecialchars((string) $k, ENT_QUOTES) . '">'
                      . htmlspecialchars((string) $label) . '</option>';
        }
        $list = "<datalist id='list_{$this->getName()}'>{$options}</datalist>";
        $id = $this->getProperty('id') ?? 'field_' . $this->getName();
        $onclick = '';
        if ($this->searchAction) {
            $base = $this->searchAction->serialize();
            $sep = str_contains($base, '?') ? '&' : '?';
            $js = "window.location='{$base}{$sep}{$this->searchParam}='+encodeURIComponent(document.getElementById('{$id}').value)";
            $onclick = ' onclick="' . htmlspecialchars($js, ENT_QUOTES) . '"';
        }
        $button = "<button type='button'{$onclick}>" . htmlspecialchars($this->buttonLabel) . '</button>';
        return $input . $list . $button;
    }
}
