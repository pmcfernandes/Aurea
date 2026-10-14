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
    /** Custo BCrypt padrão para novas palavras-passe. */
    public const DEFAULT_OPTIONS = ['cost' => 12];

    /**
     * Cria um hash BCRYPT da palavra-passe.
     *
     * @param string $plain Palavra-passe em claro
     * @param array{cost?:int} $options Opções BCrypt; por omissão usa custo 12
     * @return string Hash gerado
     */
    public static function make(string $plain, array $options = self::DEFAULT_OPTIONS): string
    {
        $hash = password_hash($plain, PASSWORD_BCRYPT, $options);
        if ($hash === false) {
            throw new \RuntimeException('Não foi possível criar o hash da palavra-passe.');
        }
        return $hash;
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
     * @param array{cost?:int} $options Política BCrypt pretendida
     * @return bool True se precisa de novo hash
     */
    public static function needsRehash(string $hash, array $options = self::DEFAULT_OPTIONS): bool
    {
        return password_needs_rehash($hash, PASSWORD_BCRYPT, $options);
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
