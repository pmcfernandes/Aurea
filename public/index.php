<?php
declare(strict_types=1);

/**
 * Front Controller — ponto único de entrada da aplicação.
 * Roteia ?class=ClienteList&method=onReload para App\Control\ClienteList::onReload($param).
 */
require dirname(__DIR__) . '/bootstrap.php';

use App\Lib\CrmShell;
use App\Lib\AccessControl;
use Aurea\Auth\TAuth;
use Aurea\Auth\TPermission;
use Aurea\Core\TApplication;
use Aurea\Core\TAction;
use Aurea\Core\TAssets;
use Aurea\Core\TScript;
use Aurea\Core\TSession;
use Aurea\Core\TConfig;
use Aurea\Database\TTransaction;
use Aurea\Log\TLog;
use Aurea\Log\TLogContext;
use Aurea\Log\TRotatingTxtLogger;

TConfig::loadEnv(APP_ROOT . '/.env');
$config = TConfig::loadWithEnv(APP_ROOT . '/config/application.php');
$config->validate([
    'app.environment' => 'string',
    'app.debug' => 'bool',
    'app.csrf_protection' => 'bool',
    'app.permission_guard' => 'bool',
    'permissions' => 'array',
    'logging' => 'array',
]);

TApplication::setTitle('Aurea CRM Demo');
TAssets::setVersion('1.4.0');
TApplication::setControlNamespace('App\\Control\\');
TApplication::setDefault('CrmLogin', 'onShow');
TApplication::setEnv($config->get('app.environment'), $config->get('app.debug'));
TApplication::setCsrfProtection($config->get('app.csrf_protection'));
TApplication::usePermissionGuard($config->get('app.permission_guard'));
TPermission::setActionChecker(static function (string $controller, string $action, ?string $userId): bool {
    if ($controller === 'CrmLogin') {
        return true;
    }

    return AccessControl::allows($userId, $controller, $action);
});
TApplication::setLayoutRenderer(static fn(string $content, string $active): string => CrmShell::render(
    $content,
    $active,
    $config->get('permissions')
));

if ($config->get('logging.enabled', false)) {
    $logger = new TRotatingTxtLogger(
        (string) $config->get('logging.file'),
        (int) $config->get('logging.max_bytes', 1048576),
        (int) $config->get('logging.keep', 5)
    );
    TLog::setLogger($logger);
    TTransaction::setLogger(TLog::writer());
    TLogContext::beginRequest();
    TLogContext::set('environment', TApplication::getEnv());
    TLogContext::set('http_method', (string) ($_SERVER['REQUEST_METHOD'] ?? 'CLI'));
    TLog::info('Pedido iniciado', ['path' => (string) ($_SERVER['REQUEST_URI'] ?? '')]);
}

foreach ($config->get('permissions') as $controller => $actions) {
    foreach ($actions as $action) {
        TPermission::grantAction((string) $controller, (string) $action);
    }
}

TApplication::setAuthCallback(static function (string $class, string $method): bool|string {
    if ($class === 'CrmLogin' || TAuth::check()) {
        return true;
    }
    TSession::setValue('crm_after_login', ['class' => $class, 'method' => $method]);
    TScript::redirect(new TAction(['CrmLogin', 'onShow']));
    return 'Autenticação necessária.';
});
TApplication::run();
