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
        'SalesAnalytics' => ['onShow'],
        'AccessManagement' => ['onShow', 'onUsers', 'onUserForm', 'onGroups', 'onGroupForm', 'onPermissions', 'onPermissionForm', 'onSaveUser', 'onDeleteUser', 'onSaveGroup', 'onDeleteGroup'],
        'CustomerOpportunityKanban' => ['onShow', 'onMoveCard'],
        'CustomerList' => ['onShow', 'onReload', 'onDelete', 'onExportCSV'],
        'CustomerForm' => ['onShow', 'onSave', 'onEdit', 'onClear', 'onNewActivity', 'onEditActivity', 'onNewOpportunity', 'onEditOpportunity', 'onNewDocument', 'onEditDocument'],
        'CustomerActivityList' => ['onShow', 'onReload', 'onDelete', 'onExportCSV'],
        'CustomerActivityForm' => ['onShow', 'onSave', 'onEdit', 'onClear'],
        'CustomerOpportunityList' => ['onShow', 'onReload', 'onDelete', 'onExportCSV'],
        'CustomerOpportunityForm' => ['onShow', 'onSave', 'onEdit', 'onClear'],
        'CustomerDocumentList' => ['onShow', 'onReload', 'onDelete', 'onExportCSV'],
        'CustomerDocumentForm' => ['onShow', 'onSave', 'onEdit', 'onClear'],
    ],
    'logging' => [
        'enabled' => true,
        'file' => __DIR__ . '/../var/log/aurea.log',
        'max_bytes' => 1048576,
        'keep' => 5,
    ],
];
