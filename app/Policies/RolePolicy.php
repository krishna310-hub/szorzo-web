<?php

namespace App\Policies;

use App\Models\User;

class RolePolicy
{
    /**
     * Create a new policy instance.
     */
    public function read(User $user)
    {
        return $this->getPermission($user, 4);
    }
    public function create(User $user)
    {
        return $this->getPermission($user, 5);
    }
    public function edit(User $user)
    {
        return $this->getPermission($user, 6);
    }
    public function delete(User $user)
    {
        return $this->getPermission($user, 7);
    }

    private function getPermission($user, $permission_id)
    {
        if ($user->role) {
            foreach ($user->role->permissions as $permission) {
                if ($permission->id == $permission_id)
                    return true;
            }
        }
        return false;
    }

    public function before($user, $ability)
    {
        if ($user->isSuperAdmin()) {
            return true;
        }
    }
}
