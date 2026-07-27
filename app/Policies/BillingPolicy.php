<?php

namespace App\Policies;

use App\Models\Permission;
use App\Models\User;

class BillingPolicy
{
    public function read(User $user)
    {
        return $this->getPermission($user, 'Read');
    }

    public function create(User $user)
    {
        return $this->getPermission($user, 'Create');
    }

    public function edit(User $user)
    {
        return $this->getPermission($user, 'Edit');
    }

    public function delete(User $user)
    {
        return $this->getPermission($user, 'Delete');
    }

    private function getPermission(User $user, string $name): bool
    {
        if (!$user->role) {
            return false;
        }

        $permissionId = Permission::where('page', 'billing')->where('name', $name)->value('id');

        return $permissionId
            ? $user->role->permissions->contains('id', $permissionId)
            : false;
    }

    /**
     * Billing and invoice data is restricted to the primary administrator.
     *
     * Returning false here is intentional: it prevents other users from
     * accessing the module even if their role contains billing permissions.
     */
    public function before(User $user, string $ability): bool
    {
        return (int) $user->id === 1;
    }
}
