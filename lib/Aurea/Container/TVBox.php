<?php
declare(strict_types=1);

namespace Aurea\Container;

use Aurea\Core\TComponent;


/**
 * TVBox — empilha componentes verticalmente.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TVBox extends TComponent
{
    /**
     * Cria um contentor vertical de largura total.
     */
    public function __construct()
    {
        parent::__construct('div');
        $this->addClass('d-flex flex-column gap-2');
        $this->setStyle('width:100%');
    }
}
