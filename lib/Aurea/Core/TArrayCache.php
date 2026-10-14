<?php
declare(strict_types=1);

namespace Aurea\Core;

/**
 * Cache em memória por processo, útil para testes e pedidos curtos.
 * Os dados não sobrevivem ao fim do processo PHP.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
final class TArrayCache implements TCacheInterface
{
    /** @var array<string,array{value:mixed,expires:int}> */
    private array $items = [];

    /** {@inheritdoc} */
    public function get(string $key, mixed $default = null): mixed
    {
        if (!$this->has($key)) {
            return $default;
        }
        return $this->items[$key]['value'];
    }

    /** {@inheritdoc} */
    public function set(string $key, mixed $value, int $ttl = 0): void
    {
        $this->items[$key] = ['value' => $value, 'expires' => $ttl > 0 ? time() + $ttl : 0];
    }

    /** {@inheritdoc} */
    public function delete(string $key): void { unset($this->items[$key]); }
    /** {@inheritdoc} */
    public function clear(): void { $this->items = []; }

    /** {@inheritdoc} */
    public function has(string $key): bool
    {
        if (!isset($this->items[$key])) {
            return false;
        }
        if ($this->items[$key]['expires'] > 0 && $this->items[$key]['expires'] <= time()) {
            unset($this->items[$key]);
            return false;
        }
        return true;
    }
}
