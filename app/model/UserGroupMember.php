<?php
declare(strict_types=1);

namespace App\Model;

use Aurea\Database\TRecord;

class UserGroupMember extends TRecord
{
    public const TABLENAME = 'user_group_member';
    public const PRIMARYKEY = 'id';
    public const IDPOLICY = 'serial';
    public const ATTRIBUTES = ['group_id', 'user_id'];

    public function __construct(array|int|string|null $data = null, bool $callObjectLoad = true)
    {
        foreach (self::ATTRIBUTES as $attribute) {
            $this->addAttribute($attribute);
        }
        parent::__construct($data, $callObjectLoad);
    }
}
