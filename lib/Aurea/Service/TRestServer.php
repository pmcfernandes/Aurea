<?php
declare(strict_types=1);

namespace Aurea\Service;

use Aurea\Http\THttpRequest;
use Aurea\Http\THttpResponse;
use PDO;

/**
 * TRestServer — despachante JSON-RPC simples (service + method).
 *
 * Regista serviços (nome => handler) e atende pedidos onde 'service' e
 * 'method' vêm da query ou do corpo JSON. O método do handler recebe um
 * vetor de parâmetros e o seu retorno é serializado para JSON. Em caso de
 * erro (serviço/método inexistente, exceção) devolve {"error": "..."}.
 *
 *   $server = new TRestServer;
 *   $server->addService('Cliente', new ClienteService);
 *   echo $server->handle(); // ?service=Cliente&method=onList
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TRestServer
{
    /** @var array<string, string|object> */
    protected array $services = [];
    /** @var callable|null fn(array $param, ?THttpRequest $req): bool|string */
    protected $authCallback = null;
    protected int $rateLimit = 0;
    protected int $rateWindow = 60;
    protected ?PDO $rateLimitConnection = null;
    protected string $rateLimitTable = 'aurea_rate_limit';
    /** @var array<string, array<int>> */
    protected static array $hits = [];
    /** @var array<string,array{service:string,method:string}> */
    protected array $routes = [];
    /** @var array<string,string[]> */
    protected array $serviceMethods = [];
    /** @var array<string,array{request:array<string,mixed>,response:array<string,mixed>}> */
    protected array $schemas = [];

    /**
     * Regista um serviço (instância ou nome de classe a instanciar).
     *
     * @param string $name Nome do serviço no pedido
     * @param string|object $handler Instância ou classe do handler
     * @return static
     */
    public function addService(string $name, string|object $handler): static
    {
        $this->services[$name] = $handler;
        return $this;
    }

    /**
     * Expõe explicitamente os métodos públicos de um serviço.
     * Sem esta configuração, só métodos públicos no formato onEvento são aceites.
     *
     * @param string[] $methods
     * @return static
     */
    public function setServiceMethods(string $service, array $methods): static
    {
        $this->serviceMethods[$service] = array_values(array_filter($methods, 'is_string'));
        return $this;
    }

    /**
     * Regista uma rota REST exata, por exemplo GET /clientes => Cliente::onList.
     * @return static
     */
    public function addRoute(string $httpMethod, string $path, string $service, string $method): static
    {
        $key = strtoupper($httpMethod) . ' ' . $this->normalizePath($path);
        $this->routes[$key] = ['service' => $service, 'method' => $method];
        return $this;
    }

    /**
     * Define autenticação: deve devolver true ou string com motivo.
     */
    public function setAuthCallback(?callable $callback): static
    {
        $this->authCallback = $callback;
        return $this;
    }

    /**
     * Limite de pedidos por janela (por IP+serviço).
     */
    public function setRateLimit(int $maxRequests, int $windowSeconds = 60): static
    {
        $this->rateLimit = max(0, $maxRequests);
        $this->rateWindow = max(1, $windowSeconds);
        return $this;
    }

    /**
     * Persiste o rate limit numa base de dados, para o partilhar entre processos.
     * A tabela é criada automaticamente na primeira utilização.
     */
    public function setRateLimitDatabase(PDO $connection, string $table = 'aurea_rate_limit'): static
    {
        if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $table)) {
            throw new \InvalidArgumentException('Nome de tabela de rate limit inválido.');
        }
        $this->rateLimitConnection = $connection;
        $this->rateLimitTable = $table;
        return $this;
    }

    /**
     * Define os schemas request/response de uma operação de serviço.
     * O schema usa a forma OpenAPI: `required` e `properties`, com `type`.
     * O request é validado antes de chamar o handler.
     *
     * @param array<string,mixed> $requestSchema
     * @param array<string,mixed> $responseSchema
     */
    public function setServiceSchema(string $service, string $method, array $requestSchema = [], array $responseSchema = []): static
    {
        $this->schemas[$service . '::' . $method] = ['request' => $requestSchema, 'response' => $responseSchema];
        return $this;
    }

    /**
     * Limpa os contadores do rate limit (útil em testes).
     */
    public static function resetRateLimit(): void
    {
        self::$hits = [];
    }

    /**
     * Atende um pedido e devolve a resposta JSON.
     *
     * @param THttpRequest|null $req Pedido (null captura o pedido corrente)
     * @return string Resposta JSON (resultado ou {"error": "..."})
     */
    public function handle(?THttpRequest $req = null): string
    {
        return $this->handleResponse($req)->getBody();
    }

    /**
     * Atende o pedido e devolve uma resposta HTTP estruturada.
     * O chamador pode usar ->send() para emitir estado e cabeçalhos.
     * @param THttpRequest|null $req Pedido; null captura o pedido atual
     * @return THttpResponse Envelope JSON ou resposta devolvida pelo handler
     */
    public function handleResponse(?THttpRequest $req = null): THttpResponse
    {
        try {
            $req ??= THttpRequest::capture();
            $service = $req->get('service');
            $method = $req->get('method');
            $route = $this->routes[$req->getMethod() . ' ' . $this->normalizePath($req->getPath())] ?? null;
            if ($route !== null) {
                $service = $route['service'];
                $method = $route['method'];
            }
            if (empty($service)) {
                throw new \RuntimeException("Parâmetro 'service' é obrigatório", 400);
            }
            if (empty($method)) {
                throw new \RuntimeException("Parâmetro 'method' é obrigatório", 400);
            }
            $this->checkRateLimit((string) $service);
            if ($this->authCallback !== null) {
                $auth = ($this->authCallback)($this->resolveParams($req), $req);
                if ($auth !== true) {
                    throw new \RuntimeException(is_string($auth) && $auth !== '' ? $auth : 'Não autorizado', 401);
                }
            }
            if (!array_key_exists((string) $service, $this->services)) {
                throw new \RuntimeException("Serviço '{$service}' não encontrado", 404);
            }
            $handler = $this->services[(string) $service];
            if (is_string($handler)) {
                if (!class_exists($handler)) {
                    throw new \RuntimeException("Classe do serviço '{$service}' não encontrada: {$handler}", 500);
                }
                $handler = new $handler;
            }
            if (!$this->isAllowedMethod((string) $service, $handler, (string) $method)) {
                throw new \RuntimeException("Método '{$method}' não existe no serviço '{$service}'", 404);
            }
            $params = $this->resolveParams($req);
            $this->validateRequest((string) $service, (string) $method, $params);
            $result = $handler->{(string) $method}($params);
            if ($result instanceof THttpResponse) {
                return $result;
            }
            return THttpResponse::json(['data' => $result]);
        } catch (\Throwable $e) {
            $code = (int) $e->getCode();
            if ($code < 400 || $code >= 600) {
                $code = 500;
            }
            return THttpResponse::json(['error' => $e->getMessage(), 'code' => $code], $code);
        }
    }

    /** Verifica a allow-list explícita ou a convenção pública onEvento. */
    private function isAllowedMethod(string $service, object $handler, string $method): bool
    {
        if (!method_exists($handler, $method) || !is_callable([$handler, $method])) {
            return false;
        }
        if (isset($this->serviceMethods[$service])) {
            return in_array($method, $this->serviceMethods[$service], true);
        }
        $reflection = new \ReflectionMethod($handler, $method);
        return $reflection->isPublic() && (bool) preg_match('/^on[A-Z][A-Za-z0-9_]*$/', $method);
    }

    /** Normaliza uma rota para comparação sem barras finais. */
    private function normalizePath(string $path): string
    {
        return '/' . trim($path, '/');
    }

    /**
     * Verifica o limite de pedidos por janela (IP+serviço).
     *
     * @param string $service Nome do serviço
     * @throws \RuntimeException Se o limite for excedido (429)
     */
    protected function checkRateLimit(string $service): void
    {
        if ($this->rateLimit <= 0) {
            return;
        }
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'cli';
        $key = $ip . '|' . $service;
        $now = time();
        $windowStart = $now - $this->rateWindow;
        if ($this->rateLimitConnection !== null) {
            $table = $this->rateLimitTable;
            $this->rateLimitConnection->exec("CREATE TABLE IF NOT EXISTS {$table} (rate_key VARCHAR(255) NOT NULL, created_at INTEGER NOT NULL)");
            $delete = $this->rateLimitConnection->prepare("DELETE FROM {$table} WHERE created_at < ?");
            $delete->execute([$windowStart]);
            $count = $this->rateLimitConnection->prepare("SELECT COUNT(*) FROM {$table} WHERE rate_key = ? AND created_at >= ?");
            $count->execute([$key, $windowStart]);
            if ((int) $count->fetchColumn() >= $this->rateLimit) {
                throw new \RuntimeException('Limite de pedidos excedido', 429);
            }
            $insert = $this->rateLimitConnection->prepare("INSERT INTO {$table} (rate_key, created_at) VALUES (?, ?)");
            $insert->execute([$key, $now]);
            return;
        }
        self::$hits[$key] = array_values(array_filter(self::$hits[$key] ?? [], fn($t) => $t >= $windowStart));
        if (count(self::$hits[$key]) >= $this->rateLimit) {
            throw new \RuntimeException('Limite de pedidos excedido', 429);
        }
        self::$hits[$key][] = $now;
    }

    /**
     * Gera especificação OpenAPI mínima dos serviços registados.
     */
    public function openApi(string $title = 'Aurea API', string $version = '1.0.0'): array
    {
        $paths = [];
        foreach ($this->routes as $routeKey => $route) {
            [$verb, $path] = explode(' ', $routeKey, 2);
            $schema = $this->schemas[$route['service'] . '::' . $route['method']] ?? ['request' => [], 'response' => []];
            $operation = [
                'summary' => "{$route['service']}::{$route['method']}",
                'responses' => ['200' => ['description' => 'Resposta bem-sucedida']],
            ];
            if ($schema['request'] !== []) {
                $operation['requestBody'] = ['required' => true, 'content' => ['application/json' => ['schema' => $schema['request']]]];
            }
            if ($schema['response'] !== []) {
                $operation['responses']['200']['content'] = ['application/json' => ['schema' => $schema['response']]];
            }
            $paths[$path][strtolower($verb)] = $operation;
        }
        foreach ($this->services as $name => $handler) {
            $class = is_string($handler) ? $handler : $handler::class;
            $methods = [];
            if (!(is_string($handler) && !class_exists($handler))) {
                $methods = get_class_methods($class) ?: [];
            }
            foreach ($methods as $method) {
                if (str_starts_with($method, '__')) {
                    continue;
                }
                if (!$this->isDocumentedMethod($name, $class, $method)) {
                    continue;
                }
                $path = "/{$name}/{$method}";
                if (isset($paths[$path])) {
                    continue;
                }
                $schema = $this->schemas[$name . '::' . $method] ?? ['request' => [], 'response' => []];
                $operation = [
                        'summary' => "{$name}::{$method}",
                        'parameters' => [
                            ['name' => 'service', 'in' => 'query', 'schema' => ['type' => 'string']],
                            ['name' => 'method', 'in' => 'query', 'schema' => ['type' => 'string']],
                        ],
                        'responses' => ['200' => ['description' => 'Resposta bem-sucedida']],
                ];
                if ($schema['response'] !== []) {
                    $operation['responses']['200']['content'] = ['application/json' => ['schema' => $schema['response']]];
                }
                $paths[$path] = ['get' => $operation];
            }
        }
        return [
            'openapi' => '3.0.0',
            'info' => ['title' => $title, 'version' => $version],
            'paths' => $paths,
        ];
    }

    /**
     * Monta os parâmetros do método (query menos service/method + corpo JSON).
     *
     * @param THttpRequest $req Pedido a processar
     * @return array Parâmetros para o método do handler
     */
    protected function resolveParams(THttpRequest $req): array
    {
        $params = $req->getQuery();
        unset($params['service'], $params['method']);
        $json = $req->getJson();
        if (is_array($json)) {
            if (isset($json['params']) && is_array($json['params'])) {
                $params = array_merge($params, $json['params']);
            }
            foreach ($json as $key => $value) {
                if (!in_array($key, ['service', 'method', 'params'], true)) {
                    $params[$key] = $value;
                }
            }
        }
        return $params;
    }

    /** @param array<string,mixed> $params */
    private function validateRequest(string $service, string $method, array $params): void
    {
        $schema = $this->schemas[$service . '::' . $method]['request'] ?? [];
        if ($schema === []) {
            return;
        }
        foreach ((array) ($schema['required'] ?? []) as $field) {
            if (!array_key_exists($field, $params) || $params[$field] === null || $params[$field] === '') {
                throw new \RuntimeException("Parâmetro obrigatório ausente: {$field}", 422);
            }
        }
        foreach ((array) ($schema['properties'] ?? []) as $field => $definition) {
            if (!array_key_exists($field, $params) || !is_array($definition) || !isset($definition['type'])) {
                continue;
            }
            $valid = match ($definition['type']) {
                'string' => is_string($params[$field]), 'integer' => is_int($params[$field]) || ctype_digit((string) $params[$field]),
                'number' => is_numeric($params[$field]), 'boolean' => is_bool($params[$field]),
                'array' => is_array($params[$field]), 'object' => is_array($params[$field]) || is_object($params[$field]), default => true,
            };
            if (!$valid) {
                throw new \RuntimeException("Parâmetro inválido: {$field}", 422);
            }
        }
    }

    /** Evita publicar helpers privados da API na especificação. */
    private function isDocumentedMethod(string $service, string $class, string $method): bool
    {
        if (isset($this->serviceMethods[$service])) {
            return in_array($method, $this->serviceMethods[$service], true);
        }
        $reflection = new \ReflectionMethod($class, $method);
        return $reflection->isPublic() && (bool) preg_match('/^on[A-Z][A-Za-z0-9_]*$/', $method);
    }
}
