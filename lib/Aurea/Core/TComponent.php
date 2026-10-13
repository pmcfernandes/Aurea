<?php
declare(strict_types=1);

namespace Aurea\Core;

/**
 * TComponent — classe base de todos os componentes visuais.
 *
 * Todo componente é um elemento HTML com tag, atributos e filhos.
 * Renderiza via getContents() / show().
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TComponent
{
    protected string $tag;
    protected array $attributes = [];
    protected array $children = [];
    protected static array $voidTags = [
        'input', 'img', 'br', 'hr', 'meta', 'link', 'source', 'wbr'
    ];

    /**
     * Cria o componente com a tag informada.
     *
     * @param string $tag Tag HTML do elemento
     */
    public function __construct(string $tag = 'div')
    {
        $this->tag = strtolower($tag);
    }

    /**
     * Define um atributo do elemento.
     *
     * @param string $name Nome do atributo
     * @param mixed $value Valor do atributo
     */
    public function __set(string $name, $value): void
    {
        $this->setProperty($name, (string) $value);
    }

    /**
     * Obtém um atributo do elemento.
     *
     * @param string $name Nome do atributo
     * @return mixed Valor do atributo ou null se inexistente
     */
    public function __get(string $name)
    {
        return $this->attributes[$name] ?? null;
    }

    /**
     * Define uma propriedade (atributo) do elemento.
     *
     * @param string $name Nome da propriedade
     * @param string|null $value Valor da propriedade (null remove)
     * @return static
     */
    public function setProperty(string $name, ?string $value): static
    {
        if ($value === null) {
            unset($this->attributes[$name]);
        } else {
            $this->attributes[$name] = $value;
        }
        return $this;
    }

    /**
     * Obtém uma propriedade (atributo) do elemento.
     *
     * @param string $name Nome da propriedade
     * @return string|null Valor da propriedade ou null se inexistente
     */
    public function getProperty(string $name): ?string
    {
        return $this->attributes[$name] ?? null;
    }

    /**
     * Adiciona um filho ao elemento.
     *
     * @param mixed $content Componente ou HTML do filho
     * @return static
     */
    public function add($content): static
    {
        $this->children[] = $content;
        return $this;
    }

    /**
     * Substitui todos os filhos do elemento.
     *
     * @param array $children Novos filhos
     * @return static
     */
    public function setChildren(array $children): static
    {
        $this->children = $children;
        return $this;
    }

    /**
     * Adiciona uma classe CSS ao elemento.
     *
     * @param string $class Classe CSS
     * @return static
     */
    public function addClass(string $class): static
    {
        $current = trim(($this->attributes['class'] ?? '') . ' ' . $class);
        $this->attributes['class'] = $current;
        return $this;
    }

    /**
     * Define o id do elemento.
     *
     * @param string $id Identificador do elemento
     * @return static
     */
    public function setId(string $id): static
    {
        return $this->setProperty('id', $id);
    }

    /**
     * Define o estilo inline do elemento.
     *
     * @param string $style Regras CSS inline
     * @return static
     */
    public function setStyle(string $style): static
    {
        return $this->setProperty('style', $style);
    }

    /**
     * Renderiza os atributos do elemento.
     *
     * @return string Atributos em HTML
     */
    protected function renderAttributes(): string
    {
        $out = '';
        foreach ($this->attributes as $name => $value) {
            $out .= ' ' . htmlspecialchars($name, ENT_QUOTES)
                  . '="' . htmlspecialchars((string) $value, ENT_QUOTES) . '"';
        }
        return $out;
    }

    /**
     * Renderiza os filhos do elemento.
     *
     * @return string HTML dos filhos
     */
    protected function renderChildren(): string
    {
        $out = '';
        foreach ($this->children as $child) {
            if ($child instanceof self) {
                $out .= $child->getContents();
            } elseif (is_object($child) && method_exists($child, 'getContents')) {
                $out .= (string) $child->getContents();
            } else {
                $out .= (string) $child;
            }
        }
        return $out;
    }

    /** @return string HTML do componente */

    public function getContents(): string
    {
        $attrs = $this->renderAttributes();
        if (in_array($this->tag, self::$voidTags, true)) {
            return "<{$this->tag}{$attrs}>";
        }
        return "<{$this->tag}{$attrs}>" . $this->renderChildren() . "</{$this->tag}>";
    }

    /**
     * Imprime o HTML do componente.
     */
    public function show(): void
    {
        echo $this->getContents();
    }

    /**
     * Converte o componente em HTML.
     *
     * @return string HTML do componente
     */
    public function __toString(): string
    {
        return $this->getContents();
    }

    /**
     * Clona o componente e seus filhos.
     */
    public function __clone()
    {
        $children = [];
        foreach ($this->children as $child) {
            $children[] = is_object($child) ? clone $child : $child;
        }
        $this->children = $children;
    }
}
