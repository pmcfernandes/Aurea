<?php
declare(strict_types=1);

namespace Aurea\Core;

/**
 * TPage — contentor de topo de uma tela (controller view).
 *
 * Controllers de página estendem TPage e implementam métodos de evento
 * (onShow, onReload, onSave, onEdit, onDelete, ...) que recebem $param.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TPage extends TComponent
{
    protected string $targetContainer = '#aurea-content';

    /**
     * Cria a página como contentor div.
     */
    public function __construct()
    {
        parent::__construct('div');
        $this->addClass('aurea-page');
    }

    /**
     * Define o contentor alvo das respostas AJAX.
     *
     * @param string $target Seletor do contentor alvo
     * @return static
     */
    public function setTargetContainer(string $target): static
    {
        $this->targetContainer = $target;
        return $this;
    }

    /**
     * Obtém o contentor alvo das respostas AJAX.
     *
     * @return string Seletor do contentor alvo
     */
    public function getTargetContainer(): string
    {
        return $this->targetContainer;
    }

    /** Chamado pelo Front Controller após construir a página */

    public function onShow(array $param = []): void
    {
    }
}
