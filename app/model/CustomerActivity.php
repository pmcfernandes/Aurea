<?php
declare(strict_types=1);

namespace App\Model;

use Aurea\Database\TRecord;

/** Actividade associada a um cliente. */
class CustomerActivity extends TRecord
{
    public const TABLENAME = 'customer_activity';
    public const PRIMARYKEY = 'id';
    public const IDPOLICY = 'serial';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';
    public const ATTRIBUTES = ['customer_id', 'type', 'subject', 'description', 'scheduled_at', 'status', 'created_at', 'updated_at'];

    public function __construct(array|int|string|null $data = null, bool $callObjectLoad = true)
    {
        foreach (self::ATTRIBUTES as $attribute) {
            $this->addAttribute($attribute);
        }
        parent::__construct($data, $callObjectLoad);
    }

    public function customer(): ?Customer
    {
        $customer = $this->belongsTo(Customer::class, 'customer_id');
        return $customer instanceof Customer ? $customer : null;
    }
}
