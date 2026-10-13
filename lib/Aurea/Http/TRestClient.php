<?php
declare(strict_types=1);

namespace Aurea\Http;

/**
 * TRestClient — cliente REST mínimo sobre stream context (sem ext-curl).
 *
 * Envia e recebe JSON; cada chamada devolve ['status' => int, 'data' => mixed].
 * Falhas de transporte (ex.: serviço inalcançável) devolvem status 0 e data
 * null em vez de lançar exceção, para o chamador decidir.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TRestClient
{
    protected int $lastStatus = 0;

    /**
     * Cria o cliente REST com URL base e cabeçalhos extra.
     *
     * @param string $baseUrl URL base do serviço (ex.: http://api:8000)
     * @param array $headers Cabeçalhos extra nome => valor
     */
    public function __construct(
        protected string $baseUrl,
        protected array $headers = []
    ) {
    }

    /**
     * Executa um GET (dados seguem na query string).
     *
     * @param string $path Caminho relativo ao baseUrl
     * @param array $data Parâmetros da query string
     * @return array Vetor com status e data
     */
    public function get(string $path, array $data = []): array
    {
        return $this->request('GET', $path, $data);
    }

    /**
     * Executa um POST com corpo JSON.
     *
     * @param string $path Caminho relativo ao baseUrl
     * @param array $data Dados do corpo JSON
     * @return array Vetor com status e data
     */
    public function post(string $path, array $data = []): array
    {
        return $this->request('POST', $path, $data);
    }

    /**
     * Executa um PUT com corpo JSON.
     *
     * @param string $path Caminho relativo ao baseUrl
     * @param array $data Dados do corpo JSON
     * @return array Vetor com status e data
     */
    public function put(string $path, array $data = []): array
    {
        return $this->request('PUT', $path, $data);
    }

    /**
     * Executa um PATCH com corpo JSON.
     *
     * @param string $path Caminho relativo ao baseUrl
     * @param array $data Dados do corpo JSON
     * @return array Vetor com status e data
     */
    public function patch(string $path, array $data = []): array
    {
        return $this->request('PATCH', $path, $data);
    }

    /**
     * Executa um DELETE com corpo JSON.
     *
     * @param string $path Caminho relativo ao baseUrl
     * @param array $data Dados do corpo JSON
     * @return array Vetor com status e data
     */
    public function delete(string $path, array $data = []): array
    {
        return $this->request('DELETE', $path, $data);
    }

    /**
     * Obtém o estado da última chamada executada.
     *
     * @return int Último código de estado (0 se falhou o transporte)
     */
    public function getLastStatus(): int
    {
        return $this->lastStatus;
    }

    /**
     * Executa um pedido HTTP e normaliza a resposta.
     *
     * @param string $method Método HTTP
     * @param string $path Caminho relativo ao baseUrl
     * @param array $data Dados (query no GET, JSON nos restantes)
     * @return array Vetor com status e data
     */
    protected function request(string $method, string $path, array $data): array
    {
        $url = rtrim($this->baseUrl, '/') . '/' . ltrim($path, '/');
        $headerLines = ['Accept: application/json'];
        foreach ($this->headers as $name => $value) {
            $headerLines[] = "{$name}: {$value}";
        }
        $options = [
            'http' => [
                'method' => $method,
                'header' => implode("\r\n", $headerLines),
                'ignore_errors' => true,
                'timeout' => 10,
            ],
        ];
        if ($method === 'GET') {
            if ($data) {
                $url .= (str_contains($url, '?') ? '&' : '?') . http_build_query($data);
            }
        } else {
            $options['http']['header'] .= "\r\nContent-Type: application/json; charset=UTF-8";
            $options['http']['content'] = (string) json_encode($data, JSON_UNESCAPED_UNICODE);
        }
        $context = stream_context_create($options);
        $raw = @file_get_contents($url, false, $context);
        $status = self::parseStatus($http_response_header ?? null);
        $this->lastStatus = $status;
        if ($raw === false) {
            return ['status' => $status, 'data' => null];
        }
        return ['status' => $status, 'data' => self::parseBody($raw)];
    }

    /**
     * Extrai o código de estado dos cabeçalhos de resposta.
     *
     * @param array|null $responseHeaders Cabeçalhos em $http_response_header
     * @return int Código de estado ou 0 se indeterminável
     */
    protected static function parseStatus(?array $responseHeaders): int
    {
        if (!$responseHeaders) {
            return 0;
        }
        foreach ($responseHeaders as $line) {
            if (preg_match('/^HTTP\/\S+\s+(\d{3})/i', (string) $line, $m)) {
                return (int) $m[1];
            }
        }
        return 0;
    }

    /**
     * Decodifica o corpo (JSON quando possível, texto bruto senão).
     *
     * @param string $raw Corpo bruto da resposta
     * @return mixed Dados decodificados, texto bruto ou null se vazio
     */
    protected static function parseBody(string $raw): mixed
    {
        if (trim($raw) === '') {
            return null;
        }
        $data = json_decode($raw, true);
        return json_last_error() === JSON_ERROR_NONE ? $data : $raw;
    }
}
