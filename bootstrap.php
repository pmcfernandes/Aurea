<?php
declare(strict_types=1);

/**
 * Bootstrap da aplicação.
 *
 * Define APP_ROOT, fixa o diretório de trabalho na raiz do projeto
 * (para os caminhos relativos funcionarem com qualquer docroot)
 * e carrega o autoloader do Composer.
 */
define('APP_ROOT', __DIR__);

chdir(APP_ROOT);

require APP_ROOT . '/vendor/autoload.php';
