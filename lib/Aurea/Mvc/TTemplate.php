<?php
declare(strict_types=1);

namespace Aurea\Mvc;

/**
 * TTemplate — engine mínima de templates PHP (estilo Savant).
 * As variáveis ficam disponíveis no template via extract().
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TTemplate
{
    protected array $vars = [];
    protected string $path;

    /**
     * Cria o engine para um diretório de templates.
     *
     * @param string|null $path Caminho dos templates
     */
    public function __construct(?string $path = null)
    {
        $this->path = $path ?? '';
        $this->vars = [];
    }

    /**
     * Define o diretório dos templates.
     *
     * @param string $path Caminho
     * @return static
     */
    public function setPath(string $path): static
    {
        $this->path = $path;
        return $this;
    }

    /**
     * Atribui uma variável ao template.
     *
     * @param string $name Nome da variável
     * @param mixed $value Valor
     * @return static
     */
    public function assign(string $name, mixed $value): static
    {
        $this->vars[$name] = $value;
        return $this;
    }

    /**
     * Atribui várias variáveis de uma vez.
     *
     * @param array $vars Nome => valor
     * @param bool $clear Se substitui todas as existentes
     * @return static
     */
    public function setVars(array $vars, bool $clear = false): static
    {
        $this->vars = $clear ? $vars : array_merge($this->vars, $vars);
        return $this;
    }

    /**
     * Processa o template e devolve o HTML.
     *
     * @param string $file Ficheiro do template
     * @return string HTML processado
     * @throws \RuntimeException Se o ficheiro não existir
     */
    public function fetch(string $file): string
    {
        $full = $this->path . $file;
        if (!is_file($full)) {
            throw new \RuntimeException("Template não encontrado: {$full}");
        }
        extract($this->vars);
        ob_start();
        include $full;
        return (string) ob_get_clean();
    }

    /**
     * Processa e imprime o template.
     *
     * @param string $file Ficheiro do template
     */
    public function display(string $file): void
    {
        echo $this->fetch($file);
    }
}
