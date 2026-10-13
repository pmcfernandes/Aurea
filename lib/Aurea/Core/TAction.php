<?php
declare(strict_types=1);

namespace Aurea\Core;

/**
 * TAction — encapsula um evento (controller + método + parâmetros).
 *
 * Um evento é disparado via URL do Front Controller:
 *   index.php?class=ClienteList&method=onReload&limit=10
 *
 * Pode ser serializada para href ou onclick (navegação ou AJAX).
 *
 * Uso:
 *   new TAction(['ClienteList', 'onReload'], ['order' => 'nome']);
 *   new TAction([ClienteForm::class, 'onEdit'], ['key' => 1]);
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TAction
{
    protected ?string $controller = null;
    protected string $method = 'onShow';
    protected array $params = [];
    protected bool $static = true;

    /**
     * @param callable|array|string $action ['Classe','metodo'] | 'Classe::metodo' | closure descrita
     */
    public function __construct($action, array $params = [])
    {
        if (is_array($action) && count($action) === 2) {
            [$ctrl, $method] = $action;
            $this->controller = is_object($ctrl) ? get_class($ctrl) : (string) $ctrl;
            $this->static = !is_object($ctrl);
            $this->method = (string) $method;
        } elseif (is_string($action) && str_contains($action, '::')) {
            [$ctrl, $method] = explode('::', $action, 2);
            $this->controller = ltrim($ctrl, '\\');
            $this->method = $method;
        } elseif (is_string($action)) {
            $this->controller = $action;
            $this->method = 'onShow';
        } else {
            throw new \InvalidArgumentException('Action inválida. Use [Classe, metodo].');
        }
        $this->params = $params;
    }

    /**
     * Define um parâmetro do evento.
     *
     * @param string $name Nome do parâmetro
     * @param mixed $value Valor do parâmetro
     * @return static
     */
    public function setParameter(string $name, $value): static
    {
        $this->params[$name] = $value;
        return $this;
    }

    /**
     * Mescla parâmetros ao evento.
     *
     * @param array $params Parâmetros a adicionar
     * @return static
     */
    public function setParameters(array $params): static
    {
        $this->params = array_merge($this->params, $params);
        return $this;
    }

    /**
     * Obtém um parâmetro do evento.
     *
     * @param string $name Nome do parâmetro
     * @return mixed Valor do parâmetro ou null se inexistente
     */
    public function getParameter(string $name)
    {
        return $this->params[$name] ?? null;
    }

    /**
     * Obtém todos os parâmetros do evento.
     *
     * @return array Parâmetros do evento
     */
    public function getParameters(): array
    {
        return $this->params;
    }

    /**
     * Obtém o controller do evento.
     *
     * @return string|null Nome do controller ou null se indefinido
     */
    public function getController(): ?string
    {
        return $this->controller;
    }

    /**
     * Obtém o método do evento.
     *
     * @return string Nome do método
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Indica se o evento é estático.
     *
     * @return bool True se o evento é estático
     */
    public function isStatic(): bool
    {
        return $this->static;
    }

    /** Serializa o evento para URL do Front Controller */

    public function serialize(bool $absolute = false): string
    {
        $base = $absolute ? $this->baseUrl() : 'index.php';
        $query = http_build_query(array_merge(
            ['class' => $this->shortClass(), 'method' => $this->method],
            $this->params
        ));
        return $base . '?' . $query;
    }

    /** URL pronta para href */

    public function toHref(): string
    {
        return $this->serialize();
    }

    /** onclick que navega (com confirmação opcional) */

    public function toOnClick(?string $confirm = null): string
    {
        $url = $this->serialize();
        if ($confirm) {
            return "if(confirm('" . addslashes($confirm) . "')){window.location='{$url}'}";
        }
        return "window.location='{$url}'";
    }

    /** onclick AJAX: executa o evento sem recarregar e injeta resposta no alvo */

    public function toAjax(string $target = '#aurea-content'): string
    {
        $url = $this->serialize();
        return "__aureaAjax('{$url}', '{$target}'); return false;";
    }

    /** Executa o evento no servidor (usado pelo TApplication e em testes) */

    public function execute(array $extra = []): mixed
    {
        $class = $this->resolveClass();
        if (!class_exists($class)) {
            throw new \RuntimeException("Controller não encontrado: {$class}");
        }
        $param = array_merge($this->params, $extra);
        $instance = new $class;
        if (!method_exists($instance, $this->method)) {
            throw new \RuntimeException("Método {$this->method} não existe em {$class}");
        }
        if ((new \ReflectionMethod($class, $this->method))->isStatic()) {
            return $class::{$this->method}($param);
        }
        return $instance->{$this->method}($param);
    }

    // ---- internos ----

    /**
     * Obtém o nome curto do controller (sem namespace).
     *
     * @return string Nome curto do controller
     */
    protected function shortClass(): string
    {
        if (!$this->controller) {
            return '';
        }
        $parts = explode('\\', $this->controller);
        return end($parts);
    }

    /**
     * Resolve o nome completo do controller.
     *
     * @return string Nome completo da classe do controller
     * @throws \RuntimeException Se o evento não tem controller
     */
    protected function resolveClass(): string
    {
        if (!$this->controller) {
            throw new \RuntimeException('Action sem controller.');
        }
        if (class_exists($this->controller)) {
            return $this->controller;
        }
        $short = $this->shortClass();
        $candidate = "App\\Control\\{$short}";
        if (class_exists($candidate)) {
            return $candidate;
        }
        return $this->controller;
    }

    /**
     * Monta a URL base absoluta do Front Controller.
     *
     * @return string URL base absoluta
     */
    protected function baseUrl(): string
    {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $dir = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/');
        return "{$scheme}://{$host}{$dir}/index.php";
    }
}
