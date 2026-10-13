<?php
declare(strict_types=1);

use Aurea\Core\TTranslator;

if (!function_exists('_t')) {
    /**
     * Atalho de tradução.
     */
    function _t(string $message, mixed ...$args): string
    {
        return TTranslator::translate($message, ...$args);
    }
}
