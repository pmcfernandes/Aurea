<?php
declare(strict_types=1);

namespace App\Lib;

use App\Model\UserGroupMember;
use App\Model\UserGroupPermission;
use Aurea\Database\TTransaction;

/** Verifica permissões de controlador e ação atribuídas por grupo. */
final class AccessControl
{
    public static function allows(?string $userId, string $controller, string $action): bool
    {
        if ($userId === null || !ctype_digit($userId)) {
            return false;
        }

        try {
            TTransaction::open('app');
            $memberships = UserGroupMember::where('user_id', '=', (int) $userId)->get();
            foreach ($memberships as $membership) {
                $permissions = UserGroupPermission::where('group_id', '=', (int) $membership->group_id)->get();
                foreach ($permissions as $permission) {
                    $controllerMatches = $permission->controller === '*' || $permission->controller === $controller;
                    $actionMatches = $permission->action === '*' || $permission->action === $action;
                    if ($controllerMatches && $actionMatches) {
                        TTransaction::close();
                        return true;
                    }
                }
            }
            TTransaction::close();
        } catch (\Throwable) {
            TTransaction::rollback();
        }

        return false;
    }
}
