<?php
declare(strict_types=1);

namespace Aurea\Form;

use Aurea\Core\TComponent;
use Aurea\Validator\TValidatorInterface;


/**
 * Grupo de caixas de verificação.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TCheckGroup extends TField
{
    protected array $items = [];
    protected array $checked = [];

    /**
     * Cria o grupo de caixas de seleção.
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
     * Define um valor para o campo.
     *
     * @param mixed $value Valor a atribuir ao campo
     * @return static
     */
    public function setValue(mixed $value): static
    {
        $this->checked = (array) $value;
        $this->value = $value;
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
            $chk = in_array((string) $k, array_map('strval', $this->checked), true) ? ' checked' : '';
            $out .= "<label class='form-check-label'><input type='checkbox' class='form-check-input' name='{$this->name}[]' value='"
                  . htmlspecialchars((string) $k, ENT_QUOTES) . "'{$chk}> " . htmlspecialchars((string) $v) . "</label>";
        }
        return $out . '</div>';
    }
}
