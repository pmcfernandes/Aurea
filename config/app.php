<?php
declare(strict_types=1);

/** Ligação principal MariaDB. As credenciais são lidas do ambiente. */
return [
    'host' => getenv('DB_HOST') ?: '127.0.0.1',
    'port' => getenv('DB_PORT') ?: '3306',
    'name' => getenv('DB_DATABASE') ?: 'docman',
    'user' => getenv('DB_USERNAME') ?: 'root',
    'pass' => getenv('DB_PASSWORD') ?: '',
    'type' => 'mysql',
    'char' => 'utf8mb4',
    'prep' => '1',
];
