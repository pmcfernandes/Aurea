<?php
declare(strict_types=1);

namespace Aurea\Mvc;

/**
 * TView — vista MVC: variáveis + template PHP.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TView
{
    protected array $pageVars = [];
    protected TTemplate $template;
    protected string $file;

    /**
     * Cria a vista para um template.
     *
     * @param string $template Nome do template (ex: home/index)
     * @param string|null $basePath Diretório base das vistas (omissão = app/views/)
     */
    public function __construct(string $template, ?string $basePath = null)
    {
        $this->file = $template;
        $this->template = new TTemplate();
        $this->template->setPath($basePath ?? self::defaultBasePath());
    }

    /**
     * Diretório padrão das vistas (absoluto quando há bootstrap).
     *
     * @return string Caminho de app/views/
     */
    protected static function defaultBasePath(): string
    {
        return (defined('APP_ROOT') ? APP_ROOT . '/' : '') . 'app/views/';
    }

    /**
     * Define uma variável da vista.
     *
     * @param string $var Nome
     * @param mixed $val Valor
     * @return static
     */
    public function set(string $var, mixed $val): static
    {
        $this->pageVars[$var] = $val;
        return $this;
    }

    /**
     * Obtém uma variável da vista.
     *
     * @param string $var Nome
     * @return mixed Valor ou null
     */
    public function get(string $var): mixed
    {
        return $this->pageVars[$var] ?? null;
    }

    /**
     * Processa a vista e devolve o HTML.
     *
     * @return string HTML da vista
     */
    public function fetch(): string
    {
        $this->template->setVars($this->pageVars, true);
        return $this->template->fetch($this->file . '.php');
    }

    /**
     * Processa e imprime a vista.
     */
    public function render(): void
    {
        echo $this->fetch();
    }
}
