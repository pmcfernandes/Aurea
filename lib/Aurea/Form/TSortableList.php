<?php
declare(strict_types=1);

namespace Aurea\Form;

/**
 * TSortableList — lista de itens ordenável por arrasto (SortableJS).
 *
 *   $lista = new TSortableList('ordem');
 *   $lista->addItems(['a' => 'Primeiro', 'b' => 'Segundo']);
 *   $lista->setValue(['b', 'a']);
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TSortableList extends TField
{
    protected static int $counter = 0;
    protected static bool $libIncluded = false;
    protected string $listId;
    /** @var array<string,string> chave => rótulo */
    protected array $items = [];
    /** @var string[] chaves pela ordem pretendida */
    protected array $ordered = [];

    /**
     * Cria a lista ordenável com o nome informado.
     *
     * @param string $name Nome do campo
     */
    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->tag = 'div';
        self::$counter++;
        $this->listId = 'sortable_' . $name . '_' . self::$counter;
    }

    /**
     * Define os itens da lista.
     *
     * @param array $items Itens no formato valor => rótulo
     * @return static
     */
    public function addItems(array $items): static
    {
        $this->items = $items;
        return $this;
    }

    /**
     * Define a ordem das chaves a apresentar.
     *
     * @param array $orderedKeys Chaves pela ordem pretendida
     * @return static
     */
    public function setValue(mixed $value): static
    {
        $this->ordered = array_map('strval', (array) $value);
        $this->value = $value;
        return $this;
    }

    /**
     * Obtém o HTML da lista com os inputs ocultos e o arrasto.
     *
     * @return string HTML da lista
     */
    public function getContents(): string
    {
        $name = $this->getName();
        $byString = [];
        foreach ($this->items as $k => $label) {
            $byString[(string) $k] = $label;
        }
        $keys = array_map('strval', array_keys($byString));
        if ($this->ordered !== []) {
            $wanted = array_values(array_intersect($this->ordered, $keys));
            foreach ($keys as $k) {
                if (!in_array($k, $wanted, true)) {
                    $wanted[] = $k;
                }
            }
            $keys = $wanted;
        }
        $lib = '';
        if (!self::$libIncluded) {
            $lib = '<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>';
            self::$libIncluded = true;
        }
        $lis = '';
        $hiddens = '';
        foreach ($keys as $k) {
            $k = (string) $k;
            $label = $byString[$k] ?? $byString[(int) $k] ?? $k;
            $lis .= "<li data-value='" . htmlspecialchars($k, ENT_QUOTES) . "'>"
                 . htmlspecialchars((string) $label) . '</li>';
            $hiddens .= "<input type='hidden' name='{$name}[]' value='"
                     . htmlspecialchars($k, ENT_QUOTES) . "'>";
        }
        $attrs = $this->renderAttributes();
        $out = "{$lib}<div{$attrs}><ul id='{$this->listId}'>{$lis}</ul>"
             . "<span id='{$this->listId}_inputs'>{$hiddens}</span></div>";
        $out .= "<script>new Sortable(document.getElementById('{$this->listId}'),{animation:150,onEnd:function(){"
             . "var ul=document.getElementById('{$this->listId}');"
             . "var wrap=document.getElementById('{$this->listId}_inputs');"
             . "ul.querySelectorAll('li').forEach(function(li){"
             . "var v=li.getAttribute('data-value');"
             . "var inp=wrap.querySelector('input[value=\"'+v.replace(/\"/g,'\\\"')+'\"]');"
             . "if(inp){wrap.appendChild(inp);}});}});</script>";
        return $out;
    }
}
