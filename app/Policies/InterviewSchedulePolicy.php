<?php

namespace App\Policies;

use App\Models\Permission;
use App\Models\User;

class InterviewSchedulePolicy
{
    public function read(User $user): bool
    {
        return $this->getPermission($user, 'Read');
    }

    public function create(User $user): bool
    {
        return $this->getPermission($user, 'Create');
    }

    public function edit(User $user): bool
    {
        return $this->getPermission($user, 'Edit');
    }

    public function delete(User $user): bool
    {
        return $this->getPermission($user, 'Delete');
    }

    public function viewAny(User $user): bool
    {
        return $this->read($user);
    }

    public function view(User $user): bool
    {
        return $this->read($user);
    }

    public function update(User $user): bool
    {
        return $this->edit($user);
    }

    private function getPermission(User $user, string $name): bool
    {
        if (! $user->role) {
            return false;
        }

        $permissionId = Permission::where('page', 'interview_schedule')->where('name', $name)->value('id');

        return $permissionId ? $user->role->permissions->contains('id', $permissionId) : false;
    }

    public function before(User $user, $ability): ?bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return null;
    }
}
