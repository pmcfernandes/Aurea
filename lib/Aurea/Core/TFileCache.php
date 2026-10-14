<?php
declare(strict_types=1);

namespace Aurea\Core;

/**
 * Cache serializado em ficheiros, com escrita atómica e TTL.
 * Não armazene objetos: a desserialização bloqueia classes por segurança.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
final class TFileCache implements TCacheInterface
{
    /** @param string $directory Diretório privado e gravável pelo processo PHP. */
    public function __construct(private string $directory)
    {
        if (!is_dir($directory) && !mkdir($directory, 0750, true) && !is_dir($directory)) {
            throw new \RuntimeException("Não foi possível criar cache em {$directory}");
        }
    }

    /** {@inheritdoc} */
    public function get(string $key, mixed $default = null): mixed
    {
        if (!$this->has($key)) {
            return $default;
        }
        $data = @unserialize((string) file_get_contents($this->path($key)), ['allowed_classes' => false]);
        return is_array($data) ? ($data['value'] ?? $default) : $default;
    }

    /** {@inheritdoc} */
    public function set(string $key, mixed $value, int $ttl = 0): void
    {
        $payload = serialize(['value' => $value, 'expires' => $ttl > 0 ? time() + $ttl : 0]);
        $tmp = $this->path($key) . '.' . bin2hex(random_bytes(4)) . '.tmp';
        file_put_contents($tmp, $payload, LOCK_EX);
        rename($tmp, $this->path($key));
        @chmod($this->path($key), 0640);
    }

    /** {@inheritdoc} */
    public function delete(string $key): void { @unlink($this->path($key)); }
    /** {@inheritdoc} */
    public function clear(): void { foreach ((array) glob($this->directory . DIRECTORY_SEPARATOR . '*.cache') as $file) { @unlink($file); } }

    /** {@inheritdoc} */
    public function has(string $key): bool
    {
        $path = $this->path($key);
        if (!is_file($path)) {
            return false;
        }
        $data = @unserialize((string) file_get_contents($path), ['allowed_classes' => false]);
        if (!is_array($data) || (($data['expires'] ?? 0) > 0 && $data['expires'] <= time())) {
            @unlink($path);
            return false;
        }
        return true;
    }

    /** Converte a chave num nome de ficheiro opaco e seguro. */
    private function path(string $key): string { return $this->directory . DIRECTORY_SEPARATOR . hash('sha256', $key) . '.cache'; }
}
