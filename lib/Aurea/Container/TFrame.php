<?php
declare(strict_types=1);

namespace Aurea\Container;

use Aurea\Core\TComponent;

/**
 * TFrame (fieldset + legend) que encaminha os filhos para o corpo.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TFrame extends TComponent
{
    protected TComponent $legendEl;
    protected TComponent $body;
    protected bool $built = false;

    /**
     * Cria o fieldset com a legenda informada.
     *
     * @param string $legend Texto da legenda
     */
    public function __construct(string $legend = '')
    {
        parent::__construct('fieldset');
        $this->legendEl = new TComponent('legend');
        if ($legend !== '') {
            $this->legendEl->add($legend);
        }
        parent::add($this->legendEl);
        $this->body = new TComponent('div');
        parent::add($this->body);
        $this->built = true;
    }

    /**
     * Define o texto da legenda do fieldset.
     *
     * @param string $legend Texto da legenda
     * @return static
     */
    public function setLegend(string $legend): static
    {
        $this->legendEl->setChildren([$legend]);
        return $this;
    }

    /**
     * Adiciona conteúdo ao corpo do fieldset.
     *
     * @param mixed $content Componente ou texto a adicionar
     * @return static
     */
    public function add($content): static
    {
        // após construído, filhos vão para o corpo (padrão TPanel)
        if ($this->built && $content !== $this->body && $content !== $this->legendEl) {
            $this->body->add($content);
            return $this;
        }
        return parent::add($content);
    }
}
