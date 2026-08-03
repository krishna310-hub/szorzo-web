<?php

namespace App\Policies;

use App\Models\Permission;
use App\Models\User;

class ReportPolicy
{
    public function read(User $user): bool
    {
        return $this->hasPermission($user, 'Read');
    }

    public function export(User $user): bool
    {
        return $this->hasPermission($user, 'Export');
    }

    private function hasPermission(User $user, string $name): bool
    {
        if (! $user->role) {
            return false;
        }

        $permissionId = Permission::where('page', 'reports')
            ->where('name', $name)
            ->value('id');

        return $permissionId
            ? $user->role->permissions->contains('id', $permissionId)
            : false;
    }

    public function before(User $user, string $ability): ?bool
    {
        return $user->isSuperAdmin() ? true : null;
    }
}
