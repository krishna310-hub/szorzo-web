<?php

namespace App\Policies;

use App\Models\LeaveRequest;
use App\Models\Permission;
use App\Models\User;

class LeaveRequestPolicy
{
    public function read(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }
        if ($user->isInternalEmployee()) {
            return true;
        }
        return $this->allowed($user, 'Read');
    }

    public function create(User $user): bool
    {
        if ($user->isSuperAdmin() || $user->isInvestor()) {
            return false;
        }
        // Strictly internal employees only
        return $user->isInternalEmployee();
    }

    public function approve(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }
        return $this->allowed($user, 'Approve');
    }

    public function view(User $user, LeaveRequest $leave): bool
    {
        return $leave->user_id === $user->id || $this->approve($user);
    }

    public function before(User $user, string $ability): ?bool
    {
        // Super admin should not create employee leave requests for self
        if ($ability === 'create') {
            return false;
        }
        if ($user->isSuperAdmin()) {
            return true;
        }
        return null;
    }

    private function allowed(User $user, string $name): bool
    {
        $id = Permission::where('page', 'leave')->where('name', $name)->value('id');
        return (bool) ($id && $user->role?->permissions->contains('id', $id));
    }
}
