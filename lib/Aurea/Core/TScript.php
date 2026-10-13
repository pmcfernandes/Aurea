<?php
declare(strict_types=1);

namespace Aurea\Core;

/**
 * TScript — injeta JavaScript a partir dos controllers.
 *
 *   TScript::create("alert('olá')");
 *   TScript::redirect(new TAction(['ClienteList', 'onReload']));
 *
 * Os scripts acumulados são impressos no fim da página pelo TApplication
 * (inclusive em respostas AJAX).
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TScript
{
    protected static array $scripts = [];

    /**
     * Enfileira um JavaScript para impressão no fim da página.
     *
     * @param string $js Código JavaScript
     */
    public static function create(string $js): void
    {
        self::$scripts[] = $js;
    }

    /**
     * Recarrega a página atual.
     */
    public static function reload(): void
    {
        self::create('window.location.reload();');
    }

    /**
     * Redireciona para o evento informado.
     *
     * @param TAction $action Evento de destino
     */
    public static function redirect(TAction $action): void
    {
        self::create("window.location='" . $action->serialize() . "';");
    }

    /**
     * Fecha o modal informado.
     *
     * @param string $id Id do modal
     */
    public static function closeModal(string $id = 'aurea-modal'): void
    {
        self::create("var m=document.getElementById('{$id}');if(m&&window.bootstrap){bootstrap.Modal.getInstance(m)?.hide();}");
    }

    /**
     * Exibe o modal informado.
     *
     * @param string $id Id do modal
     */
    public static function showModal(string $id): void
    {
        self::create("var m=document.getElementById('{$id}');if(m&&window.bootstrap){new bootstrap.Modal(m).show();}");
    }

    /** Devolve <script> com tudo o que foi acumulado e limpa a fila */

    public static function render(): string
    {
        if (!self::$scripts) {
            return '';
        }
        $out = "<script>\n" . implode("\n", self::$scripts) . "\n</script>";
        self::$scripts = [];
        return $out;
    }

    /**
     * Indica se há scripts pendentes de impressão.
     *
     * @return bool True se há scripts na fila
     */
    public static function hasPending(): bool
    {
        return (bool) self::$scripts;
    }

    /**
     * Limpa a fila de scripts pendentes.
     */
    public static function clear(): void
    {
        self::$scripts = [];
    }
}
