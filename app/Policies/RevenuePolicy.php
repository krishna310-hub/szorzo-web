<?php

namespace App\Policies;

use App\Models\Permission;
use App\Models\User;

class RevenuePolicy
{
    public function read(User $user): bool { return $this->allowed($user, 'Read'); }
    public function create(User $user): bool { return $this->allowed($user, 'Create'); }
    public function edit(User $user): bool { return $this->allowed($user, 'Edit'); }
    public function download(User $user): bool { return $this->allowed($user, 'Download'); }

    public function before(User $user): ?bool
    {
        return $user->isSuperAdmin() ? true : null;
    }

    private function allowed(User $user, string $name): bool
    {
        $permissionId = Permission::where('page', 'revenue')->where('name', $name)->value('id');

        return (bool) ($permissionId && $user->role?->permissions->contains('id', $permissionId));
    }
}
