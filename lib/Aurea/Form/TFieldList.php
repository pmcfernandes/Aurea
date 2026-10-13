<?php
declare(strict_types=1);

namespace Aurea\Form;

/**
 * TFieldList — grelha de linhas editáveis mestre-detalhe.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TFieldList extends \Aurea\Core\TComponent
{
    protected static int $counter = 0;
    protected string $baseName;
    protected string $tableId;
    /** @var TField[] */
    protected array $prototypes = [];
    /** @var array<int, array<string, mixed>> */
    protected array $rows = [];

    /**
     * Cria a lista de campos com o nome base informado.
     *
     * @param string $name Nome base (inputs saem como "nome[campo][]")
     */
    public function __construct(string $name)
    {
        parent::__construct('div');
        $this->baseName = $name;
        self::$counter++;
        $this->tableId = 'fieldlist-' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $name) . '-' . self::$counter;
        $this->addClass('aurea-fieldlist');
    }

    /**
     * Adiciona um protótipo de coluna à lista.
     *
     * @param TField $field Protótipo da coluna (usa-se getName() para clonar por linha)
     * @return static
     */
    public function addField(TField $field): static
    {
        $this->prototypes[$field->getName()] = $field;
        return $this;
    }

    /**
     * Adiciona uma linha com os valores informados.
     *
     * @param array<string, mixed> $data Valores da linha por nome de campo
     * @return static
     */
    public function addRow(array $data = []): static
    {
        $this->rows[] = $data;
        return $this;
    }

    /**
     * Substitui todas as linhas pelos valores informados.
     *
     * @param array<int, array<string, mixed>> $rows Lista de linhas (assoc por linha)
     * @return static
     */
    public function setRows(array $rows): static
    {
        $this->rows = array_values($rows);
        return $this;
    }

    /**
     * Lê as linhas submetidas a partir de $_POST.
     *
     * @return array<int, array<string, mixed>> Lista de assoc por linha
     */
    public function getRows(): array
    {
        $posted = $_POST[$this->baseName] ?? null;
        if (!is_array($posted)) {
            return [];
        }
        $count = 0;
        foreach ($posted as $values) {
            if (is_array($values)) {
                $count = max($count, count($values));
            }
        }
        $rows = [];
        for ($i = 0; $i < $count; $i++) {
            $row = [];
            foreach ($posted as $field => $values) {
                if (is_array($values)) {
                    $row[(string) $field] = $values[$i] ?? null;
                }
            }
            $rows[] = $row;
        }
        return $rows;
    }

    /**
     * Clona um protótipo para uma linha com nome e valor próprios.
     *
     * @param TField $proto Protótipo da coluna
     * @param mixed $value Valor a atribuir ao clone
     * @param int $index Índice da linha (para id único)
     * @return TField Clone pronto a renderizar
     */
    protected function cloneForRow(TField $proto, mixed $value, int $index): TField
    {
        $fname = $proto->getName();
        $clone = clone $proto;
        $clone->setValue($value);
        $clone->setProperty('name', "{$this->baseName}[{$fname}][]");
        $clone->setProperty('id', 'field_' . $fname . '_' . $index);
        return $clone;
    }

    /**
     * Renderiza a tabela com as linhas e os botões de gestão.
     *
     * @return string HTML da grelha
     */
    public function getContents(): string
    {
        $bodyId = $this->tableId . '-body';
        $rows = $this->rows;
        if ($rows === []) {
            $rows = [[]];
        }

        $out = '<table class="table aurea-fieldlist-table" id="' . htmlspecialchars($this->tableId, ENT_QUOTES) . '">';
        $out .= '<thead><tr>';
        foreach ($this->prototypes as $fname => $proto) {
            $out .= '<th>' . htmlspecialchars($proto->getLabel(), ENT_QUOTES) . '</th>';
        }
        $out .= '<th></th></tr></thead>';
        $out .= '<tbody id="' . htmlspecialchars($bodyId, ENT_QUOTES) . '">';
        foreach ($rows as $i => $row) {
            $out .= '<tr>';
            foreach ($this->prototypes as $fname => $proto) {
                $val = $row[$fname] ?? null;
                $clone = $this->cloneForRow($proto, $val, (int) $i);
                $out .= '<td>' . $clone->getContents() . '</td>';
            }
            $out .= '<td><button type="button" class="btn btn-sm btn-danger" '
                . 'onclick="this.closest(\'tr\').remove();return false;">&times;</button></td>';
            $out .= '</tr>';
        }
        $out .= '</tbody></table>';
        $out .= '<button type="button" class="btn btn-sm btn-secondary" onclick="'
            . 'var tb=document.getElementById(\'' . $bodyId . '\');'
            . 'var last=tb.rows[tb.rows.length-1];if(!last)return false;'
            . 'var r=last.cloneNode(true);'
            . 'r.querySelectorAll(\'input,select,textarea\').forEach(function(el){el.value=\'\';});'
            . 'tb.appendChild(r);return false;">+ Adicionar</button>';
        return $out;
    }
}
