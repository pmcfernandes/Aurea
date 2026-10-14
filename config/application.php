<?php
declare(strict_types=1);

/**
 * Política de execução da aplicação.
 *
 * APP_ENV aceita dev, test ou prod. APP_DEBUG pode forçar o modo debug.
 */
$environment = (string) (getenv('APP_ENV') ?: 'dev');
$debugSetting = getenv('APP_DEBUG');
$debug = $debugSetting === false
    ? null
    : filter_var($debugSetting, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);

return [
    'app' => [
        'environment' => $environment,
        'debug' => $debug ?? $environment !== 'prod',
        'csrf_protection' => true,
        'permission_guard' => true,
    ],
    // Allow-list explícita dos eventos expostos pela demo.
    'permissions' => [
        'CrmLogin' => ['onShow', 'onLogin', 'onLogout'],
        'CrmDashboard' => ['onShow'],
        'CrmClienteList' => ['onShow', 'onReload', 'onDelete', 'onExportCSV'],
        'CrmClienteForm' => ['onShow', 'onSave', 'onEdit', 'onClear'],
    ],
    'logging' => [
        'enabled' => true,
        'file' => __DIR__ . '/../var/log/aurea.log',
        'max_bytes' => 1048576,
        'keep' => 5,
    ],
];
