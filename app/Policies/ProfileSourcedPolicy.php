<?php

namespace App\Policies;

use App\Models\Permission;
use App\Models\User;

class ProfileSourcedPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isSuperAdmin() ? true : null;
    }

    public function read(User $user): bool { return $this->allowed($user, 'Read'); }
    public function create(User $user): bool { return $this->allowed($user, 'Create'); }
    public function edit(User $user): bool { return $this->allowed($user, 'Edit'); }
    public function delete(User $user): bool { return $this->allowed($user, 'Delete'); }

    private function allowed(User $user, string $ability): bool
    {
        $id = Permission::where('page', 'profile_sourced')->where('name', $ability)->value('id');

        return (bool) ($id && $user->role?->permissions->contains('id', $id));
    }
}
