<?php
declare(strict_types=1);

namespace Aurea\Form;

use Aurea\Core\TComponent;
use Aurea\Validator\TValidatorInterface;


/**
 * Rótulo de campo de formulário.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TLabel extends TComponent
{
    /**
     * Cria o rótulo com o texto informado.
     *
     * @param string $text Texto do rótulo
     */
    public function __construct(string $text)
    {
        parent::__construct('label');
        $this->addClass('form-label fw-semibold');
        $this->add($text);
    }
}
