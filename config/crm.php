<?php
declare(strict_types=1);

/** Ligação MariaDB do CRM. Pode ser configurada separadamente via CRM_DB_*. */
return [
    'host' => getenv('CRM_DB_HOST') ?: (getenv('DB_HOST') ?: '127.0.0.1'),
    'port' => getenv('CRM_DB_PORT') ?: (getenv('DB_PORT') ?: '3306'),
    'name' => getenv('CRM_DB_DATABASE') ?: (getenv('DB_DATABASE') ?: 'docman'),
    'user' => getenv('CRM_DB_USERNAME') ?: (getenv('DB_USERNAME') ?: 'root'),
    'pass' => getenv('CRM_DB_PASSWORD') ?: (getenv('DB_PASSWORD') ?: ''),
    'type' => 'mysql',
    'char' => 'utf8mb4',
    'prep' => '1',
];
