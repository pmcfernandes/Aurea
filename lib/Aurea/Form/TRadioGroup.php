<?php
declare(strict_types=1);

namespace Aurea\Form;

use Aurea\Core\TComponent;
use Aurea\Validator\TValidatorInterface;


/**
 * Grupo de botões de rádio.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TRadioGroup extends TField
{
    protected array $items = [];

    /**
     * Cria o grupo de botões de opção.
     *
     * @param string $name Nome do campo
     */
    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->tag = 'div';
    }

    /**
     * Define os itens do grupo.
     *
     * @param array $items Itens no formato chave => rótulo
     * @return static
     */
    public function addItems(array $items): static
    {
        $this->items = $items;
        return $this;
    }

    /**
     * Obtém o HTML do campo.
     *
     * @return string HTML do campo
     */
    public function getContents(): string
    {
        $out = "<div class='d-flex gap-3 flex-wrap'>";
        foreach ($this->items as $k => $v) {
            $chk = ((string) $k === (string) $this->value) ? ' checked' : '';
            $out .= "<label class='form-check-label'><input type='radio' class='form-check-input' name='{$this->name}' value='"
                  . htmlspecialchars((string) $k, ENT_QUOTES) . "'{$chk}> " . htmlspecialchars((string) $v) . "</label>";
        }
        return $out . '</div>';
    }
}
