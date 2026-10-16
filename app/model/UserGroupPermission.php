<?php
declare(strict_types=1);

namespace App\Model;

use Aurea\Database\TRecord;

class UserGroupPermission extends TRecord
{
    public const TABLENAME = 'user_group_permission';
    public const PRIMARYKEY = 'id';
    public const IDPOLICY = 'serial';
    public const ATTRIBUTES = ['group_id', 'controller', 'action'];

    public function __construct(array|int|string|null $data = null, bool $callObjectLoad = true)
    {
        foreach (self::ATTRIBUTES as $attribute) {
            $this->addAttribute($attribute);
        }
        parent::__construct($data, $callObjectLoad);
    }
}
