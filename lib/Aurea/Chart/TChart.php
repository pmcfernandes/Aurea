<?php
declare(strict_types=1);

namespace Aurea\Chart;

use Aurea\Core\TComponent;

/**
 * TChart — gráficos via Chart.js.
 *
 *   $chart = new TChart('bar', 'Vendas por mês');
 *   $chart->setLabels(['Jan', 'Fev', 'Mar']);
 *   $chart->addDataset('2026', [10, 20, 15]);
 *   $chart->show();
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TChart extends TComponent
{
    protected static int $counter = 0;
    protected static bool $libIncluded = false;
    protected string $chartId;
    /** @var array<int,array{label:string,data:array,color:string}> */
    protected array $datasets = [];
    protected array $labels = [];

    protected static array $palette = [
        '#0d6efd', '#198754', '#ffc107', '#dc3545', '#6f42c1', '#fd7e14', '#20c997', '#6c757d',
    ];

    /**
     * Cria um gráfico Chart.js com tipo e título.
     *
     * @param string $type Tipo (bar, line, pie, doughnut)
     * @param string $title Título do gráfico
     */
    public function __construct(protected string $type = 'bar', protected string $title = '')
    {
        parent::__construct('div');
        self::$counter++;
        $this->chartId = 'chart_' . self::$counter;
        if (!in_array($type, ['bar', 'line', 'pie', 'doughnut'], true)) {
            throw new \InvalidArgumentException("Tipo de gráfico inválido: {$type}");
        }
    }

    /** @param string[] $labels */

    public function setLabels(array $labels): static
    {
        $this->labels = array_values($labels);
        return $this;
    }

    /**
     * Adiciona uma série de dados ao gráfico.
     *
     * @param string $label Rótulo da série
     * @param array $data Valores da série
     * @param string|null $color Cor da série ou null para paleta
     * @return static
     */
    public function addDataset(string $label, array $data, ?string $color = null): static
    {
        $this->datasets[] = [
            'label' => $label,
            'data' => array_values($data),
            'color' => $color ?? self::$palette[count($this->datasets) % count(self::$palette)],
        ];
        return $this;
    }

    /**
     * Define a altura do gráfico em pixels.
     *
     * @param int $px Altura em pixels
     * @return static
     */
    public function setHeight(int $px): static
    {
        return $this->setStyle("height:{$px}px");
    }

    /**
     * Renderiza o HTML e o script do gráfico.
     *
     * @return string HTML do gráfico
     */
    public function getContents(): string
    {
        $lib = '';
        if (!self::$libIncluded) {
            $lib = '<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>';
            self::$libIncluded = true;
        }
        $datasets = [];
        foreach ($this->datasets as $ds) {
            $datasets[] = [
                'label' => $ds['label'],
                'data' => $ds['data'],
                'backgroundColor' => $this->type === 'line' ? $ds['color'] . '33' : $ds['color'],
                'borderColor' => $ds['color'],
                'borderWidth' => 2,
                'fill' => $this->type === 'line',
            ];
        }
        $config = json_encode([
            'type' => $this->type,
            'data' => ['labels' => $this->labels, 'datasets' => $datasets],
            'options' => [
                'responsive' => true,
                'maintainAspectRatio' => false,
                'plugins' => ['title' => ['display' => $this->title !== '', 'text' => $this->title]],
            ],
        ]);
        $attrs = $this->renderAttributes();
        return "{$lib}<div{$attrs}><canvas id='{$this->chartId}'></canvas></div>"
             . "<script>new Chart(document.getElementById('{$this->chartId}'), {$config});</script>";
    }
}
