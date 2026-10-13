<?php
declare(strict_types=1);

namespace Aurea\Service;

use Aurea\Http\THttpRequest;

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
     * Atende um pedido e devolve a resposta JSON.
     *
     * @param THttpRequest|null $req Pedido (null captura o pedido corrente)
     * @return string Resposta JSON (resultado ou {"error": "..."})
     */
    public function handle(?THttpRequest $req = null): string
    {
        try {
            $req ??= THttpRequest::capture();
            $service = $req->get('service');
            $method = $req->get('method');
            if (empty($service)) {
                throw new \RuntimeException("Parâmetro 'service' é obrigatório");
            }
            if (empty($method)) {
                throw new \RuntimeException("Parâmetro 'method' é obrigatório");
            }
            if (!array_key_exists((string) $service, $this->services)) {
                throw new \RuntimeException("Serviço '{$service}' não encontrado");
            }
            $handler = $this->services[(string) $service];
            if (is_string($handler)) {
                if (!class_exists($handler)) {
                    throw new \RuntimeException("Classe do serviço '{$service}' não encontrada: {$handler}");
                }
                $handler = new $handler;
            }
            if (!method_exists($handler, (string) $method) || !is_callable([$handler, (string) $method])) {
                throw new \RuntimeException("Método '{$method}' não existe no serviço '{$service}'");
            }
            $result = $handler->{(string) $method}($this->resolveParams($req));
            return (string) json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } catch (\Throwable $e) {
            return (string) json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
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
}
