<?php
namespace App\Policies;
use App\Models\Permission;
use App\Models\User;
class AttendancePolicy {
    public function read(User $user): bool { return $this->allowed($user, 'Read'); }
    public function create(User $user): bool { return $this->allowed($user, 'Create'); }
    public function edit(User $user): bool { return $this->allowed($user, 'Edit'); }
    public function export(User $user): bool { return $this->allowed($user, 'Export'); }
    public function before(User $user): ?bool { return $user->isSuperAdmin() ? true : null; }
    private function allowed(User $user, string $name): bool { $id = Permission::where('page', 'attendance')->where('name', $name)->value('id'); return (bool) ($id && $user->role?->permissions->contains('id', $id)); }
}
