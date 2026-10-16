<?php
declare(strict_types=1);

/** @var array{customers:int,activeCustomers:int,pipeline:float,plannedActivities:int,validDocuments:int,recentCustomers:array<int,array<string,mixed>>,recentActivities:array<int,array<string,mixed>>} $dashboard */
?>
<section class="dashboard" aria-labelledby="dashboard-title">
    <header class="dashboard-heading">
        <div>
            <p class="dashboard-kicker">VISÃO GERAL</p>
            <h1 id="dashboard-title">Dashboard</h1>
            <p>Acompanhe a atividade comercial e o estado da sua carteira de clientes.</p>
        </div>
        <a class="btn btn-primary dashboard-create" href="index.php?class=CustomerForm&amp;method=onShow">
            <i class="fa-solid fa-user-plus" aria-hidden="true"></i> Novo cliente
        </a>
    </header>

    <div class="dashboard-metrics">
        <article class="dashboard-metric">
            <span class="dashboard-metric-icon is-indigo"><i class="fa-solid fa-users" aria-hidden="true"></i></span>
            <div>
                <span>Clientes</span>
                <strong><?= number_format($dashboard['customers'], 0, ',', '.') ?></strong>
                <small><?= number_format($dashboard['activeCustomers'], 0, ',', '.') ?> ativos</small>
            </div>
        </article>
        <article class="dashboard-metric">
            <span class="dashboard-metric-icon is-sky"><i class="fa-solid fa-chart-line" aria-hidden="true"></i></span>
            <div>
                <span>Pipeline aberto</span>
                <strong><?= number_format($dashboard['pipeline'], 2, ',', '.') ?> €</strong>
                <small>Oportunidades em curso</small>
            </div>
        </article>
        <article class="dashboard-metric">
            <span class="dashboard-metric-icon is-amber"><i class="fa-solid fa-list-check" aria-hidden="true"></i></span>
            <div>
                <span>Actividades</span>
                <strong><?= number_format($dashboard['plannedActivities'], 0, ',', '.') ?></strong>
                <small>Planeadas</small>
            </div>
        </article>
        <article class="dashboard-metric">
            <span class="dashboard-metric-icon is-emerald"><i class="fa-solid fa-folder-open" aria-hidden="true"></i></span>
            <div>
                <span>Documentos</span>
                <strong><?= number_format($dashboard['validDocuments'], 0, ',', '.') ?></strong>
                <small>Válidos</small>
            </div>
        </article>
    </div>

    <div class="dashboard-grid">
        <section class="dashboard-panel">
            <header>
                <div>
                    <h2>Clientes recentes</h2>
                    <p>Últimos clientes adicionados à carteira.</p>
                </div>
                <a href="index.php?class=CustomerList&amp;method=onShow">Ver todos</a>
            </header>
            <?php if ($dashboard['recentCustomers']): ?>
                <div class="table-responsive">
                    <table class="table dashboard-table mb-0">
                        <thead>
                            <tr><th>Cliente</th><th>Empresa</th><th>Estado</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($dashboard['recentCustomers'] as $customer): ?>
                                <tr>
                                    <td>
                                        <a href="index.php?class=CustomerForm&amp;method=onEdit&amp;key=<?= (int) $customer['id'] ?>">
                                            <?= htmlspecialchars((string) $customer['name'], ENT_QUOTES, 'UTF-8') ?>
                                        </a>
                                    </td>
                                    <td><?= htmlspecialchars((string) ($customer['company'] ?: '—'), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><span class="dashboard-status"><?= htmlspecialchars((string) $customer['status'], ENT_QUOTES, 'UTF-8') ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="dashboard-empty">
                    <i class="fa-solid fa-users" aria-hidden="true"></i> Ainda não existem clientes registados.
                </p>
            <?php endif; ?>
        </section>

        <section class="dashboard-panel">
            <header>
                <div>
                    <h2>Actividade recente</h2>
                    <p>Últimas interações registadas.</p>
                </div>
            </header>
            <?php if ($dashboard['recentActivities']): ?>
                <ul class="dashboard-activity-list">
                    <?php foreach ($dashboard['recentActivities'] as $activity): ?>
                        <li>
                            <span class="dashboard-activity-icon"><i class="fa-solid fa-check" aria-hidden="true"></i></span>
                            <div>
                                <strong><?= htmlspecialchars((string) $activity['subject'], ENT_QUOTES, 'UTF-8') ?></strong>
                                <span>
                                    <?= htmlspecialchars((string) $activity['customer_name'], ENT_QUOTES, 'UTF-8') ?> ·
                                    <?= htmlspecialchars((string) $activity['type'], ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="dashboard-empty">
                    <i class="fa-solid fa-list-check" aria-hidden="true"></i> Ainda não existem actividades registadas.
                </p>
            <?php endif; ?>
        </section>
    </div>
</section>
