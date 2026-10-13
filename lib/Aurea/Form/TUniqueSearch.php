<?php
declare(strict_types=1);

namespace Aurea\Form;

use Aurea\Core\TAction;
use Aurea\Database\TCriteria;
use Aurea\Database\TRepository;
use Aurea\Database\TTransaction;


/**
 * TUniqueSearch — busca com autocomplete via <datalist>.
 * Versão simplificada do TUniqueSearch: exibe "id - descrição",
 * grava o valor digitado e dispara um evento onChange opcional.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TUniqueSearch extends TField
{
    /** @var array<string,string> chave => rótulo */
    protected array $items = [];
    protected ?TAction $changeAction = null;
    protected string $changeParam = 'key';
    protected int $minLength = 0;

    /**
     * Cria o campo de busca com autocomplete.
     *
     * @param string $name Nome do campo
     */
    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->setProperty('list', 'list_' . $name);
        $this->setProperty('autocomplete', 'off');
    }

    /** @param array<string,string> $items */

    public function addItems(array $items): static
    {
        $this->items = $items;
        return $this;
    }

    /**
     * Define o tamanho mínimo para a busca.
     *
     * @param int $len Tamanho mínimo permitido
     * @return static
     */
    public function setMinLength(int $len): static
    {
        $this->minLength = $len;
        return $this->setProperty('minlength', (string) $len);
    }

    /**
     * Define a ação executada ao alterar o valor.
     *
     * @param TAction $action Ação a executar
     * @param string $param Nome do parâmetro enviado
     * @return static
     */
    public function setChangeAction(TAction $action, string $param = 'key'): static
    {
        $this->changeAction = $action;
        $this->changeParam = $param;
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
        $options = '';
        foreach ($this->items as $k => $label) {
            $options .= '<option value="' . htmlspecialchars((string) $k, ENT_QUOTES) . '">'
                      . htmlspecialchars((string) $label) . '</option>';
        }
        $list = "<datalist id='list_{$this->getName()}'>{$options}</datalist>";
        if ($this->changeAction) {
            $base = $this->changeAction->serialize();
            $sep = str_contains($base, '?') ? '&' : '?';
            $js = "window.location='{$base}{$sep}{$this->changeParam}='+encodeURIComponent(this.value)";
            $input = str_replace('<input ', "<input onchange=\"" . htmlspecialchars($js, ENT_QUOTES) . '" ', $input);
        }
        return $input . $list;
    }
}
