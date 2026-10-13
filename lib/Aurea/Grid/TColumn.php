<?php
declare(strict_types=1);

namespace Aurea\Grid;

use Aurea\Core\TAction;
use Aurea\Core\TComponent;
use Aurea\Database\TRecord;


/**
 * TColumn — coluna do TDataGrid.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TColumn
{
    protected ?TAction $action = null;
    protected string $align = 'left';
    protected ?string $transformer = null;
    protected bool $orderable = false;
    protected string $total = '';
    protected $totalFunction = null;

    /**
     * Cria uma coluna do DataGrid.
     *
     * @param string $name Nome do campo nos dados
     * @param string $label Rótulo do cabeçalho
     * @param string $width Largura da coluna
     */
    public function __construct(
        protected string $name,
        protected string $label,
        protected string $width = ''
    ) {
    }

    /**
     * Obtém o nome do campo da coluna.
     *
     * @return string Nome do campo
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Obtém o rótulo do cabeçalho da coluna.
     *
     * @return string Rótulo da coluna
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * Define a ação do link da célula.
     *
     * @param TAction $action Ação do link
     * @return static
     */
    public function setAction(TAction $action): static
    {
        $this->action = $action;
        return $this;
    }

    /**
     * Obtém a ação do link da célula.
     *
     * @return ?TAction Ação configurada ou null
     */
    public function getAction(): ?TAction
    {
        return $this->action;
    }

    /**
     * Define o alinhamento do texto da célula.
     *
     * @param string $align Alinhamento (left, center, right)
     * @return static
     */
    public function setAlign(string $align): static
    {
        $this->align = $align;
        return $this;
    }

    /**
     * Obtém o alinhamento do texto da célula.
     *
     * @return string Alinhamento atual
     */
    public function getAlign(): string
    {
        return $this->align;
    }

    /** Permite ordenar clicando no cabeçalho. */

    public function setOrderable(bool $orderable = true): static
    {
        $this->orderable = $orderable;
        return $this;
    }

    /**
     * Indica se a coluna permite ordenação por clique.
     *
     * @return bool Verdadeiro se ordenável
     */
    public function isOrderable(): bool
    {
        return $this->orderable;
    }

    /**
     * Define a função de transformação do valor da célula.
     *
     * @param callable $fn Função fn($valor, $item): string
     * @return static
     */
    public function setTransformer(callable $fn): static
    {
        $this->transformer = null;
        // guarda closure via propriedade dinâmica simples
        $this->transformerClosure = $fn;
        return $this;
    }

    public mixed $transformerClosure = null;

    /**
     * Define o totalizador do rodapé: 'sum', 'count' ou Closure fn($valores, $itens).
     *
     * @param string|\Closure $total Tipo de total ou função
     * @return static
     */
    public function setTotal(string|\Closure $total): static
    {
        if ($total instanceof \Closure) {
            $this->totalFunction = $total;
            $this->total = 'custom';
        } else {
            if (!in_array($total, ['sum', 'count'], true)) {
                throw new \InvalidArgumentException("Totalizador inválido: {$total}");
            }
            $this->total = $total;
        }
        return $this;
    }

    /**
     * Indica se a coluna tem totalizador.
     *
     * @return bool Verdadeiro se há total configurado
     */
    public function hasTotal(): bool
    {
        return $this->total !== '';
    }

    /**
     * Calcula o total da coluna sobre os itens do grid.
     *
     * @param array $items Itens do grid (TRecord[] ou arrays)
     * @return string Total formatado (vazio se sem total)
     */
    public function getTotalValue(array $items): string
    {
        if (!$this->hasTotal()) {
            return '';
        }
        $values = [];
        foreach ($items as $item) {
            $arr = $item instanceof TRecord ? $item->toArray() : (array) $item;
            $values[] = $arr[$this->name] ?? null;
        }
        if ($this->totalFunction) {
            return (string) ($this->totalFunction)($values, $items);
        }
        if ($this->total === 'count') {
            return (string) count($items);
        }
        $sum = 0.0;
        foreach ($values as $v) {
            $sum += (float) str_replace(',', '.', (string) $v);
        }
        return number_format($sum, 2, ',', '.');
    }

    /**
     * Renderiza a célula HTML para um item da grade.
     *
     * @param object|array $item Registro ou linha de dados
     * @return string HTML da célula td
     */
    public function renderCell(object|array $item): string
    {
        $arr = $item instanceof TRecord ? $item->toArray() : (array) $item;
        $value = $arr[$this->name] ?? '';
        if ($this->transformerClosure) {
            $value = ($this->transformerClosure)($value, $item);
        }
        $value = htmlspecialchars((string) $value);
        if ($this->action) {
            $a = clone $this->action;
            // passa só a chave do registro (evita URLs gigantes); params extras vão no TAction
            if (isset($arr['id'])) {
                $a->setParameter('key', $arr['id']);
            }
            $value = "<a href='" . $a->toHref() . "'>{$value}</a>";
        }
        return "<td class='text-{$this->align}'>{$value}</td>";
    }
}
