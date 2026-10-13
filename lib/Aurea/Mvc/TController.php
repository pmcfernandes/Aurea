<?php
declare(strict_types=1);

namespace Aurea\Mvc;

use Aurea\Http\THttpResponse;

/**
 * TController — base fina para controllers MVC.
 * As ações usam nome curto (`show`) e correm como `onShow`,
 * com filtros before()/after().
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
abstract class TController
{
    protected array $route_params = [];

    /**
     * Cria o controller com os parâmetros da rota.
     *
     * @param array $route_params Parâmetros casados pelo TRouter
     */
    public function __construct(array $route_params = [])
    {
        $this->route_params = $route_params;
    }

    /**
     * Despacha ação curta para onXxx com filtros.
     *
     * @param string $name Nome curto da ação
     * @param array $args Argumentos
     * @return mixed Resultado de onXxx
     * @throws \RuntimeException Se a ação não existir
     */
    public function __call(string $name, array $args): mixed
    {
        $method = 'on' . ucfirst($name);
        if (!method_exists($this, $method)) {
            throw new \RuntimeException("Method {$method} not found in controller " . static::class);
        }
        if ($this->before() !== false) {
            $result = $this->{$method}(...$args);
            $this->after();
            return $result;
        }
        return null;
    }

    /**
     * Filtro executado antes da ação (devolver false cancela).
     *
     * @return mixed False para cancelar
     */
    protected function before(): mixed
    {
        return null;
    }

    /**
     * Filtro executado depois da ação.
     */
    protected function after(): void
    {
    }

    /**
     * Cria uma vista com variáveis.
     *
     * @param string $template Nome do template
     * @param array $vars Variáveis da vista
     * @return TView Vista pronta
     */
    protected function view(string $template, array $vars = []): TView
    {
        $view = new TView($template);
        foreach ($vars as $k => $v) {
            $view->set($k, $v);
        }
        return $view;
    }

    /**
     * Resposta JSON (o dispatcher envia-a).
     *
     * @param mixed $data Dados
     * @param int $status Código HTTP
     * @return THttpResponse Resposta JSON
     */
    protected function json(mixed $data, int $status = 200): THttpResponse
    {
        return THttpResponse::json($data, $status);
    }

    /**
     * Resposta de redirecionamento (o dispatcher envia-a).
     *
     * @param string $url Destino
     * @param int $code Código
     * @return THttpResponse Resposta de redirect
     */
    protected function redirect(string $url, int $code = 302): THttpResponse
    {
        return THttpResponse::redirect($url, $code);
    }
}
