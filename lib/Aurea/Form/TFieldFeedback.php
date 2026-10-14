<?php
declare(strict_types=1);

namespace Aurea\Form;

use Aurea\Core\TComponent;

/**
 * Renderiza uma mensagem de validação associada a um TField.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
final class TFieldFeedback extends TComponent
{
    /** @param TField $field Campo cuja validação será apresentada */
    public function __construct(private readonly TField $field)
    {
        parent::__construct('div');
        $this->addClass('invalid-feedback');
        $this->setProperty('id', (string) $field->getProperty('id') . '_error');
        $this->setProperty('role', 'alert');
    }

    /** @return string HTML da mensagem, ou vazio enquanto o campo for válido */
    public function getContents(): string
    {
        $message = $this->field->getValidationError();
        if ($message === null) {
            return '';
        }
        return '<div' . $this->renderAttributes() . '>'
            . htmlspecialchars($message, ENT_QUOTES, 'UTF-8')
            . '</div>';
    }
}
