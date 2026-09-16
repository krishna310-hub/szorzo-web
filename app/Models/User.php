<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Builder;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'profile_picture',
        'cover_picture',
        'phone_number',
        'role_id',
        'is_active'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isSuperAdmin(): bool
    {
        if ($this->role) {
            $level = str_replace('_', '-', strtolower((string) $this->role->access_level));
            if (in_array($level, ['super-admin', 'admin'], true)) {
                return true;
            }
        }
        return false;
    }

    public function isAdmin(): bool
    {
        return $this->isSuperAdmin();
    }

    /**
     * Determine whether the user belongs to the Recruiter role group.
     */
    public function isRecruiter(): bool
    {
        if ($this->id === 1) {
            return false;
        }

        $accessLevel = str_replace(['_', ' '], '-', strtolower(trim((string) ($this->role?->access_level ?? ''))));
        $roleName = str_replace(['_', ' '], '-', strtolower(trim((string) ($this->role?->name ?? ''))));

        return str_contains($accessLevel, 'recruiter')
            || str_contains($accessLevel, 'delivery-lead')
            || str_contains($accessLevel, 'sourcing')
            || str_contains($roleName, 'recruiter')
            || str_contains($roleName, 'delivery lead')
            || str_contains($roleName, 'sourcing')
            || str_contains($roleName, 'rinos');
    }

    /**
     * Determine whether the user belongs to the Sales role group.
     */
    public function isSales(): bool
    {
        if ($this->id === 1) {
            return false;
        }

        $accessLevel = str_replace(['_', ' '], '-', strtolower(trim((string) ($this->role?->access_level ?? ''))));
        $roleName = str_replace(['_', ' '], '-', strtolower(trim((string) ($this->role?->name ?? ''))));

        return str_contains($accessLevel, 'sales')
            || str_contains($accessLevel, 'business-dev')
            || str_contains($accessLevel, 'bd')
            || str_contains($roleName, 'sales')
            || str_contains($roleName, 'business development')
            || str_contains($roleName, 'bd');
    }

    /**
     * Determine whether the user belongs to the Management / Super Admin / Subadmin role group.
     */
    public function isManagement(): bool
    {
        if ($this->id === 1 || $this->isSuperAdmin()) {
            return true;
        }

        if ($this->isRecruiter() || $this->isSales()) {
            return false;
        }

        return true;
    }

    /**
     * Resolve the designated login portal for this user: 'mgmt', 'rinos', or 'sales'.
     */
    public function getDesignatedPortal(): string
    {
        if ($this->isRecruiter()) {
            return 'rinos';
        }

        if ($this->isSales()) {
            return 'sales';
        }

        return 'mgmt';
    }

    /**
     * Check if user is allowed to log in via the given portal ('mgmt', 'rinos', 'sales').
     */
    public function isAllowedForPortal(string $portal): bool
    {
        return $this->getDesignatedPortal() === strtolower(trim($portal));
    }

    public function role()
    {
        return $this->belongsTo(Role::class,'role_id');
    }

    public function attendances() { return $this->hasMany(Attendance::class); }
    public function leaveRequests() { return $this->hasMany(LeaveRequest::class); }

    public function scopeEligibleForAttendance(Builder $query): Builder
    {
        return $query->where('is_active', 1)
            ->whereHas('role', fn (Builder $role) => $role->where('access_level', '!=', 'super_admin'));
    }

    public function linkedEmployee(): ?Employee
    {
        return Employee::where(function ($query) {
            $query->whereRaw('LOWER(official_mail) = ?', [mb_strtolower($this->email)])
                ->orWhereRaw('LOWER(personal_mail) = ?', [mb_strtolower($this->email)]);
        })->first();
    }
}
