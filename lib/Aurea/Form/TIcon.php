<?php
declare(strict_types=1);

namespace Aurea\Form;

use Aurea\Core\TComponent;
use Aurea\Validator\TValidatorInterface;


/**
 * Campo de texto com pré-visualização de ícone.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TIcon extends TField
{
    /** @var string[] classes de ícone sugeridas */
    protected array $items = [];

    /**
     * Cria o campo de ícone com antevisão.
     *
     * @param string $name Nome do campo
     */
    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->setProperty('type', 'text');
        $this->setProperty('list', 'list_' . $name);
        $this->setProperty('oninput', "document.getElementById('iconprev_{$name}').className=this.value");
    }

    /**
     * Define as classes de ícone sugeridas.
     *
     * @param array $iconClasses Classes de ícone para a lista de sugestões
     * @return static
     */
    public function setItems(array $iconClasses): static
    {
        $this->items = array_values($iconClasses);
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
        $input = parent::getContents();
        $prev = "<i id='iconprev_{$this->getName()}' class='"
              . htmlspecialchars((string) ($this->value ?? ''), ENT_QUOTES) . "'></i>";
        $options = '';
        foreach ($this->items as $icon) {
            $options .= '<option value="' . htmlspecialchars((string) $icon, ENT_QUOTES) . '"></option>';
        }
        return $input . $prev . "<datalist id='list_{$this->getName()}'>{$options}</datalist>";
    }
}
