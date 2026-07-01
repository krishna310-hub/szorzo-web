<?php

namespace App\Policies;

use App\Models\Permission;
use App\Models\User;

class DivisionPolicy
{
    public function read(User $user): bool { return $this->getPermission($user, 'Read'); }
    public function create(User $user): bool { return $this->getPermission($user, 'Create'); }
    public function edit(User $user): bool { return $this->getPermission($user, 'Edit'); }
    public function delete(User $user): bool { return $this->getPermission($user, 'Delete'); }

    private function getPermission(User $user, string $name): bool
    {
        if (!$user->role) {
            return false;
        }

        $permissionId = Permission::where('page', 'division')->where('name', $name)->value('id');
        return $permissionId ? $user->role->permissions->contains('id', $permissionId) : false;
    }

    public function before($user, $ability)
    {
        return $user->isSuperAdmin() ? true : null;
    }
}
