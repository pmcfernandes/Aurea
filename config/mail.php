<?php
declare(strict_types=1);

/** Configuração de envio SMTP. Valores sensíveis devem estar no ficheiro .env. */
return [
    'smtp' => [
        'host' => (string) (getenv('MAIL_HOST') ?: ''),
        'port' => (int) (getenv('MAIL_PORT') ?: 587),
        'username' => (string) (getenv('MAIL_USERNAME') ?: ''),
        'password' => (string) (getenv('MAIL_PASSWORD') ?: ''),
        'encryption' => (string) (getenv('MAIL_ENCRYPTION') ?: 'tls'),
        'auth' => filter_var(getenv('MAIL_AUTH') ?: 'true', FILTER_VALIDATE_BOOL),
        'auto_tls' => filter_var(getenv('MAIL_AUTO_TLS') ?: 'true', FILTER_VALIDATE_BOOL),
        'timeout' => (int) (getenv('MAIL_TIMEOUT') ?: 15),
    ],
    'from' => [
        'address' => (string) (getenv('MAIL_FROM_ADDRESS') ?: ''),
        'name' => (string) (getenv('MAIL_FROM_NAME') ?: ''),
    ],
];
