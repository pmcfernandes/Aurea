<?php
declare(strict_types=1);

namespace Aurea\Http;

/**
 * THttpRequest — pedido HTTP imutável (método, caminho, query, cabeçalhos, corpo).
 *
 * Para pedidos reais use THttpRequest::capture(); para testes construa
 * manualmente com o construtor público.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class THttpRequest
{
    /**
     * Cria um pedido HTTP com os dados informados.
     *
     * @param string $method Método HTTP (GET, POST, ...)
     * @param string $path Caminho do pedido (sem query string)
     * @param array $query Parâmetros da query string
     * @param array $headers Cabeçalhos do pedido
     * @param string $body Corpo bruto do pedido
     */
    public function __construct(
        protected string $method = 'GET',
        protected string $path = '/',
        protected array $query = [],
        protected array $headers = [],
        protected string $body = ''
    ) {
    }

    /**
     * Captura o pedido HTTP corrente a partir das superglobais.
     *
     * @return self Pedido capturado de $_SERVER/$_GET/php://input
     */
    public static function capture(): self
    {
        $method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
        $uri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
        $path = (string) (parse_url($uri, PHP_URL_PATH) ?: '/');
        $query = $_GET;
        $headers = self::captureHeaders();
        $body = (string) (file_get_contents('php://input') ?: '');
        return new self($method, $path, $query, $headers, $body);
    }

    /**
     * Obtém o método HTTP do pedido em maiúsculas.
     *
     * @return string Método HTTP (GET, POST, ...)
     */
    public function getMethod(): string
    {
        return strtoupper($this->method);
    }

    /**
     * Obtém o caminho do pedido sem a query string.
     *
     * @return string Caminho do pedido
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Obtém os parâmetros da query string.
     *
     * @return array Parâmetros da query
     */
    public function getQuery(): array
    {
        return $this->query;
    }

    /**
     * Obtém os cabeçalhos do pedido.
     *
     * @return array Cabeçalhos nome => valor
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Obtém o corpo bruto do pedido.
     *
     * @return string Corpo bruto
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * Obtém o corpo decodificado como vetor associativo.
     *
     * @return array|null Vetor decodificado ou null se vazio/inválido
     */
    public function getJson(): ?array
    {
        if (trim($this->body) === '') {
            return null;
        }
        $data = json_decode($this->body, true);
        return is_array($data) ? $data : null;
    }

    /**
     * Obtém um parâmetro (query primeiro, depois corpo JSON).
     *
     * @param string $key Nome do parâmetro
     * @param mixed $default Valor padrão se inexistente
     * @return mixed Valor do parâmetro ou padrão
     */
    public function get(string $key, $default = null)
    {
        if (array_key_exists($key, $this->query)) {
            return $this->query[$key];
        }
        $json = $this->getJson();
        if (is_array($json)) {
            if (array_key_exists($key, $json)) {
                return $json[$key];
            }
            if (isset($json['params']) && is_array($json['params']) && array_key_exists($key, $json['params'])) {
                return $json['params'][$key];
            }
        }
        return $default;
    }

    /**
     * Captura os cabeçalhos do pedido a partir de $_SERVER.
     *
     * @return array Cabeçalhos nome => valor
     */
    protected static function captureHeaders(): array
    {
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
            return is_array($headers) ? $headers : [];
        }
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (str_starts_with($name, 'HTTP_')) {
                $label = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                $headers[$label] = $value;
            }
        }
        if (isset($_SERVER['CONTENT_TYPE'])) {
            $headers['Content-Type'] = $_SERVER['CONTENT_TYPE'];
        }
        if (isset($_SERVER['CONTENT_LENGTH'])) {
            $headers['Content-Length'] = $_SERVER['CONTENT_LENGTH'];
        }
        return $headers;
    }
}
