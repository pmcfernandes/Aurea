<?php
declare(strict_types=1);

namespace Aurea\Form;

/**
 * TSelect — seleção nativa simples (single).
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TSelect extends TField
{
    /** @var array<string|string,int|string> */
    protected array $items = [];
    protected bool $multiple = false;

    public function addItems(array $items): static
    {
        $this->items = $items;
        return $this;
    }

    /**
     * Obtém os itens da seleção.
     *
     * @return array Itens chave => rótulo
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * Ativa seleção múltipla.
     *
     * @param bool $multiple True para múltipla
     * @return static
     */
    public function setMultiple(bool $multiple = true): static
    {
        $this->multiple = $multiple;
        return $this;
    }

    /**
     * Obtém o HTML da seleção.
     *
     * @return string HTML do campo
     */
    public function getContents(): string
    {
        $name = htmlspecialchars($this->getName(), ENT_QUOTES);
        $value = $this->getValue();
        $multiple = $this->multiple ? ' multiple' : '';
        $disabled = $this->editable ? '' : ' disabled';
        $html = "<select name=\"{$name}\"" . ($this->multiple ? '' : '') . " class=\"form-select\"{$multiple}{$disabled}>";
        foreach ($this->items as $key => $label) {
            $selected = ((string) $key === (string) $value) ? ' selected' : '';
            $html .= '<option value="' . htmlspecialchars((string) $key, ENT_QUOTES) . '"' . $selected . '>'
                . htmlspecialchars((string) $label, ENT_QUOTES) . '</option>';
        }
        $html .= '</select>';
        return $html;
    }
}
