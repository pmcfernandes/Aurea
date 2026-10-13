<?php
declare(strict_types=1);

namespace Aurea\Container;

use Aurea\Core\TComponent;


/**
 * Célula de tabela HTML (td).
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TTableCell extends TComponent
{
    /**
     * Cria uma célula de tabela (td).
     */
    public function __construct()
    {
        parent::__construct('td');
    }
}
