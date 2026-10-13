<?php
declare(strict_types=1);

namespace Aurea\Core;

/**
 * TStyle — construtor fluente de CSS inline.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TStyle
{
    /**
     * Cria o estilo com as propriedades informadas.
     *
     * @param array $props Pares propriedade/valor CSS
     */
    public function __construct(protected array $props = []) {}

    /**
     * Define uma propriedade CSS.
     *
     * @param string $prop Nome da propriedade CSS
     * @param string $val Valor da propriedade CSS
     * @return static
     */
    public function set(string $prop, string $val): static
    {
        $this->props[$prop] = $val;
        return $this;
    }

    /**
     * Renderiza o estilo como regras CSS inline.
     *
     * @return string Regras no formato "prop: val; ..."
     */
    public function render(): string
    {
        $parts = [];
        foreach ($this->props as $prop => $val) {
            $parts[] = "{$prop}: {$val};";
        }
        return implode(' ', $parts);
    }

    /**
     * Aplica o estilo a um componente.
     *
     * @param TComponent $c Componente alvo
     * @return TComponent O próprio componente
     */
    public function applyTo(TComponent $c): TComponent
    {
        $c->setStyle($this->render());
        return $c;
    }

    /**
     * Cria um estilo a partir das propriedades informadas.
     *
     * @param array $props Pares propriedade/valor CSS
     * @return self
     */
    public static function create(array $props): self
    {
        return new self($props);
    }
}
