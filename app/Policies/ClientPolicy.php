<?php

namespace App\Policies;

use App\Models\Permission;
use App\Models\User;

class ClientPolicy
{
    public function read(User $user)
    {
        return $this->getPermission($user, 'Read');
    }

    public function create(User $user)
    {
        return $this->getPermission($user, 'Create');
    }

    public function edit(User $user)
    {
        return $this->getPermission($user, 'Edit');
    }

    public function delete(User $user)
    {
        return $this->getPermission($user, 'Delete');
    }

    private function getPermission(User $user, string $name): bool
    {
        if (!$user->role) {
            return false;
        }

        $permissionId = Permission::where('page', 'client')->where('name', $name)->value('id');

        return $permissionId
            ? $user->role->permissions->contains('id', $permissionId)
            : false;
    }

    public function before($user, $ability)
    {
        if ($user->isSuperAdmin()) {
            return true;
        }
    }
}
