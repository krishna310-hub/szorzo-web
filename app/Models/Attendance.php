<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    public const STATUSES = ['present', 'absent', 'half_day', 'on_leave', 'holiday', 'week_off'];
    public const TIMER_NOT_STARTED = 'not_started';
    public const TIMER_RUNNING = 'running';
    public const TIMER_ON_BREAK = 'on_break';
    public const TIMER_ON_LUNCH = 'on_lunch';
    public const TIMER_COMPLETED = 'completed';

    protected $fillable = [
        'employee_id',
        'user_id',
        'attendance_date',
        'status',
        'timer_status',
        'timer_started_at',
        'break_started_at',
        'total_work_seconds',
        'total_break_seconds',
        'breaks',
        'check_in',
        'check_out',
        'remarks',
        'leave_request_id',
        'marked_by',
    ];

    protected function casts(): array
    {
        return [
            'attendance_date' => 'date',
            'timer_started_at' => 'datetime',
            'break_started_at' => 'datetime',
            'breaks' => 'array',
            'total_work_seconds' => 'integer',
            'total_break_seconds' => 'integer',
        ];
    }

    public function user() { return $this->belongsTo(User::class); }
    public function employee() { return $this->belongsTo(Employee::class); }
    public function marker() { return $this->belongsTo(User::class, 'marked_by'); }
    public function leaveRequest() { return $this->belongsTo(LeaveRequest::class); }

    public function scopeEligible(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->whereHas('user', fn (Builder $users) => $users->eligibleForAttendance())
              ->orWhereNotNull('employee_id');
        });
    }

    public function getCurrentWorkSecondsAttribute(): int
    {
        $seconds = (int) ($this->total_work_seconds ?? 0);
        if ($this->timer_status === self::TIMER_RUNNING && $this->timer_started_at) {
            $seconds += max(0, (int) now()->diffInSeconds($this->timer_started_at));
        }
        return $seconds;
    }

    public function getCurrentBreakSecondsAttribute(): int
    {
        $seconds = (int) ($this->total_break_seconds ?? 0);
        if (in_array($this->timer_status, [self::TIMER_ON_BREAK, self::TIMER_ON_LUNCH], true) && $this->break_started_at) {
            $seconds += max(0, (int) now()->diffInSeconds($this->break_started_at));
        }
        return $seconds;
    }

    public function getWorkingMinutesAttribute(): int
    {
        if ($this->total_work_seconds > 0) {
            return (int) round($this->current_work_seconds / 60);
        }
        if (! $this->check_in || ! $this->check_out) return 0;
        return max(0, (int) \Carbon\Carbon::parse($this->check_in)->diffInMinutes(\Carbon\Carbon::parse($this->check_out), false));
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->employee?->employee_name ?? $this->user?->name ?? 'User #' . $this->user_id;
    }

    public function getEmployeeNoAttribute(): string
    {
        return $this->employee?->employee_no ?? $this->user?->linkedEmployee()?->employee_no ?? '—';
    }
}
