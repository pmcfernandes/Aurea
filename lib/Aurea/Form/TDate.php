<?php
declare(strict_types=1);

namespace Aurea\Form;

use Aurea\Core\TComponent;
use Aurea\Validator\TValidatorInterface;


/**
 * Campo de data (input date).
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TDate extends TField
{
    protected string $mask = 'dd/mm/yyyy';
    protected string $dbMask = 'yyyy-mm-dd';

    /**
     * Cria o campo de data.
     *
     * @param string $name Nome do campo
     */
    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->setProperty('type', 'date');
    }

    /**
     * Define a máscara de exibição da data.
     *
     * @param string $mask Máscara de exibição
     * @return static
     */
    public function setMask(string $mask): static
    {
        $this->mask = $mask;
        return $this;
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
