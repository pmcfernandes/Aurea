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
    protected int $timeout = 10;
    protected int $retries = 0;
    protected int $retryDelayMs = 200;
    protected ?string $bearerToken = null;
    protected ?string $basicAuth = null;
    protected int $connectTimeout = 5;
    protected bool $verifyTls = true;
    protected bool $preferCurl = false;
    protected array $lastResponseHeaders = [];

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
     * Define o timeout dos pedidos em segundos.
     *
     * @param int $seconds Timeout em segundos (mínimo 1)
     * @return static
     */
    public function setTimeout(int $seconds): static
    {
        $this->timeout = max(1, $seconds);
        return $this;
    }

    /**
     * Define tentativas de repetição em falhas de transporte/5xx.
     *
     * @param int $retries Número de tentativas extra
     * @param int $delayMs Espera entre tentativas em milissegundos
     * @return static
     */
    public function setRetries(int $retries, int $delayMs = 200): static
    {
        $this->retries = max(0, $retries);
        $this->retryDelayMs = max(0, $delayMs);
        return $this;
    }

    /**
     * Define o token Bearer de autenticação.
     *
     * @param string $token Token Bearer
     * @return static
     */
    public function setBearerToken(string $token): static
    {
        $this->bearerToken = $token;
        return $this;
    }

    /**
     * Define autenticação básica (Basic).
     *
     * @param string $user Utilizador
     * @param string $pass Palavra-passe
     * @return static
     */
    public function setBasicAuth(string $user, string $pass): static
    {
        $this->basicAuth = base64_encode("{$user}:{$pass}");
        return $this;
    }

    /** Define o timeout específico de ligação em segundos. */
    public function setConnectTimeout(int $seconds): static
    {
        $this->connectTimeout = max(1, $seconds);
        return $this;
    }

    /** Ativa ou desativa a validação TLS; só desative em ambientes de teste. */
    public function setVerifyTls(bool $verify = true): static
    {
        $this->verifyTls = $verify;
        return $this;
    }

    /** Prefere cURL quando a extensão estiver disponível. */
    public function useCurl(bool $enabled = true): static
    {
        $this->preferCurl = $enabled;
        return $this;
    }

    /** Define ou substitui um cabeçalho de pedido. */
    public function setHeader(string $name, string $value): static
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /** @return string[] Cabeçalhos recebidos na última resposta. */
    public function getLastResponseHeaders(): array
    {
        return $this->lastResponseHeaders;
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
        $attempt = 0;
        do {
            $result = $this->doRequest($method, $path, $data);
            if ($result['status'] !== 0 && ($result['status'] < 500 || $attempt >= $this->retries)) {
                return $result;
            }
            $attempt++;
            if ($attempt <= $this->retries) {
                usleep($this->retryDelayMs * 1000);
            }
        } while ($attempt <= $this->retries);
        return $result;
    }

    /**
     * Executa um pedido HTTP simples (sem repetição).
     *
     * @param string $method Método HTTP
     * @param string $path Caminho relativo ao baseUrl
     * @param array $data Dados (query no GET, JSON nos restantes)
     * @return array Vetor com status e data
     */
    protected function doRequest(string $method, string $path, array $data): array
    {
        $url = rtrim($this->baseUrl, '/') . '/' . ltrim($path, '/');
        $headerLines = ['Accept: application/json'];
        foreach ($this->headers as $name => $value) {
            $headerLines[] = "{$name}: {$value}";
        }
        if ($this->bearerToken) {
            $headerLines[] = "Authorization: Bearer {$this->bearerToken}";
        } elseif ($this->basicAuth) {
            $headerLines[] = "Authorization: Basic {$this->basicAuth}";
        }
        $options = [
            'http' => [
                'method' => $method,
                'header' => implode("\r\n", $headerLines),
                'ignore_errors' => true,
                'timeout' => $this->timeout,
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
        if ($this->preferCurl && function_exists('curl_init')) {
            return $this->doCurlRequest($method, $url, $data, $headerLines);
        }
        $options['http']['header'] = implode("\r\n", $headerLines) . ($method === 'GET' ? '' : "\r\nContent-Type: application/json; charset=UTF-8");
        $options['ssl'] = ['verify_peer' => $this->verifyTls, 'verify_peer_name' => $this->verifyTls];
        $context = stream_context_create($options);
        $raw = @file_get_contents($url, false, $context);
        $status = self::parseStatus($http_response_header ?? null);
        $this->lastResponseHeaders = $http_response_header ?? [];
        $this->lastStatus = $status;
        if ($raw === false) {
            return ['status' => $status, 'data' => null];
        }
        return ['status' => $status, 'data' => self::parseBody($raw)];
    }

    /** Executa o pedido por cURL, mantendo o mesmo envelope da implementação stream. */
    protected function doCurlRequest(string $method, string $url, array $data, array $headerLines): array
    {
        $curl = curl_init($url);
        curl_setopt_array($curl, [CURLOPT_CUSTOMREQUEST => $method, CURLOPT_HTTPHEADER => $headerLines, CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => $this->timeout, CURLOPT_CONNECTTIMEOUT => $this->connectTimeout, CURLOPT_SSL_VERIFYPEER => $this->verifyTls, CURLOPT_SSL_VERIFYHOST => $this->verifyTls ? 2 : 0, CURLOPT_HEADER => true]);
        if ($method !== 'GET') {
            $headerLines[] = 'Content-Type: application/json; charset=UTF-8';
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headerLines);
            curl_setopt($curl, CURLOPT_POSTFIELDS, (string) json_encode($data, JSON_UNESCAPED_UNICODE));
        }
        $response = curl_exec($curl);
        if ($response === false) {
            curl_close($curl);
            $this->lastStatus = 0;
            return ['status' => 0, 'data' => null];
        }
        $headerLength = (int) curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $this->lastStatus = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        $this->lastResponseHeaders = preg_split('/\r?\n/', trim(substr($response, 0, $headerLength))) ?: [];
        $body = substr($response, $headerLength);
        curl_close($curl);
        return ['status' => $this->lastStatus, 'data' => self::parseBody($body)];
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
