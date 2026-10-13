<?php
declare(strict_types=1);

namespace Aurea\Form;

use Aurea\Core\TComponent;
use Aurea\Validator\TValidatorInterface;


/**
 * Campo de hora.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TTime extends TField
{
    /**
     * Cria o campo de hora.
     *
     * @param string $name Nome do campo
     */
    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->setProperty('type', 'time');
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
