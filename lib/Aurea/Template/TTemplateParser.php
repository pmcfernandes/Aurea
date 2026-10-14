<?php
declare(strict_types=1);

namespace Aurea\Template;

/**
 * TTemplateParser — mini-motor de templates com variáveis, loops e ifs.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TTemplateParser
{
    /** @var array<string,string> Secções registadas para herança de layouts */
    protected array $sections = [];
    /** @var array<string,string> Templates já renderizados por conteúdo e dados. */
    protected array $cache = [];
    protected bool $cacheEnabled = false;

    /** Ativa cache em memória por instância para renderizações idênticas. */
    public function setCacheEnabled(bool $enabled = true): static
    {
        $this->cacheEnabled = $enabled;
        if (!$enabled) {
            $this->cache = [];
        }
        return $this;
    }

    /**
     * Regista o conteúdo de uma secção para o layout.
     *
     * @param string $name Nome da secção
     * @param string $content Conteúdo da secção
     * @return static
     */
    public function section(string $name, string $content): static
    {
        $this->sections[$name] = $content;
        return $this;
    }

    /**
     * Interpreta uma vista e aplica-a ao layout informado.
     *
     * @param string $template Texto da vista
     * @param array $data Dados para substituição
     * @param string|null $layout Texto do layout com blocos {block:NAME}
     * @return string Vista interpretada, com ou sem layout
     */
    public function render(string $template, array $data = [], ?string $layout = null): string
    {
        $view = $this->parse($template, $data);
        if ($layout === null) {
            return $view;
        }
        $sections = $this->sections;
        if (!isset($sections['content'])) {
            $sections['content'] = $view;
        }
        $merged = (string) preg_replace_callback(
            '/\{block:(\w+)\}/',
            function (array $m) use ($sections): string {
                return $sections[$m[1]] ?? '';
            },
            $layout
        );
        $this->sections = [];
        return $this->parse($merged, $data);
    }

    /**
     * Interpreta um template com os dados fornecidos.
     *
     * @param string $template Texto do template
     * @param array $data Dados para substituição
     * @return string Template interpretado
     */
    public function parse(string $template, array $data): string
    {
        $key = hash('sha256', $template . serialize($data));
        if ($this->cacheEnabled && isset($this->cache[$key])) {
            return $this->cache[$key];
        }
        $template = $this->parseLoops($template, $data);
        $template = $this->parseIfs($template, $data);
        $template = $this->parseVars($template, $data);
        if ($this->cacheEnabled) {
            $this->cache[$key] = $template;
        }
        return $template;
    }

    /**
     * Interpreta os blocos de repetição do template.
     *
     * @param string $template Texto do template
     * @param array $data Dados para substituição
     * @return string Template com os loops expandidos
     */
    protected function parseLoops(string $template, array $data): string
    {
        $pattern = '/<!--\[loop\s+(\w+)\]-->(.*?)<!--\[\/loop\s+\1\]-->/s';
        return (string) preg_replace_callback(
            $pattern,
            function (array $m) use ($data): string {
                $name = $m[1];
                $inner = $m[2];
                $items = $data[$name] ?? [];
                if (!is_array($items)) {
                    return '';
                }
                $out = '';
                foreach ($items as $item) {
                    $rendered = (string) preg_replace_callback(
                        '/\{' . preg_quote($name, '/') . '->(\w+)(!?)\}/',
                        function (array $mm) use ($item): string {
                            $value = $this->itemValue($item, $mm[1]);
                            return $this->stringify($value, $mm[2] === '!');
                        },
                        $inner
                    );
                    $rendered = $this->parse($rendered, $data);
                    $out .= $rendered;
                }
                return $out;
            },
            $template
        );
    }

    /**
     * Interpreta os blocos condicionais do template.
     *
     * @param string $template Texto do template
     * @param array $data Dados para substituição
     * @return string Template com os ifs resolvidos
     */
    protected function parseIfs(string $template, array $data): string
    {
        $pattern = '/\{if\s+(\w+(?:->\w+)?)\}(.*?)\{endif\}/s';
        return (string) preg_replace_callback(
            $pattern,
            function (array $m) use ($data): string {
                $value = $this->resolve($m[1], $data);
                if (!empty($value)) {
                    return $this->parse($m[2], $data);
                }
                return '';
            },
            $template
        );
    }

    /**
     * Substitui as variáveis simples do template.
     *
     * @param string $template Texto do template
     * @param array $data Dados para substituição
     * @return string Template com as variáveis substituídas
     */
    protected function parseVars(string $template, array $data): string
    {
        $pattern = '/\{(\w+(?:->\w+)?)(!?)\}/';
        return (string) preg_replace_callback(
            $pattern,
            function (array $m) use ($data): string {
                $value = $this->resolve($m[1], $data);
                return $this->stringify($value, $m[2] === '!');
            },
            $template
        );
    }

    /**
     * Resolve uma expressão (var ou obj->attr) nos dados.
     *
     * @param string $expr Expressão a resolver
     * @param array $data Dados para substituição
     * @return mixed Valor resolvido ou null
     */
    protected function resolve(string $expr, array $data)
    {
        if (str_contains($expr, '->')) {
            [$base, $attr] = explode('->', $expr, 2);
            $container = $data[$base] ?? null;
            return $this->itemValue($container, $attr);
        }
        return $data[$expr] ?? null;
    }

    /**
     * Obtém um atributo de um item (array ou objecto).
     *
     * @param mixed $item Item do loop ou contentor
     * @param string $attr Nome do atributo
     * @return mixed Valor do atributo ou null
     */
    protected function itemValue($item, string $attr)
    {
        if (is_array($item)) {
            return $item[$attr] ?? null;
        }
        if (is_object($item)) {
            return $item->{$attr} ?? null;
        }
        return null;
    }

    /**
     * Converte um valor para string (com ou sem escape HTML).
     *
     * @param mixed $value Valor a converter
     * @param bool $raw Verdadeiro para devolver sem escape
     * @return string Valor em string (vazio se inexistente)
     */
    protected function stringify($value, bool $raw): string
    {
        if ($value === null) {
            return '';
        }
        if (is_array($value) || (is_object($value) && !method_exists($value, '__toString'))) {
            return '';
        }
        if (is_object($value)) {
            $value = (string) $value;
        } elseif (is_bool($value)) {
            $value = $value ? '1' : '';
        } else {
            $value = (string) $value;
        }
        if ($raw) {
            return $value;
        }
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
