<?php
declare(strict_types=1);

namespace App\Model;

use Aurea\Database\TRecord;

class UserGroup extends TRecord
{
    public const TABLENAME = 'user_group';
    public const PRIMARYKEY = 'id';
    public const IDPOLICY = 'serial';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';
    public const ATTRIBUTES = ['name', 'description', 'created_at', 'updated_at'];

    public function __construct(array|int|string|null $data = null, bool $callObjectLoad = true)
    {
        foreach (self::ATTRIBUTES as $attribute) {
            $this->addAttribute($attribute);
        }
        parent::__construct($data, $callObjectLoad);
    }
}
