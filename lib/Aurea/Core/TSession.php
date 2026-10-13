<?php
declare(strict_types=1);

namespace Aurea\Core;

/**
 * TSession — acesso à sessão com prefixo da aplicação.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TSession
{
    protected static string $prefix = 'aurea_';

    /**
     * Define o prefixo das chaves da sessão.
     *
     * @param string $prefix Prefixo das chaves
     */
    public static function setPrefix(string $prefix): void
    {
        self::$prefix = $prefix;
    }

    /**
     * Inicia a sessão se ainda não estiver ativa.
     */
    protected static function start(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE && !headers_sent()) {
            session_start();
        }
        if (!isset($_SESSION)) {
            $_SESSION = [];
        }
    }

    /**
     * Guarda um valor na sessão.
     *
     * @param string $key Chave do valor
     * @param mixed $value Valor a guardar
     */
    public static function setValue(string $key, $value): void
    {
        self::start();
        $_SESSION[self::$prefix . $key] = $value;
    }

    /**
     * Obtém um valor da sessão.
     *
     * @param string $key Chave do valor
     * @param mixed $default Valor padrão se inexistente
     * @return mixed Valor guardado ou padrão
     */
    public static function getValue(string $key, $default = null)
    {
        self::start();
        return $_SESSION[self::$prefix . $key] ?? $default;
    }

    /**
     * Indica se uma chave existe na sessão.
     *
     * @param string $key Chave a verificar
     * @return bool True se a chave existe
     */
    public static function has(string $key): bool
    {
        self::start();
        return array_key_exists(self::$prefix . $key, $_SESSION);
    }

    /**
     * Remove uma chave da sessão.
     *
     * @param string $key Chave a remover
     */
    public static function clear(string $key): void
    {
        self::start();
        unset($_SESSION[self::$prefix . $key]);
    }

    /** Limpa todos os valores da aplicação */

    public static function free(): void
    {
        self::start();
        foreach (array_keys($_SESSION) as $key) {
            if (str_starts_with((string) $key, self::$prefix)) {
                unset($_SESSION[$key]);
            }
        }
    }

    /**
     * Regenera o id da sessão.
     */
    public static function regenerate(): void
    {
        self::start();
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }
}
