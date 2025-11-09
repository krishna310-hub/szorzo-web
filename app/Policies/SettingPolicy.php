<?php

namespace App\Policies;

use App\Models\User;

class SettingPolicy
{
    /**
     * Create a new policy instance.
     */
    public function generalSetting(User $user)
    {
        return $this->getPermission($user, 12);
    }
    public function emailSetting(User $user)
    {
        return $this->getPermission($user, 5);
    }
    public function socialSetting(User $user)
    {
        return $this->getPermission($user, 6);
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
