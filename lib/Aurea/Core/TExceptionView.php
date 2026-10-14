<?php
declare(strict_types=1);

namespace Aurea\Core;

/** Renderer seguro para exceções HTML, com trace somente em modo debug. */
final class TExceptionView
{
    public static function render(\Throwable $error, bool $debug = false): string
    {
        $message = htmlspecialchars($error->getMessage(), ENT_QUOTES, 'UTF-8');
        $detail = $debug ? '<pre class="mt-3 small text-muted">' . htmlspecialchars($error->getTraceAsString(), ENT_QUOTES, 'UTF-8') . '</pre>' : '';
        return "<div class='alert alert-danger' role='alert'><strong>Erro:</strong> {$message}</div>{$detail}";
    }
}
