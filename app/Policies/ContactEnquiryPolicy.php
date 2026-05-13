<?php

namespace App\Policies;

use App\Models\User;

class ContactEnquiryPolicy
{
    public function read(User $user)
    {
        return $this->getPermission($user, 19);
    }
    public function edit(User $user)
    {
        return $this->getPermission($user, 20);
    }
    public function delete(User $user)
    {
        return $this->getPermission($user, 21);
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
