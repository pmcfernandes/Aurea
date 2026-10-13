<?php
declare(strict_types=1);

namespace Aurea\Form;

use Aurea\Core\TComponent;
use Aurea\Validator\TValidatorInterface;


/**
 * Campo de texto de linha única.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TEntry extends TField
{
    /**
     * Cria o campo de texto.
     *
     * @param string $name Nome do campo
     */
    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->setProperty('type', 'text');
    }

    /**
     * Obtém o HTML do campo.
     *
     * @return string HTML do campo
     */
    public function getContents(): string
    {
        $this->sync();
        return parent::getContents();
    }

    /**
     * Define a máscara de digitação do campo.
     *
     * @param string $mask Máscara a aplicar
     * @return static
     */
    public function setMask(string $mask): static
    {
        return $this->setProperty('data-mask', $mask);
    }

    /**
     * Define o tamanho máximo do campo.
     *
     * @param int $len Tamanho máximo permitido
     * @return static
     */
    public function setMaxLength(int $len): static
    {
        return $this->setProperty('maxlength', (string) $len);
    }
}
