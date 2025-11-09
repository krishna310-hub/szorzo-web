<?php

namespace App\Policies;

use App\Models\User;

class GeneralPolicy
{
    public function dashboard(User $user)
    {
        return $this->getPermission($user,1);
    } 
    public function profileRead(User $user)
    {
        return $this->getPermission($user,2);
    } 
    public function profileEdit(User $user)
    {
        return $this->getPermission($user,3);
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
