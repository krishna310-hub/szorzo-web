<?php

namespace App\Policies;

use App\Models\User;

class PagesPolicy
{
    public function read(User $user)
    {
        return $this->getPermission($user, 15);
    }
    public function create(User $user)
    {
        return $this->getPermission($user, 16);
    }
    public function edit(User $user)
    {
        return $this->getPermission($user, 17);
    }
    public function delete(User $user)
    {
        return $this->getPermission($user, 18);
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
