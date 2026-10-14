<?php
declare(strict_types=1);

namespace Aurea\Form;

/**
 * TMultiSelect — lista de seleção múltipla acessível, submetida como `nome[]`.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TMultiSelect extends TField
{
    /** @var array<string|int,string> */
    protected array $items = [];
    /** @var string[] */
    protected array $selected = [];
    protected int $size = 5;

    /**
     * @param string $name Nome base do campo submetido
     */
    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->tag = 'select';
    }

    /**
     * Define as opções disponíveis.
     *
     * @param array<string|int,string> $items Chave => rótulo
     * @return static
     */
    public function addItems(array $items): static
    {
        $this->items = $items;
        return $this;
    }

    /**
     * Define os valores selecionados.
     *
     * @param mixed $value Valor ou lista de valores selecionados
     * @return static
     */
    public function setValue(mixed $value): static
    {
        $this->selected = array_map('strval', (array) $value);
        $this->value = $value;
        return $this;
    }

    /**
     * Define o número de opções visíveis sem abrir a lista.
     *
     * @param int $size Quantidade mínima de linhas visíveis
     * @return static
     */
    public function setVisibleOptions(int $size): static
    {
        $this->size = max(1, $size);
        return $this;
    }

    /** @return string Select múltiplo com opções escapadas. */
    public function getContents(): string
    {
        $this->setProperty('name', $this->name . '[]');
        $this->setProperty('multiple', 'multiple');
        $this->setProperty('size', (string) $this->size);
        $this->setProperty('aria-label', $this->getProperty('aria-label') ?? $this->name);
        $options = '';
        foreach ($this->items as $key => $label) {
            $value = (string) $key;
            $selected = in_array($value, $this->selected, true) ? ' selected' : '';
            $options .= '<option value="' . htmlspecialchars($value, ENT_QUOTES) . '"' . $selected . '>'
                . htmlspecialchars((string) $label) . '</option>';
        }
        return '<select' . $this->renderAttributes() . '>' . $options . '</select>';
    }
}
