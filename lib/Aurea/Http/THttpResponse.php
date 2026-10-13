<?php
declare(strict_types=1);

namespace Aurea\Http;

/**
 * THttpResponse — resposta HTTP (corpo, estado e cabeçalhos).
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class THttpResponse
{
    /**
     * Cria uma resposta HTTP com corpo, estado e cabeçalhos.
     *
     * @param string $body Corpo da resposta
     * @param int $status Código de estado HTTP
     * @param array $headers Cabeçalhos nome => valor
     */
    public function __construct(
        protected string $body = '',
        protected int $status = 200,
        protected array $headers = []
    ) {
    }

    /**
     * Cria uma resposta JSON a partir de dados.
     *
     * @param mixed $data Dados a serializar para JSON
     * @param int $status Código de estado HTTP
     * @return self Resposta JSON
     */
    public static function json(mixed $data, int $status = 200): self
    {
        $body = (string) json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return new self($body, $status, ['Content-Type' => 'application/json; charset=UTF-8']);
    }

    /**
     * Obtém o código de estado da resposta.
     *
     * @return int Código de estado HTTP
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * Obtém o corpo da resposta.
     *
     * @return string Corpo da resposta
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * Obtém os cabeçalhos da resposta.
     *
     * @return array Cabeçalhos nome => valor
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Cria uma resposta de download de ficheiro.
     *
     * @param string $file Caminho do ficheiro
     * @param string|null $name Nome proposto (omissão = basename)
     * @param string $mime Tipo MIME
     * @return self Resposta com o conteúdo do ficheiro
     * @throws \RuntimeException Se o ficheiro não existir
     */
    public static function download(string $file, ?string $name = null, string $mime = 'application/octet-stream'): self
    {
        if (!is_file($file)) {
            throw new \RuntimeException("Ficheiro não encontrado: {$file}");
        }
        $body = (string) file_get_contents($file);
        return new self($body, 200, [
            'Content-Type' => $mime,
            'Content-Length' => (string) strlen($body),
            'Content-Disposition' => 'attachment; filename="' . addslashes($name ?? basename($file)) . '"',
        ]);
    }

    /**
     * Cria uma resposta de redirecionamento.
     *
     * @param string $url Destino
     * @param int $code Código (301, 302, 303, 307, 308)
     * @return self Resposta de redirecionamento
     */
    public static function redirect(string $url, int $code = 302): self
    {
        return new self('', $code, ['Location' => $url]);
    }

    /**
     * Emite a resposta (estado, cabeçalhos e corpo).
     */
    public function send(): void
    {
        if (!headers_sent()) {
            http_response_code($this->status);
            foreach ($this->headers as $name => $value) {
                header("{$name}: {$value}");
            }
        }
        echo $this->body;
    }
}
