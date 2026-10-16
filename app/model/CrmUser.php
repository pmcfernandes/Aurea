<?php
declare(strict_types=1);

namespace App\Model;

use Aurea\Auth\THash;
use Aurea\Database\TRecord;

/** Utilizador autenticável do CRM. */
class CrmUser extends TRecord
{
    public const TABLENAME = 'crm_user';
    public const PRIMARYKEY = 'id';
    public const IDPOLICY = 'serial';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';
    public const ATTRIBUTES = ['nome', 'email', 'password_hash', 'created_at', 'updated_at'];

    public function __construct(array|int|string|null $data = null, bool $callObjectLoad = true)
    {
        foreach (self::ATTRIBUTES as $attribute) {
            $this->addAttribute($attribute);
        }
        parent::__construct($data, $callObjectLoad);
    }

    /** Localiza um utilizador pelo e-mail normalizado. */
    public static function findByEmail(string $email): ?self
    {
        $user = self::where('email', '=', strtolower(trim($email)))->first();
        return $user instanceof self ? $user : null;
    }

    /** Confirma a palavra-passe com o hash BCrypt guardado. */
    public function verifiesPassword(string $password): bool
    {
        return THash::verify($password, (string) $this->password_hash);
    }

    /** Atualiza o hash BCrypt quando a política de segurança o exige. */
    public function refreshPasswordHash(string $password): void
    {
        if (!THash::needsRehash((string) $this->password_hash)) {
            return;
        }

        $this->password_hash = THash::make($password);
        $this->store();
    }
}
