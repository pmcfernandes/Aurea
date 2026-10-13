<?php
declare(strict_types=1);

namespace Aurea\Form;

use Aurea\Core\TComponent;
use Aurea\Validator\TValidatorInterface;


/**
 * Editor HTML simples com barra de ferramentas.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class THtmlEditor extends TField
{
    /**
     * Cria o editor HTML simples.
     *
     * @param string $name Nome do campo
     */
    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->tag = 'textarea';
    }

    /**
     * Monta o JavaScript que envolve a seleção com as marcas informadas.
     *
     * @param string $before Marca de abertura
     * @param string $after Marca de fecho
     * @return string Código JavaScript
     */
    protected function wrapJs(string $before, string $after): string
    {
        $id = $this->getProperty('id') ?? 'field_' . $this->getName();
        return "var t=document.getElementById('{$id}');var s=t.selectionStart,e=t.selectionEnd,v=t.value;"
             . "t.value=v.substring(0,s)+'{$before}'+v.substring(s,e)+'{$after}'+v.substring(e);t.focus();";
    }

    /**
     * Monta o JavaScript do botão de ligação.
     *
     * @return string Código JavaScript
     */
    protected function linkJs(): string
    {
        $id = $this->getProperty('id') ?? 'field_' . $this->getName();
        return "var t=document.getElementById('{$id}');var u=prompt('URL do link:','https://');"
             . "if(u){var s=t.selectionStart,e=t.selectionEnd,v=t.value;"
             . "t.value=v.substring(0,s)+'<a href=\"'+u+'\">'+v.substring(s,e)+'</a>'+v.substring(e);t.focus();}";
    }

    /**
     * Monta o JavaScript do botão de lista.
     *
     * @return string Código JavaScript
     */
    protected function listJs(): string
    {
        $id = $this->getProperty('id') ?? 'field_' . $this->getName();
        return "var t=document.getElementById('{$id}');var s=t.selectionStart,e=t.selectionEnd,v=t.value;"
             . "var lis=v.substring(s,e).split('\n').map(function(l){return '<li>'+l+'</li>'}).join('');"
             . "t.value=v.substring(0,s)+'<ul>'+lis+'</ul>'+v.substring(e);t.focus();";
    }

    /**
     * Monta um botão da barra de ferramentas.
     *
     * @param string $title Título do botão
     * @param string $js Código JavaScript do clique
     * @return string HTML do botão
     */
    protected function toolButton(string $title, string $js): string
    {
        return '<button type="button" onclick="' . htmlspecialchars($js, ENT_QUOTES) . '">'
             . htmlspecialchars($title) . '</button>';
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
        $val = htmlspecialchars((string) ($this->value ?? ''), ENT_QUOTES);
        $toolbar = "<div class='htmleditor-toolbar'>"
                 . $this->toolButton('B', $this->wrapJs('<b>', '</b>'))
                 . $this->toolButton('I', $this->wrapJs('<i>', '</i>'))
                 . $this->toolButton('Link', $this->linkJs())
                 . $this->toolButton('Lista', $this->listJs())
                 . '</div>';
        return $toolbar . "<textarea{$attrs}>{$val}</textarea>";
    }
}
