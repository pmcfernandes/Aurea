<?php
declare(strict_types=1);

/** @var string $kanban */
?>
<section class="opportunity-kanban" aria-labelledby="opportunity-kanban-title">
    <header class="opportunity-kanban-heading">
        <div>
            <p class="opportunity-kanban-kicker">PIPELINE COMERCIAL</p>
            <h1 id="opportunity-kanban-title">Oportunidades</h1>
            <p>Acompanhe as oportunidades ao longo do processo comercial.</p>
        </div>
        <a class="btn btn-outline-secondary" href="index.php?class=CustomerOpportunityList&amp;method=onShow">
            <i class="fa-solid fa-list" aria-hidden="true"></i> Ver lista
        </a>
    </header>
    <?= $kanban ?>
</section>
