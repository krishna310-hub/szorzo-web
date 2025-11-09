<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Create a new policy instance.
     */
    public function read(User $user)
    {
        return $this->getPermission($user, 8);
    }
    public function create(User $user)
    {
        return $this->getPermission($user, 9);
    }
    public function edit(User $user)
    {
        return $this->getPermission($user, 10);
    }
    public function delete(User $user)
    {
        return $this->getPermission($user, 11);
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
