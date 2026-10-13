<?php
declare(strict_types=1);

namespace Aurea\Mvc;

/**
 * TRouter — roteador MVC com URLs amigáveis e tabela de rotas regex.
 *
 *   $router = new TRouter('App\\Controllers\\');
 *   $router->add('', ['controller' => 'Home', 'action' => 'index']);
 *   $router->add('{controller}/{action}');
 *   $router->add('{controller}/{id:\d+}/{action}');
 *   $router->dispatchFromRequest();
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TRouter
{
    /** @var array<string,array> */
    protected array $routes = [];
    protected array $params = [];
    protected string $controllerNamespace;

    /**
     * Cria o roteador.
     *
     * @param string $controllerNamespace Namespace dos controllers (com \\ final ou sem)
     */
    public function __construct(string $controllerNamespace = 'App\\Controllers\\')
    {
        $this->controllerNamespace = rtrim($controllerNamespace, '\\') . '\\';
    }

    /**
     * Define o namespace dos controllers.
     *
     * @param string $namespace Namespace base
     * @return static
     */
    public function setControllerNamespace(string $namespace): static
    {
        $this->controllerNamespace = rtrim($namespace, '\\') . '\\';
        return $this;
    }

    /**
     * Adiciona uma rota à tabela (`{nome}` e `{nome:regex}` viram grupos nomeados).
     *
     * @param string $route Rota (ex: posts/{id:\d+}/show)
     * @param array $params Parâmetros fixos (controller, action, namespace...)
     * @return static
     */
    public function add(string $route, array $params = []): static
    {
        $route = preg_replace('/\//', '\\/', $route) ?? $route;
        $route = preg_replace('/\{([a-z]+)\}/', '(?P<\1>[a-z-]+)', $route) ?? $route;
        $route = preg_replace('/\{([a-z]+):([^\}]+)\}/', '(?P<\1>\2)', $route) ?? $route;
        $this->routes['/^' . $route . '$/i'] = $params;
        return $this;
    }

    /**
     * Devolve a tabela de rotas (regex => params).
     *
     * @return array Tabela de rotas
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * Tenta casar a URL com a tabela (preenche os params em caso de match).
     *
     * @param string $url URL sem query string
     * @return bool Verdadeiro se alguma rota casou
     */
    public function match(string $url): bool
    {
        foreach ($this->routes as $route => $params) {
            if (preg_match($route, $url, $matches)) {
                foreach ($matches as $key => $match) {
                    if (is_string($key)) {
                        $params[$key] = $match;
                    }
                }
                $this->params = $params;
                return true;
            }
        }
        return false;
    }

    /**
     * Devolve os parâmetros da rota casada.
     *
     * @return array Parâmetros (controller, action, + grupos)
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * Despacha a URL: instancia o controller e corre a ação.
     * A ação da rota usa nome curto (`show` => `onShow` via __call,
     * com filtros before/after do controller).
     *
     * @param string $url URL sem query string
     * @return mixed Resultado da ação (string é impressa; THttpResponse é enviada)
     * @throws \RuntimeException Se não houver rota (404) ou controller (500)
     */
    public function dispatch(string $url): mixed
    {
        $url = $this->removeQueryStringVariables($url);
        if (!$this->match($url)) {
            throw new \RuntimeException('No route matched.', 404);
        }
        $controller = $this->convertToStudlyCaps($this->params['controller'] ?? '') . 'Controller';
        $fqcn = $this->controllerNamespace . $controller;
        if (!class_exists($fqcn)) {
            throw new \RuntimeException("Controller class {$fqcn} not found (rota usa nome curto, ex: 'home' => HomeController)");
        }
        $action = $this->convertToCamelCase($this->params['action'] ?? 'index');
        if (str_starts_with(strtolower($action), 'on')) {
            throw new \RuntimeException("Use o nome curto da ação (ex: 'show', não 'onShow')");
        }
        $object = new $fqcn($this->params);
        $result = $object->{$action}();
        if ($result instanceof \Aurea\Http\THttpResponse) {
            $result->send();
        } elseif (is_string($result)) {
            echo $result;
        }
        return $result;
    }

    /**
     * Despacha a requisição corrente (?url= ou path do servidor).
     *
     * @return mixed Resultado da ação
     */
    public function dispatchFromRequest(): mixed
    {
        if (isset($_GET['url'])) {
            return $this->dispatch((string) $_GET['url']);
        }
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
        $base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
        if ($base !== '' && str_starts_with($path, $base)) {
            $path = substr($path, strlen($base));
        }
        return $this->dispatch(trim($path, '/'));
    }

    /**
     * Converte com-hífens em StudlyCaps (post-authors => PostAuthors).
     *
     * @param string $string Texto de entrada
     * @return string Em StudlyCaps
     */
    protected function convertToStudlyCaps(string $string): string
    {
        return str_replace(' ', '', ucwords(str_replace('-', ' ', $string)));
    }

    /**
     * Converte com-hífens em camelCase (add-new => addNew).
     *
     * @param string $string Texto de entrada
     * @return string Em camelCase
     */
    protected function convertToCamelCase(string $string): string
    {
        return lcfirst($this->convertToStudlyCaps($string));
    }

    /**
     * Remove query string (?a=b) do fim da URL.
     *
     * @param string $url URL completa
     * @return string Só o caminho
     */
    protected function removeQueryStringVariables(string $url): string
    {
        if ($url !== '') {
            $parts = explode('&', $url, 2);
            $url = str_contains($parts[0], '=') ? '' : $parts[0];
        }
        return $url;
    }
}
