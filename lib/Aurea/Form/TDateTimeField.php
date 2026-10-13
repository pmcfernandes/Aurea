<?php
declare(strict_types=1);

namespace Aurea\Form;

use Aurea\Core\TComponent;
use Aurea\Validator\TValidatorInterface;


/**
 * Campo de data e hora (datetime-local).
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TDateTimeField extends TField
{
    protected string $dbMask = 'yyyy-mm-dd hh:ii:ss';

    /**
     * Cria o campo de data e hora.
     *
     * @param string $name Nome do campo
     */
    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->setProperty('type', 'datetime-local');
    }

    /**
     * Define a máscara usada no banco de dados.
     *
     * @param string $mask Máscara do banco de dados
     * @return static
     */
    public function setDatabaseMask(string $mask): static
    {
        $this->dbMask = $mask;
        return $this;
    }

    /**
     * Obtém a máscara usada no banco de dados.
     *
     * @return string Máscara do banco de dados
     */
    public function getDatabaseMask(): string
    {
        return $this->dbMask;
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
