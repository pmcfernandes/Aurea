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
            http_response_code(500);
            if (TRequest::wantsJson()) {
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(['error' => $e->getMessage()]);
                return;
            }
            $msg = htmlspecialchars($e->getMessage(), ENT_QUOTES);
            $trace = htmlspecialchars($e->getTraceAsString(), ENT_QUOTES);
            if (TRequest::isAjax()) {
                echo "<div class='alert alert-danger'>{$msg}</div>";
                return;
            }
            echo self::template(
                "<div class='alert alert-danger'><strong>Erro:</strong> {$msg}</div>"
                . "<pre class='mt-3 small text-muted'>{$trace}</pre>",
                $class
            );
        }
    }

    /** Executa controller/método e devolve o HTML */

    public static function dispatch(string $class, string $method, array $param = []): string
    {
        $fqcn = self::$controlNamespace . $class;
        if (!class_exists($fqcn)) {
            throw new \RuntimeException("Página não encontrada: {$class}");
        }
        $controller = new $fqcn;
        if (!method_exists($controller, $method)) {
            throw new \RuntimeException("Evento {$method} não existe em {$class}");
        }

        ob_start();
        $ref = new \ReflectionMethod($fqcn, $method);
        $result = $ref->isStatic()
            ? $fqcn::{$method}($param)
            : $controller->{$method}($param);

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
        $result = $ref->isStatic()
            ? $fqcn::{$method}($param)
            : $controller->{$method}($param);
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
        $controller = new $fqcn;
        if (!method_exists($controller, $method)) {
            throw new \RuntimeException("Evento {$method} não existe em {$class}");
        }
        return [$fqcn, $controller];
    }

    /** Layout base com Bootstrap + helper AJAX */

    protected static function template(string $content, string $active = ''): string
    {
        $title = htmlspecialchars(self::$title, ENT_QUOTES);
        $menu = self::$menuHtml ?? self::menu($active);
        $scripts = TScript::render();
        return <<<HTML
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{$title}</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
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
