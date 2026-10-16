<?php
declare(strict_types=1);

/** @var string $form */
/** @var string|null $error */
?>
<main class="login-shell">
    <section class="login-showcase" aria-label="Aurea CRM">
        <a class="login-brand" href="index.php" aria-label="Aurea CRM, página inicial">
            <span class="login-brand-mark"><i class="fa-solid fa-layer-group"></i></span>
            <span>Aurea</span>
        </a>
    <div class="login-showcase-copy">
            <span class="login-eyebrow">GESTÃO SIMPLIFICADA</span>
            <h1>Relações que fazem o negócio avançar.</h1>
            <p>Centralize os seus clientes, oportunidades e tarefas num só lugar.</p>
    </div>
        <div class="login-orb login-orb-one"></div>
        <div class="login-orb login-orb-two"></div>
        <p class="login-copyright">© 2026 Aurea. Todos os direitos reservados.</p>
    </section>
    <section class="login-panel" aria-labelledby="login-title">
        <div class="login-card">
            <div class="login-mobile-brand">
                <span class="login-brand-mark"><i class="fa-solid fa-layer-group"></i></span>
                <span>Aurea</span>
            </div>
            <header>
                <h2 id="login-title">Bem-vindo de volta</h2>
                <p>Entre com os seus dados para continuar.</p>
            </header>
            <?php if ($error !== null): ?>
                <div class="login-alert" role="alert">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></span>
                </div>
            <?php endif; ?>
            <?= $form ?>
            <p class="login-help">Precisa de ajuda? <a href="mailto:suporte@aurea.test">Fale com o suporte</a></p>
        </div>
    </section>
</main>
