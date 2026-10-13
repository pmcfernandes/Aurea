<?php
declare(strict_types=1);

namespace Aurea\Form;

use Aurea\Core\TComponent;
use Aurea\Validator\TValidatorInterface;


/**
 * Área de texto multilinha.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TTextArea extends TField
{
    /**
     * Cria a área de texto.
     *
     * @param string $name Nome do campo
     */
    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->tag = 'textarea';
        $this->setProperty('rows', '4');
    }

    /**
     * Define a largura e a altura da área de texto.
     *
     * @param string $size Largura da área de texto
     * @param string|null $height Altura da área de texto
     * @return static
     */
    public function setSize(string $size, ?string $height = null): static
    {
        $this->setStyle("width:{$size}" . ($height ? ";height:{$height}" : ''));
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
        $val = htmlspecialchars((string) ($this->value ?? ''), ENT_QUOTES);
        return "<textarea{$attrs}>{$val}</textarea>";
    }
}
