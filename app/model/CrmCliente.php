<?php
declare(strict_types=1);

namespace App\Model;

use Aurea\Database\TRecord;

/**
 * Cliente da demonstração CRM.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class CrmCliente extends TRecord
{
    const TABLENAME = 'crm_cliente';
    const PRIMARYKEY = 'id';
    const IDPOLICY = 'serial';
    const CREATED_AT = 'created_at';
    const ATTRIBUTES = ['nome', 'empresa', 'email', 'telefone', 'cidade', 'status', 'created_at'];

    public function __construct(array|int|string|null $data = null, bool $callObjectLoad = true)
    {
        foreach (self::ATTRIBUTES as $field) {
            parent::addAttribute($field);
        }
        parent::__construct($data, $callObjectLoad);
    }
}
