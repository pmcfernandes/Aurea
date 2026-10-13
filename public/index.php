<?php
declare(strict_types=1);

/**
 * Front Controller — ponto único de entrada da aplicação.
 * Roteia ?class=ClienteList&method=onReload para App\Control\ClienteList::onReload($param).
 */
require dirname(__DIR__) . '/bootstrap.php';

use Aurea\Core\TApplication;

TApplication::setTitle('Aurea Framework');
TApplication::setControlNamespace('App\\Control\\');
TApplication::setDefault('HomeView', 'onShow');
if (file_exists(APP_ROOT . '/app/menu.xml')) {
    // $allowed = fn(string $programa): bool => true; // ligue às suas permissões
    TApplication::setMenuXml(APP_ROOT . '/app/menu.xml');
}
TApplication::run();
