<?php
declare(strict_types=1);

namespace App\Control;

use App\Model\CrmUser;
use App\Model\UserGroup;
use App\Model\UserGroupMember;
use App\Model\UserGroupPermission;
use Aurea\Auth\TAuth;
use Aurea\Auth\THash;
use Aurea\Core\TAction;
use Aurea\Core\TAssets;
use Aurea\Core\TCsrf;
use Aurea\Core\TPage;
use Aurea\Core\TScript;
use Aurea\Database\TTransaction;
use Aurea\Mvc\TView;

/** Administração de utilizadores e grupos de acesso. */
class AccessManagement extends TPage
{
    public function __construct()
    {
        parent::__construct();
        TAssets::addCss('/assets/access-management.css');
    }

    public function onShow(array $param = []): void
    {
        $data = [
            'users' => [],
            'groups' => [],
            'permissions' => $this->permissionOptions(),
            'selectedUser' => null,
            'selectedGroup' => null,
            'selectedUserGroups' => [],
            'selectedGroupPermissions' => [],
            'csrf' => TCsrf::token(),
            'section' => in_array($param['section'] ?? '', ['users', 'groups', 'permissions'], true) ? $param['section'] : 'users',
        ];
        try {
            TTransaction::open('app');
            $data['users'] = CrmUser::where('id', '>', 0)->orderBy('nome')->get();
            $data['groups'] = UserGroup::where('id', '>', 0)->orderBy('name')->get();
            $userId = $this->id($param['user_id'] ?? null);
            $groupId = $this->id($param['group_id'] ?? null);
            if ($userId > 0) {
                $data['selectedUser'] = new CrmUser($userId);
                $data['selectedUserGroups'] = array_map(
                    static fn(UserGroupMember $member): string => (string) $member->group_id,
                    UserGroupMember::where('user_id', '=', $userId)->get()
                );
            }
            if ($groupId > 0) {
                $data['selectedGroup'] = new UserGroup($groupId);
                $data['selectedGroupPermissions'] = array_map(
                    static fn(UserGroupPermission $permission): string => $permission->controller . '::' . $permission->action,
                    UserGroupPermission::where('group_id', '=', $groupId)->get()
                );
            }
            TTransaction::close();
        } catch (\Throwable) {
            TTransaction::rollback();
        }
        $this->addHtml((new TView('admin/access-management'))->set('data', $data)->fetch());
    }

    public function onUsers(array $param = []): void
    {
        $param['section'] = 'users';
        $this->onShow($param);
    }

    public function onUserForm(array $param = []): void
    {
        $param['section'] = 'users-form';
        $this->onShow($param);
    }

    public function onGroups(array $param = []): void
    {
        $param['section'] = 'groups';
        $this->onShow($param);
    }

    public function onGroupForm(array $param = []): void
    {
        $param['section'] = 'groups-form';
        $this->onShow($param);
    }

    public function onPermissions(array $param = []): void
    {
        $param['section'] = 'permissions';
        $this->onShow($param);
    }

    public function onPermissionForm(array $param = []): void
    {
        $param['section'] = 'permissions-form';
        $this->onShow($param);
    }

    public function onSaveUser(array $param = []): void
    {
        $data = $_POST + $param;
        $id = $this->id($data['id'] ?? null);
        $name = trim((string) ($data['nome'] ?? ''));
        $email = strtolower(trim((string) ($data['email'] ?? '')));
        $password = (string) ($data['password'] ?? '');
        $targetId = $id;
        if ($name === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false || ($id === 0 && $password === '')) {
            return;
        }
        try {
            TTransaction::open('app');
            $user = $id > 0 ? new CrmUser($id) : new CrmUser();
            $user->nome = $name;
            $user->email = $email;
            if ($password !== '') {
                $user->password_hash = THash::make($password);
            }
            $user->store();
            $targetId = (int) $user->id;
            $this->syncUserGroups((int) $user->id, (array) ($data['groups'] ?? []));
            TTransaction::close();
        } catch (\Throwable) {
            TTransaction::rollback();
        }
        TScript::redirect(new TAction([$this, 'onShow'], ['user_id' => $targetId]));
    }

    public function onDeleteUser(array $param = []): void
    {
        $id = $this->id($param['key'] ?? null);
        if ($id < 1 || (string) $id === TAuth::id()) {
            return;
        }
        try {
            TTransaction::open('app');
            (new CrmUser($id))->delete();
            TTransaction::close();
        } catch (\Throwable) {
            TTransaction::rollback();
        }
        TScript::redirect(new TAction([$this, 'onShow']));
    }

    public function onSaveGroup(array $param = []): void
    {
        $data = $_POST + $param;
        $id = $this->id($data['id'] ?? null);
        $name = trim((string) ($data['name'] ?? ''));
        $targetId = $id;
        if ($name === '') {
            return;
        }
        try {
            TTransaction::open('app');
            $group = $id > 0 ? new UserGroup($id) : new UserGroup();
            $group->name = $name;
            $group->description = trim((string) ($data['description'] ?? '')) ?: null;
            $group->store();
            $targetId = (int) $group->id;
            foreach (UserGroupPermission::where('group_id', '=', (int) $group->id)->get() as $permission) {
                $permission->delete();
            }
            foreach ((array) ($data['permissions'] ?? []) as $resource) {
                if (!isset($this->permissionOptions()[$resource])) {
                    continue;
                }
                [$controller, $action] = explode('::', $resource, 2);
                (new UserGroupPermission(['group_id' => $group->id, 'controller' => $controller, 'action' => $action]))->store();
            }
            TTransaction::close();
        } catch (\Throwable) {
            TTransaction::rollback();
        }
        TScript::redirect(new TAction([$this, 'onShow'], ['group_id' => $targetId]));
    }

    public function onDeleteGroup(array $param = []): void
    {
        $id = $this->id($param['key'] ?? null);
        if ($id < 1 || $id === 1) {
            return;
        }
        try {
            TTransaction::open('app');
            (new UserGroup($id))->delete();
            TTransaction::close();
        } catch (\Throwable) {
            TTransaction::rollback();
        }
        TScript::redirect(new TAction([$this, 'onShow']));
    }

    private function syncUserGroups(int $userId, array $groupIds): void
    {
        foreach (UserGroupMember::where('user_id', '=', $userId)->get() as $member) {
            $member->delete();
        }
        foreach (array_unique(array_filter($groupIds, 'ctype_digit')) as $groupId) {
            (new UserGroupMember(['user_id' => $userId, 'group_id' => (int) $groupId]))->store();
        }
    }

    /** @return array<string,string> */
    private function permissionOptions(): array
    {
        $config = require APP_ROOT . '/config/application.php';
        $options = [];
        foreach ($config['permissions'] as $controller => $actions) {
            foreach ($actions as $action) {
                $options["{$controller}::{$action}"] = "{$controller} · {$action}";
            }
        }
        return $options;
    }

    private function id(mixed $value): int
    {
        return is_scalar($value) && ctype_digit((string) $value) ? (int) $value : 0;
    }
}
