<?php
declare(strict_types=1);

namespace Aurea\Container;

use Aurea\Core\TComponent;


/**
 * THBox — alinha componentes horizontalmente.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class THBox extends TComponent
{
    /**
     * Cria um contentor horizontal alinhado ao centro.
     */
    public function __construct()
    {
        parent::__construct('div');
        $this->addClass('d-flex flex-row gap-2 align-items-center');
    }
}
