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

    public function isSuperAdmin()
	{
		if ($this->role) {
			if ($this->role->access_level == 'super_admin') {
				return true;
			}
		}
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
