<?php
declare(strict_types=1);

namespace Aurea\Http;

/**
 * TCookie — acesso a cookies com opções.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TCookie
{
    /**
     * Grava um cookie.
     *
     * @param string $name Nome
     * @param string $value Valor
     * @param array $options expire (timestamp), path, domain, secure, httponly, samesite
     * @return bool Resultado de setcookie()
     */
    public static function set(string $name, string $value, array $options = []): bool
    {
        $params = array_merge([
            'expires' => time() + 365 * 86400,
            'path' => '/',
            'domain' => '',
            'secure' => false,
            'httponly' => true,
            'samesite' => 'Lax',
        ], $options);
        return setcookie($name, $value, $params);
    }

    /**
     * Lê um cookie.
     *
     * @param string $name Nome
     * @param string|null $default Valor se ausente
     * @return string|null Valor ou omissão
     */
    public static function get(string $name, ?string $default = null): ?string
    {
        $value = $_COOKIE[$name] ?? null;
        return is_string($value) ? $value : $default;
    }

    /**
     * Indica se o cookie existe.
     *
     * @param string $name Nome
     * @return bool Verdadeiro se definido
     */
    public static function has(string $name): bool
    {
        return isset($_COOKIE[$name]);
    }

    /**
     * Apaga um cookie (expira no passado).
     *
     * @param string $name Nome
     * @param string $path Caminho
     * @param string $domain Domínio
     * @return bool Resultado de setcookie()
     */
    public static function delete(string $name, string $path = '/', string $domain = ''): bool
    {
        unset($_COOKIE[$name]);
        return setcookie($name, '', [
            'expires' => time() - 3600,
            'path' => $path,
            'domain' => $domain,
        ]);
    }
}
