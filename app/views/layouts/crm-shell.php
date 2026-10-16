<?php
declare(strict_types=1);

/** @var string $title */
/** @var string $version */
/** @var string $content */
/** @var \Aurea\Container\TMenu|null $menu */
/** @var string $userName */
/** @var string $userInitial */
/** @var string $logoutUrl */
/** @var string $assetsCss */
/** @var string $assetsJs */
/** @var string $scripts */
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <?= $assetsCss ?>
</head>
<body class="crm-shell-body">
<div class="crm-shell">
    <aside class="crm-sidebar" id="crm-sidebar" aria-label="Navegação principal">
        <div class="crm-project-title">
            <span class="crm-project-icon"><i class="fa-solid fa-layer-group" aria-hidden="true"></i></span>
            <span>
                <strong><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></strong>
                <small><?= htmlspecialchars($version, ENT_QUOTES, 'UTF-8') ?></small>
            </span>
        </div>
        <nav class="crm-sidebar-nav"><?= $menu?->getContents() ?? '' ?></nav>
        <div class="crm-sidebar-footer">
            <div class="crm-sidebar-user">
                <span class="crm-user-avatar" aria-hidden="true"><?= htmlspecialchars($userInitial, ENT_QUOTES, 'UTF-8') ?></span>
                <span><?= htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
            <a class="crm-logout" href="<?= htmlspecialchars($logoutUrl, ENT_QUOTES, 'UTF-8') ?>">
                <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i>
                <span>Sair</span>
            </a>
        </div>
    </aside>
    <div class="crm-workspace">
        <header class="crm-topbar">
            <button class="crm-menu-toggle" type="button" aria-controls="crm-sidebar" aria-expanded="false" aria-label="Abrir menu">
                <i class="fa-solid fa-bars" aria-hidden="true"></i>
            </button>
            <span class="crm-topbar-title"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></span>
        </header>
        <main id="aurea-content" class="crm-page-content"><?= $content ?></main>
    </div>
</div>
<div class="crm-sidebar-backdrop" hidden></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?= $assetsJs ?>
<?= $scripts ?>
</body>
</html>
