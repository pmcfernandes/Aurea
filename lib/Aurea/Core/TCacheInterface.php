<?php
declare(strict_types=1);

namespace Aurea\Core;

/**
 * Contrato de cache com TTL em segundos; TTL zero significa sem expiração.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
interface TCacheInterface
{
    /** @return mixed Valor armazenado ou $default quando inexistente/expirado. */
    public function get(string $key, mixed $default = null): mixed;
    /** @param int $ttl Segundos até expirar; zero mantém o valor. */
    public function set(string $key, mixed $value, int $ttl = 0): void;
    /** Remove uma chave, sem erro se ela não existir. */
    public function delete(string $key): void;
    /** Remove todos os valores do cache atual. */
    public function clear(): void;
    /** Indica se a chave existe e ainda não expirou. */
    public function has(string $key): bool;
}
