<?php
declare(strict_types=1);

namespace Aurea\Form;

use Aurea\Core\TComponent;
use Aurea\Validator\TValidatorInterface;


/**
 * Caixa de seleção com pesquisa opcional.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TCombo extends TField
{
    protected array $items = [];
    protected bool $searchable = false;
    protected static bool $searchLib = false;

    /**
     * Cria a caixa de seleção.
     *
     * @param string $name Nome do campo
     */
    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->tag = 'select';
    }

    /**
     * Ativa a pesquisa dentro da caixa (Tom Select, sem jQuery).
     *
     * @param bool $on Se ativa a pesquisa
     * @return static
     */
    public function enableSearch(bool $on = true): static
    {
        $this->searchable = $on;
        return $this;
    }

    /**
     * Define os itens da caixa de seleção.
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
     * Obtém o HTML do campo.
     *
     * @return string HTML do campo
     */
    public function getContents(): string
    {
        $this->sync();
        $attrs = $this->renderAttributes();
        $opts = '<option value="">-- selecione --</option>';
        foreach ($this->items as $k => $v) {
            $sel = ((string) $k === (string) $this->value) ? ' selected' : '';
            $opts .= '<option value="' . htmlspecialchars((string) $k, ENT_QUOTES) . '"' . $sel . '>'
                   . htmlspecialchars((string) $v) . '</option>';
        }
        $html = "<select{$attrs}>{$opts}</select>";
        if ($this->searchable) {
            $lib = '';
            if (!self::$searchLib) {
                $lib = '<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">'
                     . '<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>';
                self::$searchLib = true;
            }
            $id = $this->getProperty('id');
            $html = "{$lib}{$html}<script>if(window.TomSelect){new TomSelect('#{$id}',{create:false,sortField:{field:'text'}});}</script>";
        }
        return $html;
    }
}
