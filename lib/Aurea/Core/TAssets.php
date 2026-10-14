<?php
declare(strict_types=1);

namespace Aurea\Core;

/**
 * TAssets — gere CSS/JS locais com versão (evita CDN obrigatório).
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TAssets
{
    /** @var string[] */
    protected static array $css = [];
    /** @var string[] */
    protected static array $js = [];
    protected static string $version = '1.0.0';
    protected static string $basePath = '';

    /**
     * Define a versão anexada aos assets (?v=...).
     *
     * @param string $version Versão dos assets
     */
    public static function setVersion(string $version): void
    {
        self::$version = $version;
    }

    /**
     * Define o prefixo base dos URLs dos assets.
     *
     * @param string $path Caminho base (ex.: /assets)
     */
    public static function setBasePath(string $path): void
    {
        self::$basePath = rtrim($path, '/');
    }

    /**
     * Regista uma folha de estilo (sem duplicados).
     *
     * @param string $href URL do CSS
     */
    public static function addCss(string $href): void
    {
        if (!in_array($href, self::$css, true)) {
            self::$css[] = $href;
        }
    }

    /**
     * Regista um script (sem duplicados).
     *
     * @param string $src URL do JS
     */
    public static function addJs(string $src): void
    {
        if (!in_array($src, self::$js, true)) {
            self::$js[] = $src;
        }
    }

    /**
     * Limpa todos os assets registados.
     */
    public static function reset(): void
    {
        self::$css = [];
        self::$js = [];
    }

    /**
     * Aplica base + versão a um URL de asset.
     *
     * @param string $url URL original do asset
     * @return string URL versionado
     */
    protected static function versioned(string $url): string
    {
        $sep = str_contains($url, '?') ? '&' : '?';
        return self::$basePath . $url . $sep . 'v=' . urlencode(self::$version);
    }

    /**
     * Renderiza as tags <link> dos CSS registados.
     *
     * @return string HTML com as folhas de estilo
     */
    public static function renderCss(): string
    {
        $out = '';
        foreach (self::$css as $href) {
            $out .= '<link rel="stylesheet" href="' . htmlspecialchars(self::versioned($href), ENT_QUOTES) . '">' . "\n";
        }
        return $out;
    }

    /**
     * Renderiza as tags <script> dos JS registados.
     *
     * @return string HTML com os scripts
     */
    public static function renderJs(): string
    {
        $out = '';
        foreach (self::$js as $src) {
            $out .= '<script src="' . htmlspecialchars(self::versioned($src), ENT_QUOTES) . '"></script>' . "\n";
        }
        return $out;
    }
}
