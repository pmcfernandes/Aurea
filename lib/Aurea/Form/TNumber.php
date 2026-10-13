<?php
declare(strict_types=1);

namespace Aurea\Form;

use Aurea\Core\TComponent;
use Aurea\Validator\TValidatorInterface;


/**
 * Campo numérico com casas decimais.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TNumber extends TField
{
    /**
     * Cria o campo numérico.
     *
     * @param string $name Nome do campo
     */
    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->setProperty('type', 'number');
    }

    /**
     * Define a quantidade de casas decimais.
     *
     * @param int $d Quantidade de casas decimais
     * @return static
     */
    public function setDecimals(int $d): static
    {
        return $this->setProperty('step', $d > 0 ? '0.' . str_repeat('0', $d - 1) . '1' : '1');
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
}
