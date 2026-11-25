<?php

namespace App\Policies;

use App\Models\Permission;
use App\Models\User;

class PayslipPolicy
{
    public function read(User $user): bool
    {
        return $this->allowed($user, 'Read');
    }

    public function create(User $user): bool
    {
        return $this->allowed($user, 'Create');
    }

    public function edit(User $user): bool
    {
        return $this->allowed($user, 'Edit');
    }

    public function update(User $user): bool
    {
        return $this->edit($user);
    }

    public function delete(User $user): bool
    {
        return $this->allowed($user, 'Delete');
    }

    public function download(User $user): bool
    {
        return $this->allowed($user, 'Download') || $this->allowed($user, 'Export') || $this->allowed($user, 'Read');
    }

    public function export(User $user): bool
    {
        return $this->download($user);
    }

    public function view(User $user): bool
    {
        return $this->read($user);
    }

    public function before(User $user, ?string $ability = null): ?bool
    {
        try {
            if ($user->isSuperAdmin() || (int) $user->id === 1 || $user->isAdmin()) {
                return true;
            }

            if ($user->isInvestor() || (int) $user->role_id === 6) {
                return false;
            }
        } catch (\Throwable) {
            return null;
        }

        return null;
    }

    private function allowed(User $user, string $name): bool
    {
        try {
            if (! Permission::getConnectionResolver()) {
                return false;
            }

            if (! $user->role) {
                return false;
            }

            $id = Permission::where('page', 'payslip')->where('name', $name)->value('id');

            return (bool) ($id && $user->role->permissions->contains('id', $id));
        } catch (\Throwable) {
            return false;
        }
    }
}
