<?php
declare(strict_types=1);

/**
 * Front Controller MVC (exemplo com URLs amigáveis).
 * Em Apache com .htaccess, /home/show/Ana chega aqui sem ?url=.
 * No servidor embutido: mvc.php?url=home/show/Ana
 */
require dirname(__DIR__) . '/bootstrap.php';

use Aurea\Mvc\TRouter;

$router = new TRouter('App\\Controllers\\');
$router->add('', ['controller' => 'Home', 'action' => 'index']);
$router->add('{controller}/{action}');
$router->add('{controller}/{action}/{name}');
$router->add('{controller}/{id:\d+}/{action}');

try {
    $router->dispatchFromRequest();
} catch (\Throwable $e) {
    http_response_code($e->getCode() === 404 ? 404 : 500);
    echo '<h1>' . ($e->getCode() === 404 ? 'Página não encontrada' : 'Erro') . '</h1>'
       . '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
}
