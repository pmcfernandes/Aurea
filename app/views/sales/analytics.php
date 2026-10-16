<?php
declare(strict_types=1);

/** @var array{openPipeline:float,wonRevenue:float,wonCount:int,closedCount:int,averageTicket:float,conversionRate:float} $metrics */
/** @var string $monthlyChart */
/** @var string $pipelineChart */
?>
<section class="sales-analytics" aria-labelledby="sales-analytics-title">
    <header class="sales-analytics-heading">
        <div>
            <p class="sales-analytics-kicker">DESEMPENHO COMERCIAL</p>
            <h1 id="sales-analytics-title">Vendas e indicadores</h1>
            <p>Analise o estado atual do pipeline e a evolução das oportunidades.</p>
        </div>
        <a class="btn btn-primary" href="index.php?class=CustomerOpportunityKanban&amp;method=onShow">
            <i class="fa-solid fa-columns" aria-hidden="true"></i> Ver pipeline
        </a>
    </header>

    <div class="sales-metrics">
        <article class="sales-metric">
            <span class="sales-metric-icon is-blue"><i class="fa-solid fa-chart-line" aria-hidden="true"></i></span>
            <div><span>Pipeline aberto</span><strong><?= number_format($metrics['openPipeline'], 2, ',', '.') ?> €</strong><small>Oportunidades em curso</small></div>
        </article>
        <article class="sales-metric">
            <span class="sales-metric-icon is-green"><i class="fa-solid fa-trophy" aria-hidden="true"></i></span>
            <div><span>Vendas ganhas</span><strong><?= number_format($metrics['wonRevenue'], 2, ',', '.') ?> €</strong><small><?= $metrics['wonCount'] ?> oportunidades ganhas</small></div>
        </article>
        <article class="sales-metric">
            <span class="sales-metric-icon is-yellow"><i class="fa-solid fa-percent" aria-hidden="true"></i></span>
            <div><span>Taxa de conversão</span><strong><?= number_format($metrics['conversionRate'], 1, ',', '.') ?>%</strong><small>Sobre oportunidades fechadas</small></div>
        </article>
        <article class="sales-metric">
            <span class="sales-metric-icon is-purple"><i class="fa-solid fa-receipt" aria-hidden="true"></i></span>
            <div><span>Ticket médio</span><strong><?= number_format($metrics['averageTicket'], 2, ',', '.') ?> €</strong><small>Vendas ganhas</small></div>
        </article>
    </div>

    <div class="sales-charts">
        <article class="sales-chart-card">
            <?= $monthlyChart ?>
        </article>
        <article class="sales-chart-card">
            <?= $pipelineChart ?>
        </article>
    </div>
</section>
