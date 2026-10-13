<?php
declare(strict_types=1);

namespace Aurea\Container;

use Aurea\Core\TComponent;


/**
 * Linha de tabela HTML (tr).
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TTableRow extends TComponent
{
    /**
     * Cria uma linha de tabela (tr).
     */
    public function __construct()
    {
        parent::__construct('tr');
    }

    /**
     * Adiciona uma célula com conteúdo à linha.
     *
     * @param mixed $content Componente ou texto da célula
     * @return TTableCell Célula criada
     */
    public function addCell($content): TTableCell
    {
        $cell = new TTableCell;
        $cell->add($content);
        $this->add($cell);
        return $cell;
    }
}
