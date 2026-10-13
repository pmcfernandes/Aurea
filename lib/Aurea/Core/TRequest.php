<?php
declare(strict_types=1);

namespace Aurea\Core;

/**
 * TRequest — acesso uniforme a GET+POST+FILES.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TRequest
{
    /**
     * Obtém todos os parâmetros da requisição (GET+POST).
     *
     * @return array Parâmetros da requisição
     */
    public static function all(): array
    {
        return array_merge($_GET, $_POST);
    }

    /**
     * Obtém um parâmetro da requisição.
     *
     * @param string $key Nome do parâmetro
     * @param mixed $default Valor padrão se inexistente
     * @return mixed Valor do parâmetro ou padrão
     */
    public static function get(string $key, $default = null)
    {
        $all = self::all();
        return $all[$key] ?? $default;
    }

    /**
     * Indica se a requisição é POST.
     *
     * @return bool True se o método é POST
     */
    public static function isPost(): bool
    {
        return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
    }

    /**
     * Indica se a requisição é AJAX.
     *
     * @return bool True se for requisição AJAX
     */
    public static function isAjax(): bool
    {
        return strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest'
            || isset($_GET['ajax']);
    }

    /** O cliente espera JSON (Accept ou ?format=json) */

    public static function wantsJson(): bool
    {
        if (($_GET['format'] ?? '') === 'json') {
            return true;
        }
        return str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
    }
}
