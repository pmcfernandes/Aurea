<?php
declare(strict_types=1);

namespace Aurea\Form;

use Aurea\Core\TComponent;
use Aurea\Validator\TValidatorInterface;


/**
 * Pesquisa com seleção múltipla por caixas.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TMultiSearch extends TField
{
    /** @var array<string,string> chave => rótulo */
    protected array $items = [];
    /** @var string[] chaves pré-selecionadas */
    protected array $checked = [];

    /**
     * Cria a caixa de pesquisa com seleção múltipla.
     *
     * @param string $name Nome do campo
     */
    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->tag = 'div';
    }

    /**
     * Define os itens da lista.
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
     * Define os valores pré-selecionados.
     *
     * @param mixed $value Chaves pré-selecionadas
     * @return static
     */
    public function setValue(mixed $value): static
    {
        $this->checked = array_map('strval', (array) $value);
        $this->value = $value;
        return $this;
    }

    /**
     * Obtém o HTML do campo.
     *
     * @return string HTML do campo
     */
    public function getContents(): string
    {
        $name = $this->getName();
        $listId = 'ms_' . $name;
        $js = "var q=this.value.toLowerCase();document.querySelectorAll('#{$listId} label').forEach(function(l){l.style.display=l.textContent.toLowerCase().indexOf(q)>-1?'':'none'});";
        $out = "<div class='multisearch'><input type='search' id='msq_{$name}' placeholder='Pesquisar...' oninput=\""
             . htmlspecialchars($js, ENT_QUOTES) . '">';
        $out .= "<div id='{$listId}'>";
        foreach ($this->items as $k => $v) {
            $chk = in_array((string) $k, $this->checked, true) ? ' checked' : '';
            $out .= "<label><input type='checkbox' name='{$name}[]' value='"
                  . htmlspecialchars((string) $k, ENT_QUOTES) . "'{$chk}> "
                  . htmlspecialchars((string) $v) . '</label>';
        }
        return $out . '</div></div>';
    }
}
