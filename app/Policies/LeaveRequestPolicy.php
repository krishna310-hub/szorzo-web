<?php
namespace App\Policies;
use App\Models\LeaveRequest;
use App\Models\Permission;
use App\Models\User;
class LeaveRequestPolicy {
    public function read(User $user): bool { return $this->allowed($user, 'Read'); }
    public function create(User $user): bool { return ! $user->isSuperAdmin() && $this->allowed($user, 'Create'); }
    public function approve(User $user): bool { return $this->allowed($user, 'Approve'); }
    public function view(User $user, LeaveRequest $leave): bool { return $leave->user_id === $user->id || $this->approve($user); }
    public function before(User $user, string $ability): ?bool { return $ability === 'create' ? null : ($user->isSuperAdmin() ? true : null); }
    private function allowed(User $user, string $name): bool { $id = Permission::where('page', 'leave')->where('name', $name)->value('id'); return (bool) ($id && $user->role?->permissions->contains('id', $id)); }
}
