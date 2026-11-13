<?php

namespace App\Policies;

use App\Models\Permission;
use App\Models\User;

class HolidayPolicy
{
    public function read(User $user): bool
    {
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return true;
        }
        return $this->allowed($user, 'Read');
    }

    public function create(User $user): bool
    {
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return true;
        }
        return $this->allowed($user, 'Create');
    }

    public function edit(User $user): bool
    {
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return true;
        }
        return $this->allowed($user, 'Edit');
    }

    public function delete(User $user): bool
    {
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return true;
        }
        return $this->allowed($user, 'Delete');
    }

    public function before(User $user): ?bool
    {
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return true;
        }
        return null;
    }

    private function allowed(User $user, string $name): bool
    {
        try {
            if (!Permission::getConnectionResolver()) {
                return false;
            }
            $id = Permission::where('page', 'holiday')->where('name', $name)->value('id');
            return (bool) ($id && $user->role?->permissions->contains('id', $id));
        } catch (\Throwable) {
            return false;
        }
    }
}
