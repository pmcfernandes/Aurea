<?php
declare(strict_types=1);

namespace Aurea\Form;

use Aurea\Core\TAction;

/**
 * TSearchField — autocomplete via AJAX para tabelas grandes.
 * Grava o id num input hidden e exibe o rótulo num input de texto
 * com lista de resultados obtida em JSON do endpoint de busca.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TSearchField extends TField
{
    protected ?TAction $searchAction = null;
    protected string $idKey = 'id';
    protected string $labelKey = 'label';
    protected int $minLength = 2;
    protected string $visibleLabel = '';

    /**
     * Cria o campo de busca com autocomplete.
     *
     * @param string $name Nome do campo
     */
    public function __construct(string $name)
    {
        parent::__construct($name);
    }

    /**
     * Define a ação de busca (endpoint GET que devolve JSON).
     *
     * @param TAction $action Ação do endpoint de busca
     * @return static
     */
    public function setSearchAction(TAction $action): static
    {
        $this->searchAction = $action;
        return $this;
    }

    /**
     * Define a chave do id nos itens do JSON.
     *
     * @param string $k Nome da chave do id
     * @return static
     */
    public function setIdKey(string $k = 'id'): static
    {
        $this->idKey = $k;
        return $this;
    }

    /**
     * Define a chave do rótulo nos itens do JSON.
     *
     * @param string $k Nome da chave do rótulo
     * @return static
     */
    public function setLabelKey(string $k = 'label'): static
    {
        $this->labelKey = $k;
        return $this;
    }

    /**
     * Define o tamanho mínimo para disparar a busca.
     *
     * @param int $n Tamanho mínimo permitido
     * @return static
     */
    public function setMinLength(int $n = 2): static
    {
        $this->minLength = $n;
        return $this;
    }

    /**
     * Define o id selecionado e o rótulo visível.
     *
     * @param mixed $id Id a gravar no campo hidden
     * @param string $label Rótulo a exibir no campo de texto
     * @return static
     */
    public function setValue(mixed $id, string $label = ''): static
    {
        $this->value = $id;
        $this->visibleLabel = $label;
        return $this;
    }

    /**
     * Obtém o HTML do campo (hidden + texto + resultados + JS).
     *
     * @return string HTML do campo
     */
    public function getContents(): string
    {
        $name = $this->getName();
        $id = is_array($this->value) ? '' : (string) ($this->value ?? '');
        $escName = htmlspecialchars($name, ENT_QUOTES);
        $hiddenId = 'field_' . $name;
        $labelId = 'field_' . $name . '_label';
        $boxId = 'search_' . $name . '_results';

        $hidden = '<input type="hidden" id="' . htmlspecialchars($hiddenId, ENT_QUOTES)
            . '" name="' . $escName . '" value="' . htmlspecialchars($id, ENT_QUOTES) . '">';

        $placeholder = $this->getProperty('placeholder');
        $phAttr = $placeholder !== null ? ' placeholder="' . htmlspecialchars($placeholder, ENT_QUOTES) . '"' : '';
        $disabled = $this->editable ? '' : ' disabled="disabled"';
        $visible = '<input type="text" id="' . htmlspecialchars($labelId, ENT_QUOTES)
            . '" name="' . $escName . '_label" value="' . htmlspecialchars($this->visibleLabel, ENT_QUOTES)
            . '" autocomplete="off" class="form-control" minlength="' . $this->minLength . '"'
            . $phAttr . $disabled . '>';

        $box = '<div id="' . htmlspecialchars($boxId, ENT_QUOTES)
            . '" class="list-group" style="position:absolute;z-index:1000;width:100%"></div>';

        $base = $this->searchAction ? $this->searchAction->serialize() : '';
        $sep = str_contains($base, '?') ? '&' : '?';
        $jsUrl = addslashes($base . $sep);
        $jsHiddenId = addslashes($hiddenId);
        $jsLabelId = addslashes($labelId);
        $jsBoxId = addslashes($boxId);
        $jsIdKey = json_encode($this->idKey);
        $jsLabelKey = json_encode($this->labelKey);

        $js = "<script>(function(){"
            . "var lbl=document.getElementById('{$jsLabelId}');"
            . "var hdn=document.getElementById('{$jsHiddenId}');"
            . "var box=document.getElementById('{$jsBoxId}');"
            . "if(!lbl||!hdn||!box){return;}"
            . "var t=null;"
            . "lbl.addEventListener('input',function(){"
            . "hdn.value='';"
            . "var q=lbl.value;"
            . "if(t){clearTimeout(t);}"
            . "if(q.length<{$this->minLength}){box.innerHTML='';return;}"
            . "t=setTimeout(function(){"
            . "fetch('{$jsUrl}q='+encodeURIComponent(q))"
            . ".then(function(r){return r.json();})"
            . ".then(function(json){"
            . "var items=Array.isArray(json)?json:(json&&Array.isArray(json.data)?json.data:[]);"
            . "box.innerHTML='';"
            . "items.forEach(function(it){"
            . "var d=document.createElement('button');"
            . "d.setAttribute('type','button');"
            . "d.className='list-group-item list-group-item-action';"
            . "d.textContent=(it[{$jsLabelKey}]!==undefined&&it[{$jsLabelKey}]!==null)?it[{$jsLabelKey}]:'';"
            . "d.addEventListener('click',function(){"
            . "hdn.value=(it[{$jsIdKey}]!==undefined&&it[{$jsIdKey}]!==null)?it[{$jsIdKey}]:'';"
            . "lbl.value=(it[{$jsLabelKey}]!==undefined&&it[{$jsLabelKey}]!==null)?it[{$jsLabelKey}]:'';"
            . "box.innerHTML='';"
            . "});"
            . "box.appendChild(d);"
            . "});"
            . "});"
            . "},300);"
            . "});"
            . "})();</script>";

        return '<div class="aurea-search" style="position:relative">' . $hidden . $visible . $box . '</div>' . $js;
    }
}
