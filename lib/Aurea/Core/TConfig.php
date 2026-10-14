<?php
declare(strict_types=1);

namespace Aurea\Core;

/**
 * TConfig — leitura de configurações .php/.ini com chaves de pontos.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TConfig
{
    /** @var array Dados da configuração */
    protected array $data = [];
    /** @var array<string,self> Cache por ficheiro e data de modificação */
    protected static array $loaded = [];

    /**
     * Cria a configuração a partir de um array.
     *
     * @param array $data Dados iniciais
     */
    protected function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * Carrega a configuração a partir de um ficheiro.
     *
     * @param string $file Caminho do ficheiro .php (devolve array) ou .ini
     * @return self Configuração carregada
     * @throws \RuntimeException Se o ficheiro for inválido
     */
    public static function load(string $file): self
    {
        if (!file_exists($file)) {
            throw new \RuntimeException("Ficheiro de configuração não encontrado: {$file}");
        }
        $ext = strtolower((string) pathinfo($file, PATHINFO_EXTENSION));
        if ($ext === 'php') {
            $data = require $file;
            if (!is_array($data)) {
                throw new \RuntimeException("Configuração PHP inválida em: {$file}");
            }
            return new self($data);
        }
        if ($ext === 'ini') {
            $data = parse_ini_file($file, true, INI_SCANNER_TYPED);
            if ($data === false) {
                throw new \RuntimeException("Configuração INI inválida em: {$file}");
            }
            return new self($data);
        }
        throw new \RuntimeException("Formato de configuração não suportado: {$file}");
    }

    /**
     * Carrega e mantém em memória a configuração enquanto o ficheiro não mudar.
     * Útil para processos de longa duração; pedidos PHP tradicionais podem usar load().
     */
    public static function loadCached(string $file): self
    {
        $real = realpath($file) ?: $file;
        $key = $real . ':' . (string) (@filemtime($file) ?: 0);
        if (!isset(self::$loaded[$key])) {
            self::$loaded = array_filter(self::$loaded, fn($_, string $k): bool => !str_starts_with($k, $real . ':'));
            self::$loaded[$key] = self::load($file);
        }
        return self::$loaded[$key];
    }

    /** Limpa o cache de ficheiros de configuração, útil em testes ou reload. */
    public static function clearCache(): void
    {
        self::$loaded = [];
    }

    /**
     * Obtém um valor pela chave (suporta pontos).
     *
     * @param string $key Chave (ex.: db.host)
     * @param mixed $default Valor padrão se inexistente
     * @return mixed Valor guardado ou padrão
     */
    public function get(string $key, $default = null)
    {
        $parts = explode('.', $key);
        $node = $this->data;
        foreach ($parts as $part) {
            if (!is_array($node) || !array_key_exists($part, $node)) {
                return $default;
            }
            $node = $node[$part];
        }
        return $node;
    }

    /**
     * Define um valor pela chave (suporta pontos).
     *
     * @param string $key Chave (ex.: db.host)
     * @param mixed $value Valor a guardar
     */
    public function set(string $key, $value): void
    {
        $parts = explode('.', $key);
        $node = &$this->data;
        $last = array_pop($parts);
        foreach ($parts as $part) {
            if (!isset($node[$part]) || !is_array($node[$part])) {
                $node[$part] = [];
            }
            $node = &$node[$part];
        }
        $node[$last] = $value;
    }

    /**
     * Devolve todos os dados da configuração.
     *
     * @return array Dados completos
     */
    public function all(): array
    {
        return $this->data;
    }

    /**
     * Cria configuração a partir de array.
     */
    public static function fromArray(array $data): self
    {
        return new self($data);
    }

    /**
     * Funde valores sobre a configuração atual (chaves com pontos).
     *
     * @param array $data Pares chave => valor (chave pode usar pontos)
     */
    public function merge(array $data): void
    {
        foreach ($data as $key => $value) {
            $this->set((string) $key, $value);
        }
    }

    /**
     * Exige chaves obrigatórias.
     *
     * @param string[] $keys Chaves obrigatórias (suportam pontos)
     * @throws \RuntimeException Se alguma chave estiver ausente/vazia
     */
    public function require(array $keys): void
    {
        $missing = [];
        foreach ($keys as $key) {
            $value = $this->get($key);
            if ($value === null || $value === '') {
                $missing[] = $key;
            }
        }
        if ($missing) {
            throw new \RuntimeException('Configuração incompleta, faltam: ' . implode(', ', $missing));
        }
    }

    /**
     * Valida valores contra um schema simples chave => tipo, callable ou null.
     * Tipos aceites: string, int, float, bool, array e nullable:<tipo>.
     *
     * @param array<string,string|callable|null> $schema
     * @throws \RuntimeException Quando um valor obrigatório está ausente ou inválido
     */
    public function validate(array $schema): void
    {
        foreach ($schema as $key => $rule) {
            $value = $this->get($key);
            if ($rule === null) {
                if ($value === null) {
                    throw new \RuntimeException("Configuração obrigatória ausente: {$key}");
                }
                continue;
            }
            if (is_callable($rule)) {
                if (!$rule($value)) {
                    throw new \RuntimeException("Configuração inválida: {$key}");
                }
                continue;
            }
            $nullable = str_starts_with($rule, 'nullable:');
            $type = $nullable ? substr($rule, 9) : $rule;
            if ($value === null && $nullable) {
                continue;
            }
            $valid = match ($type) {
                'string' => is_string($value), 'int' => is_int($value), 'float' => is_float($value),
                'bool' => is_bool($value), 'array' => is_array($value), default => false,
            };
            if (!$valid) {
                throw new \RuntimeException("Configuração {$key} deve ser do tipo {$type}");
            }
        }
    }

    /**
     * Carrega variáveis de um ficheiro .env simples (KEY=valor, # comentários).
     * Não sobrescreve variáveis já definidas no ambiente.
     */
    public static function loadEnv(string $file): void
    {
        if (!is_file($file)) {
            return;
        }
        foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            if (str_starts_with($line, 'export ')) {
                $line = trim(substr($line, 7));
            }
            $pos = strpos($line, '=');
            if ($pos === false) {
                continue;
            }
            $key = trim(substr($line, 0, $pos));
            $value = trim(substr($line, $pos + 1));
            if (strlen($value) >= 2 && (($value[0] === '"' && $value[-1] === '"') || ($value[0] === "'" && $value[-1] === "'"))) {
                $value = substr($value, 1, -1);
            }
            if ($key !== '' && getenv($key) === false && !array_key_exists($key, $_ENV)) {
                putenv("{$key}={$value}");
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
        }
    }

    /**
     * Lê variável de ambiente com padrão.
     */
    public static function env(string $key, mixed $default = null): mixed
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
        return $value === false ? $default : $value;
    }

    /**
     * Carrega config base e sobrepõe com ficheiro de ambiente.
     * Ex: loadWithEnv('config/app.php', 'prod') tenta config/app.prod.php.
     */
    public static function loadWithEnv(string $file, ?string $env = null): self
    {
        $config = self::load($file);
        $env ??= (string) self::env('APP_ENV', '');
        if ($env !== '') {
            $dir = dirname($file);
            $base = basename($file);
            $dot = strrpos($base, '.');
            $candidate = $dir . '/' . ($dot === false ? $base . '.' . $env : substr($base, 0, $dot) . '.' . $env . substr($base, $dot));
            if (is_file($candidate)) {
                $overlay = self::load($candidate);
                $config->merge($overlay->all());
            }
        }
        return $config;
    }
}
