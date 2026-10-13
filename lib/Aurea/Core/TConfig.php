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
}
