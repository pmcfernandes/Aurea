<?php
declare(strict_types=1);

namespace Aurea\Auth;

/**
 * THash — criação e verificação de hashes de palavra-passe.
 *
 *   $hash = THash::make('segredo');
 *   THash::verify('segredo', $hash);
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class THash
{
    /**
     * Cria um hash BCRYPT da palavra-passe.
     *
     * @param string $plain Palavra-passe em claro
     * @return string THash gerado
     */
    public static function make(string $plain): string
    {
        return password_hash($plain, PASSWORD_BCRYPT);
    }

    /**
     * Verifica se a palavra-passe corresponde ao hash.
     *
     * @param string $plain Palavra-passe em claro
     * @param string $hash THash armazenado
     * @return bool True se corresponde
     */
    public static function verify(string $plain, string $hash): bool
    {
        return password_verify($plain, $hash);
    }

    /**
     * Indica se o hash precisa de ser recalculado.
     *
     * @param string $hash THash armazenado
     * @return bool True se precisa de novo hash
     */
    public static function needsRehash(string $hash): bool
    {
        return password_needs_rehash($hash, PASSWORD_BCRYPT);
    }

    /**
     * Obtém informação sobre o hash.
     *
     * @param string $hash THash armazenado
     * @return array Informação do algoritmo e opções
     */
    public static function info(string $hash): array
    {
        return password_get_info($hash);
    }
}
