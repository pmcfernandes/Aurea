<?php
declare(strict_types=1);

namespace Aurea\Core;

use Aurea\Core\TRequest;

/**
 * TApplication — Front Controller.
 *
 * Roteia ?class=NomeDoController&method=onEvento para
 * App\Control\NomeDoController::metodo($param).
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TApplication
{
    protected static string $controlNamespace = 'App\\Control\\';
    protected static string $defaultClass = 'HomeView';
    protected static string $defaultMethod = 'onShow';
    protected static string $title = 'Aurea Framework';
    protected static ?string $menuHtml = null;
    protected static bool $debug = false;
    /** @var callable|null Callback de autorização: fn(string $class, string $method, array $param): bool|string */
    protected static $authCallback = null;
    protected static ?string $appEnv = null;
    /** @var callable|null fn(string $content,string $active):string */
    protected static $layoutRenderer = null;
    protected static bool $permissionGuard = false;
    protected static bool $csrfProtection = false;

    /**
     * Define o namespace dos controllers.
     *
     * @param string $ns Namespace dos controllers
     */
    public static function setControlNamespace(string $ns): void
    {
        self::$controlNamespace = rtrim($ns, '\\') . '\\';
    }

    /**
     * Define a rota padrão do Front Controller.
     *
     * @param string $class Classe padrão
     * @param string $method Método padrão
     */
    public static function setDefault(string $class, string $method = 'onShow'): void
    {
        self::$defaultClass = $class;
        self::$defaultMethod = $method;
    }

    /**
     * Define o título da aplicação.
     *
     * @param string $title Título da aplicação
     */
    public static function setTitle(string $title): void
    {
        self::$title = $title;
    }

    /** Define o HTML do menu (ex: vindo de TMenu::fromXml($file)->getContents()) */

    public static function setMenuHtml(string $html): void
    {
        self::$menuHtml = $html;
    }

    /** Carrega o menu de um menu.xml, com callback opcional de permissão */

    public static function setMenuXml(string $file, ?callable $allowed = null): void
    {
        self::$menuHtml = \Aurea\Container\TMenu::fromXml($file, $allowed)->getContents();
    }

    /**
     * Define ambiente (dev/test/prod) e modo debug.
     * Por padrão debug segue APP_ENV !== 'prod'.
     */
    public static function setEnv(string $env, ?bool $debug = null): void
    {
        self::$appEnv = $env;
        if ($debug !== null) {
            self::$debug = $debug;
        } else {
            self::$debug = $env !== 'prod';
        }
    }

    /**
     * Liga/desliga o modo debug (stack traces nas respostas de erro).
     *
     * @param bool $debug True para ativar o modo debug
     */
    public static function setDebug(bool $debug): void
    {
        self::$debug = $debug;
    }

    /**
     * Indica se o modo debug está ativo.
     *
     * @return bool True se o modo debug está ativo
     */
    public static function isDebug(): bool
    {
        return self::$debug;
    }

    /**
     * Obtém o ambiente atual da aplicação.
     *
     * @return string Ambiente (ex.: dev, test, prod)
     */
    public static function getEnv(): string
    {
        if (self::$appEnv !== null) {
            return self::$appEnv;
        }
        $env = TConfig::env('APP_ENV', 'dev');
        return is_string($env) && $env !== '' ? $env : 'dev';
    }

    /**
     * Define callback de autorização executado antes de cada despacho.
     * Deve devolver true (autorizado), false ou string com motivo (negado).
     */
    public static function setAuthCallback(?callable $callback): void
    {
        self::$authCallback = $callback;
    }

    /** Ativa a verificação TPermission::checkAction() antes do dispatch. */
    public static function usePermissionGuard(bool $enabled = true): void
    {
        self::$permissionGuard = $enabled;
    }

    /** Exige token CSRF em pedidos HTTP mutáveis processados por run(). */
    public static function setCsrfProtection(bool $enabled = true): void
    {
        self::$csrfProtection = $enabled;
    }

    /** Define um renderer de layout, separando a composição visual do dispatcher. */
    public static function setLayoutRenderer(?callable $renderer): void
    {
        self::$layoutRenderer = $renderer;
    }

    /**
     * Executa o callback de autorização antes do despacho.
     *
     * @param string $class Classe do controller
     * @param string $method Método do evento
     * @param array $param Parâmetros do pedido
     * @throws \RuntimeException Se o acesso for negado (403)
     */
    protected static function authorize(string $class, string $method, array $param): void
    {
        if (self::$permissionGuard && !\Aurea\Auth\TPermission::checkAction($class, $method)) {
            throw new \RuntimeException("Acesso negado a {$class}::{$method}", 403);
        }
        if (self::$authCallback === null) {
            return;
        }
        $result = (self::$authCallback)($class, $method, $param);
        if ($result === true) {
            return;
        }
        $reason = is_string($result) && $result !== '' ? $result : "Acesso negado a {$class}::{$method}";
        throw new \RuntimeException($reason, 403);
    }

    /** Valida CSRF em POST/PUT/PATCH/DELETE quando a proteção foi ativada. */
    protected static function verifyCsrf(array $param): void
    {
        if (!self::$csrfProtection) {
            return;
        }
        $verb = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
        if (in_array($verb, ['GET', 'HEAD', 'OPTIONS'], true)) {
            return;
        }
        $token = $param[TCsrf::FIELD] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        if (!TCsrf::validate($token)) {
            throw new \RuntimeException('Token CSRF inválido ou expirado.', 419);
        }
    }

    /**
     * Envelope padrão de erro para APIs.
     */
    public static function errorEnvelope(\Throwable $e): array
    {
        $code = (int) $e->getCode();
        if ($code < 400 || $code >= 600) {
            $code = $e instanceof \RuntimeException && str_contains($e->getMessage(), 'não encontrada') ? 404 : 500;
            if ((int) $e->getCode() === 403) {
                $code = 403;
            }
        }
        $error = ['error' => $e->getMessage(), 'code' => $code];
        if (self::$debug) {
            $error['trace'] = explode("\n", $e->getTraceAsString());
        }
        return $error;
    }

    /** Ponto de entrada: despacha o evento e renderiza a página */

    public static function run(?string $class = null, ?string $method = null, array $param = []): void
    {
        $class  = $class  ?? ($_GET['class']  ?? self::$defaultClass);
        $method = $method ?? ($_GET['method'] ?? self::$defaultMethod);
        $param  = $param ?: TRequest::all();
        unset($param['class'], $param['method']);

        // sanitiza nomes (evita path traversal / execução arbitrária)
        $class  = preg_replace('/[^A-Za-z0-9_]/', '', (string) $class);
        $method = preg_replace('/[^A-Za-z0-9_]/', '', (string) $method);
        if ($class === '' || $method === '') {
            $class = self::$defaultClass;
            $method = self::$defaultMethod;
        }

        try {
            self::verifyCsrf($param);
            self::authorize($class, $method, $param);
            if (TRequest::wantsJson()) {
                header('Content-Type: application/json; charset=UTF-8');
                echo self::dispatchJson($class, $method, $param);
                return;
            }
            $content = self::dispatch($class, $method, $param);
            if (TRequest::isAjax()) {
                echo $content . TScript::render();
                return;
            }
            echo self::template($content, $class);
        } catch (\Throwable $e) {
            \Aurea\Log\TLog::error('Falha ao processar pedido', [
                'exception' => $e,
                'controller' => $class,
                'action' => $method,
            ]);
            $envelope = self::errorEnvelope($e);
            http_response_code($envelope['code']);
            if (TRequest::wantsJson()) {
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode($envelope);
                return;
            }
            $msg = htmlspecialchars($envelope['error'], ENT_QUOTES);
            if (TRequest::isAjax()) {
                echo "<div class='alert alert-danger'>{$msg}</div>";
                return;
            }
            $detail = '';
            if (self::$debug) {
                $trace = htmlspecialchars($e->getTraceAsString(), ENT_QUOTES);
                $detail = "<pre class='mt-3 small text-muted'>{$trace}</pre>";
            }
            echo self::template(
                "<div class='alert alert-danger'><strong>Erro:</strong> {$msg}</div>" . $detail,
                $class
            );
        }
    }

    /** Executa controller/método e devolve o HTML */

    public static function dispatch(string $class, string $method, array $param = []): string
    {
        self::authorize($class, $method, $param);
        $fqcn = self::$controlNamespace . $class;
        if (!class_exists($fqcn)) {
            throw new \RuntimeException("Página não encontrada: {$class}");
        }
        self::assertDispatchableMethod($fqcn, $method, $class);
        $controller = new $fqcn;

        ob_start();
        $ref = new \ReflectionMethod($fqcn, $method);
        if ($controller instanceof TPage) {
            $controller->onBeforeAction($method, $param);
        }
        $result = $ref->isStatic()
            ? $fqcn::{$method}($param)
            : $controller->{$method}($param);
        if ($controller instanceof TPage) {
            $controller->onAfterAction($method, $param, $result);
        }

        // se o método imprimiu via show(), captura; se devolveu Page/TComponent, renderiza
        $echoed = (string) ob_get_clean();
        if ($result instanceof TComponent) {
            return $result->getContents() . $echoed;
        }
        if (is_string($result)) {
            return $echoed . $result;
        }
        // padrão: método instancia a página no __construct e imprime via parent::add
        if ($controller instanceof TComponent) {
            return $controller->getContents() . $echoed;
        }
        return $echoed;
    }

    /**
     * Despacha um evento e devolve JSON.
     * Controllers de API devolvem arrays:
     *   public function onList($param): array { return ['data' => [...]]; }
     */
    public static function dispatchJson(string $class, string $method, array $param = []): string
    {
        [$fqcn, $controller] = self::resolve($class, $method);
        ob_start();
        $ref = new \ReflectionMethod($fqcn, $method);
        if ($controller instanceof TPage) {
            $controller->onBeforeAction($method, $param);
        }
        $result = $ref->isStatic()
            ? $fqcn::{$method}($param)
            : $controller->{$method}($param);
        if ($controller instanceof TPage) {
            $controller->onAfterAction($method, $param, $result);
        }
        $echoed = (string) ob_get_clean();

        if ($result instanceof TComponent) {
            return json_encode(['html' => $result->getContents() . $echoed]);
        }
        if (is_array($result) || $result instanceof \JsonSerializable) {
            return json_encode($result);
        }
        return json_encode(['html' => $echoed . (string) $result]);
    }

    /** @return array{0:string,1:object} */

    protected static function resolve(string $class, string $method): array
    {
        $fqcn = self::$controlNamespace . $class;
        if (!class_exists($fqcn)) {
            throw new \RuntimeException("Página não encontrada: {$class}");
        }
        self::assertDispatchableMethod($fqcn, $method, $class);
        $controller = new $fqcn;
        return [$fqcn, $controller];
    }

    /**
     * Garante que apenas handlers de evento públicos podem ser chamados pela URL.
     */
    protected static function assertDispatchableMethod(string $fqcn, string $method, string $class): void
    {
        if (!method_exists($fqcn, $method)) {
            throw new \RuntimeException("Evento {$method} não existe em {$class}");
        }
        $reflection = new \ReflectionMethod($fqcn, $method);
        if (!$reflection->isPublic() || !preg_match('/^on[A-Z][A-Za-z0-9_]*$/', $method)) {
            throw new \RuntimeException("Evento {$method} não é permitido em {$class}", 403);
        }
    }

    /** Layout base com Bootstrap + helper AJAX */

    protected static function template(string $content, string $active = ''): string
    {
        if (self::$layoutRenderer !== null) {
            return (self::$layoutRenderer)($content, $active);
        }
        $title = htmlspecialchars(self::$title, ENT_QUOTES);
        $menu = self::$menuHtml ?? self::menu($active);
        $scripts = TScript::render();
        $assetCss = TAssets::renderCss();
        $assetJs = TAssets::renderJs();
        return <<<HTML
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{$title}</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
{$assetCss}
<style>
body{background:#f1f3f5}.aurea-page{padding:8px}.card{box-shadow:0 1px 3px rgba(0,0,0,.08)}
.table th a{color:inherit;text-decoration:none}
</style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
  <div class="container-fluid">
    <a class="navbar-brand" href="index.php"><i class="fa-solid fa-layer-group"></i> {$title}</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav"><span class="navbar-toggler-icon"></span></button>
    <div class="collapse navbar-collapse" id="nav"><ul class="navbar-nav me-auto">{$menu}</ul></div>
  </div>
</nav>
<main id="aurea-content" class="container pb-5">{$content}</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
{$assetJs}
<script>
function __aureaAjax(url, target){
  target = target || '#aurea-content';
  fetch(url + (url.indexOf('?')>=0?'&':'?') + 'ajax=1', {headers:{'X-Requested-With':'XMLHttpRequest'}})
    .then(r=>r.text()).then(html=>{document.querySelector(target).innerHTML = html; window.scrollTo(0,0);});
}
function __aureaPost(url, formId, target){
  var form = document.getElementById(formId);
  var data = new FormData(form);
  fetch(url + '&ajax=1', {method:'POST', body:data, headers:{'X-Requested-With':'XMLHttpRequest'}})
    .then(r=>r.text()).then(html=>{document.querySelector(target||'#aurea-content').innerHTML = html;});
  return false;
}
</script>
{$scripts}
</body>
</html>
HTML;
    }

    /**
     * Monta o menu padrão de navegação.
     *
     * @param string $active Classe ativa
     * @return string HTML do menu
     */
    protected static function menu(string $active): string
    {
        $items = [
            'HomeView'    => ['Início', 'fa-house'],
            'ClienteForm' => ['Novo Cliente', 'fa-plus'],
            'ClienteList' => ['Clientes', 'fa-users'],
        ];
        $out = '';
        foreach ($items as $class => [$label, $icon]) {
            $a = $class === $active ? ' active' : '';
            $out .= "<li class='nav-item'><a class='nav-link{$a}' href='index.php?class={$class}&method=onShow'>"
                  . "<i class='fa-solid {$icon}'></i> {$label}</a></li>";
        }
        return $out;
    }
}
