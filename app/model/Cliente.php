<?php
declare(strict_types=1);

namespace App\Model;

use Aurea\Database\TRecord;

/**
 * Cliente — Active Record de exemplo.
 * Segue o padrão TRecord:
 * TABLENAME + PRIMARYKEY + IDPOLICY + atributos.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class Cliente extends TRecord
{
    const TABLENAME = 'cliente';
    const PRIMARYKEY = 'id';
    const IDPOLICY = 'serial';

    /**
     * Declara os atributos persistidos e inicializa o registro.
     *
     * @param array|int|null $data Linha do banco, id para carregar, ou null (vazio)
     * @param bool $callObjectLoad Se dispara o pós-carregamento
     */
    public function __construct(array|int|null $data = null, bool $callObjectLoad = true)
    {
        parent::addAttribute('nome');
        parent::addAttribute('email');
        parent::addAttribute('telefone');
        parent::addAttribute('cidade');
        parent::__construct($data, $callObjectLoad);
    }
}
