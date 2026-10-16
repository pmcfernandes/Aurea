<?php
declare(strict_types=1);

namespace App\Control;

use App\Model\CustomerOpportunity;
use Aurea\Chart\TChart;
use Aurea\Core\TAssets;
use Aurea\Core\TPage;
use Aurea\Database\TTransaction;
use Aurea\Mvc\TView;
use DateInterval;
use DateTimeImmutable;

/** Indicadores e gráficos comerciais baseados nas oportunidades registadas. */
class SalesAnalytics extends TPage
{
    private const STAGES = [
        'qualification' => 'Qualificação',
        'proposal' => 'Proposta',
        'negotiation' => 'Negociação',
        'won' => 'Ganha',
        'lost' => 'Perdida',
    ];

    public function __construct()
    {
        parent::__construct();
        TAssets::addCss('/assets/sales-analytics.css');
    }

    public function onShow(array $param = []): void
    {
        $months = $this->recentMonths();
        $monthlyAmounts = array_fill_keys(array_keys($months), 0.0);
        $stageAmounts = array_fill_keys(array_keys(self::STAGES), 0.0);
        $metrics = [
            'openPipeline' => 0.0,
            'wonRevenue' => 0.0,
            'wonCount' => 0,
            'closedCount' => 0,
            'averageTicket' => 0.0,
        ];

        try {
            TTransaction::open('app');
            $opportunities = CustomerOpportunity::where('id', '>', 0)->get();

            foreach ($opportunities as $opportunity) {
                $amount = (float) $opportunity->amount;
                $stage = (string) $opportunity->stage;
                $status = (string) $opportunity->status;
                $month = substr((string) $opportunity->created_at, 0, 7);

                if (isset($monthlyAmounts[$month])) {
                    $monthlyAmounts[$month] += $amount;
                }
                if ($status === 'open' && isset($stageAmounts[$stage])) {
                    $stageAmounts[$stage] += $amount;
                    $metrics['openPipeline'] += $amount;
                }
                if (in_array($status, ['won', 'lost'], true)) {
                    $metrics['closedCount']++;
                }
                if ($status === 'won') {
                    $metrics['wonCount']++;
                    $metrics['wonRevenue'] += $amount;
                }
            }
            TTransaction::close();
        } catch (\Throwable) {
            TTransaction::rollback();
        }

        $metrics['averageTicket'] = $metrics['wonCount'] > 0
            ? $metrics['wonRevenue'] / $metrics['wonCount']
            : 0.0;
        $metrics['conversionRate'] = $metrics['closedCount'] > 0
            ? ($metrics['wonCount'] / $metrics['closedCount']) * 100
            : 0.0;

        $monthlyChart = (new TChart('line', 'Volume criado por mês'))
            ->setLabels(array_values($months))
            ->addDataset('Valor das oportunidades (€)', array_values($monthlyAmounts), '#0064e0')
            ->setHeight(300);
        $pipelineChart = (new TChart('doughnut', 'Pipeline aberto por fase'))
            ->setLabels(array_values(self::STAGES))
            ->addDataset('Pipeline aberto (€)', array_values($stageAmounts))
            ->setHeight(300);

        $this->addHtml((new TView('sales/analytics'))
            ->set('metrics', $metrics)
            ->set('monthlyChart', $monthlyChart->getContents())
            ->set('pipelineChart', $pipelineChart->getContents())
            ->fetch());
    }

    /** @return array<string,string> Meses recentes no formato YYYY-MM => rótulo. */
    private function recentMonths(): array
    {
        $month = (new DateTimeImmutable('first day of this month'))->sub(new DateInterval('P5M'));
        $months = [];

        for ($index = 0; $index < 6; $index++) {
            $months[$month->format('Y-m')] = $month->format('m/Y');
            $month = $month->add(new DateInterval('P1M'));
        }

        return $months;
    }
}
