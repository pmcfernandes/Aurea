<?php
declare(strict_types=1);

namespace Aurea\Container;

use Aurea\Core\TComponent;


/**
 * Tabela HTML (ver também TTableRow e TTableCell).
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TTable extends TComponent
{
    /**
     * Cria uma tabela Bootstrap.
     */
    public function __construct()
    {
        parent::__construct('table');
        $this->addClass('table');
    }

    /**
     * Adiciona uma linha à tabela.
     *
     * @return TTableRow Linha criada
     */
    public function addRow(): TTableRow
    {
        $row = new TTableRow;
        $this->add($row);
        return $row;
    }
}
