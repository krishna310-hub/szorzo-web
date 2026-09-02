<?php

namespace App\Policies;

use App\Models\Permission;
use App\Models\User;

class ContractReportPolicy
{
    public function read(User $user): bool
    {
        return $this->hasPermission($user, 'Read');
    }

    public function export(User $user): bool
    {
        return $this->hasPermission($user, 'Export');
    }

    public function before(User $user): ?bool
    {
        return $user->isSuperAdmin() ? true : null;
    }

    private function hasPermission(User $user, string $name): bool
    {
        if (! $user->role) {
            return false;
        }

        $permissionId = Permission::where('page', 'contract_report')
            ->where('name', $name)
            ->value('id');

        return $permissionId
            ? $user->role->permissions->contains('id', $permissionId)
            : false;
    }
}
