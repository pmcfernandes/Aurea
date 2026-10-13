<?php
declare(strict_types=1);

namespace Aurea\Form;

/**
 * TMultiEntry — campo de etiquetas (tags) com chips removíveis.
 *
 *   $tags = new TMultiEntry('tags');
 *   $tags->setValue(['php', 'aurea']);
 *   $tags->setPlaceholder('Escreva e prima Enter');
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TMultiEntry extends TField
{
    protected static int $counter = 0;
    protected string $boxId;
    /** @var string[] */
    protected array $tags = [];
    protected string $placeholder = '';

    /**
     * Cria o campo de etiquetas com o nome informado.
     *
     * @param string $name Nome do campo
     */
    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->tag = 'div';
        self::$counter++;
        $this->boxId = 'multientry_' . $name . '_' . self::$counter;
    }

    /**
     * Define as etiquetas iniciais do campo.
     *
     * @param array $values Lista de etiquetas
     * @return static
     */
    public function setValue(mixed $value): static
    {
        $this->tags = array_values(array_map('strval', (array) $value));
        $this->value = $value;
        return $this;
    }

    /**
     * Define o texto de ajuda do campo.
     *
     * @param string $text Texto a exibir como ajuda
     * @return static
     */
    public function setPlaceholder(string $text): static
    {
        $this->placeholder = $text;
        return parent::setPlaceholder($text);
    }

    /**
     * Obtém o HTML do campo com os chips e o script de edição.
     *
     * @return string HTML do campo
     */
    public function getContents(): string
    {
        $name = $this->getName();
        $chips = '';
        foreach ($this->tags as $tag) {
            $safe = htmlspecialchars($tag, ENT_QUOTES);
            $chips .= "<span class='me-chip'>{$safe}<button type='button' data-chip-remove='1'>×</button>"
                   . "<input type='hidden' name='{$name}[]' value='{$safe}'></span>";
        }
        $attrs = $this->renderAttributes();
        $ph = htmlspecialchars($this->placeholder, ENT_QUOTES);
        $out = "<div{$attrs}><div id='{$this->boxId}'>{$chips}"
             . "<input type='text' id='{$this->boxId}_input' placeholder='{$ph}' autocomplete='off'></div></div>";
        $out .= "<script>(function(){var box=document.getElementById('{$this->boxId}');"
             . "var input=document.getElementById('{$this->boxId}_input');"
             . "function addChip(text){text=text.trim().replace(/,+$/,'').trim();if(!text){return;}"
             . "var chip=document.createElement('span');chip.className='me-chip';"
             . "chip.textContent=text;"
             . "var btn=document.createElement('button');btn.type='button';btn.textContent='×';"
             . "btn.onclick=function(){chip.remove();};"
             . "var hidden=document.createElement('input');hidden.type='hidden';"
             . "hidden.name='{$name}[]';hidden.value=text;"
             . "chip.appendChild(btn);chip.appendChild(hidden);"
             . "box.insertBefore(chip,input);}"
             . "box.addEventListener('click',function(e){"
             . "var b=e.target.closest('[data-chip-remove]');"
             . "if(b){b.closest('.me-chip').remove();}});"
             . "input.addEventListener('keydown',function(e){"
             . "if(e.key==='Enter'||e.key===','){e.preventDefault();addChip(input.value);input.value='';}});"
             . "})();</script>";
        return $out;
    }
}
