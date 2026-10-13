<?php
declare(strict_types=1);

namespace Aurea\Dialog;

use Aurea\Core\TAction;
use Aurea\Core\TComponent;


/**
 * TToast — notificação flutuante.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TToast extends TComponent
{
    /**
     * Cria uma notificação flutuante (toast).
     *
     * @param string $type Tipo (info, success, warning, error)
     * @param string $text Texto da notificação
     */
    public function __construct(string $type, string $text)
    {
        parent::__construct('div');
        $map = ['info' => 'primary', 'success' => 'success', 'warning' => 'warning', 'error' => 'danger'];
        $bs = $map[$type] ?? 'primary';
        $this->addClass('toast show align-items-center text-bg-' . $bs);
        $this->add("<div class='d-flex'><div class='toast-body'>" . htmlspecialchars($text)
            . "</div><button type='button' class='btn-close btn-close-white me-2 m-auto' data-bs-dismiss='toast'></button></div>");
    }
}
