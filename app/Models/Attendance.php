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
        if ($this->timer_status === self::TIMER_RUNNING) {
            $startedAt = $this->timer_started_at ?: ($this->check_in ? \Carbon\Carbon::parse($this->attendance_date?->toDateString() . ' ' . $this->check_in) : null);
            if ($startedAt) {
                $seconds += max(0, (int) \Carbon\Carbon::parse($startedAt)->diffInSeconds(now(), false));
            }
        }
        return $seconds;
    }

    public function getCurrentBreakSecondsAttribute(): int
    {
        $seconds = (int) ($this->total_break_seconds ?? 0);
        if (in_array($this->timer_status, [self::TIMER_ON_BREAK, self::TIMER_ON_LUNCH], true) && $this->break_started_at) {
            $seconds += max(0, (int) \Carbon\Carbon::parse($this->break_started_at)->diffInSeconds(now(), false));
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

    public function getBreakSecondsAttribute(): int
    {
        $breakSecs = 0;
        $breaks = is_array($this->breaks) ? $this->breaks : [];

        foreach ($breaks as $b) {
            $type = strtolower((string) ($b['type'] ?? 'break'));
            if ($type !== 'lunch') {
                $breakSecs += max(0, (int) ($b['seconds'] ?? round(($b['minutes'] ?? 0) * 60)));
            }
        }

        if ($this->timer_status === self::TIMER_ON_BREAK && $this->break_started_at) {
            $breakSecs += max(0, (int) \Carbon\Carbon::parse($this->break_started_at)->diffInSeconds(now(), false));
        }

        if (empty($breaks) && (int) $this->total_break_seconds > 0 && $this->timer_status !== self::TIMER_ON_LUNCH) {
            $breakSecs = max($breakSecs, (int) $this->total_break_seconds);
        }

        return $breakSecs;
    }

    public function getLunchSecondsAttribute(): int
    {
        $lunchSecs = 0;
        $breaks = is_array($this->breaks) ? $this->breaks : [];

        foreach ($breaks as $b) {
            $type = strtolower((string) ($b['type'] ?? ''));
            if ($type === 'lunch') {
                $lunchSecs += max(0, (int) ($b['seconds'] ?? round(($b['minutes'] ?? 0) * 60)));
            }
        }

        if ($this->timer_status === self::TIMER_ON_LUNCH && $this->break_started_at) {
            $lunchSecs += max(0, (int) \Carbon\Carbon::parse($this->break_started_at)->diffInSeconds(now(), false));
        }

        return $lunchSecs;
    }

    public function getCombinedBreakSecondsAttribute(): int
    {
        return $this->break_seconds + $this->lunch_seconds;
    }

    public function getBreakMinutesAttribute(): int
    {
        return (int) round($this->break_seconds / 60);
    }

    public function getLunchMinutesAttribute(): int
    {
        return (int) round($this->lunch_seconds / 60);
    }

    public function getBreakCountAttribute(): int
    {
        $count = 0;
        foreach ($this->breaks ?? [] as $b) {
            if (strtolower((string) ($b['type'] ?? 'break')) !== 'lunch') {
                $count++;
            }
        }
        if ($this->timer_status === self::TIMER_ON_BREAK) {
            $count++;
        }
        return $count;
    }

    public function getLunchCountAttribute(): int
    {
        $count = 0;
        foreach ($this->breaks ?? [] as $b) {
            if (strtolower((string) ($b['type'] ?? '')) === 'lunch') {
                $count++;
            }
        }
        if ($this->timer_status === self::TIMER_ON_LUNCH) {
            $count++;
        }
        return $count;
    }

    public static function formatSecondsToTime(int $totalSeconds): string
    {
        $totalSeconds = max(0, $totalSeconds);
        $hours = intdiv($totalSeconds, 3600);
        $minutes = intdiv($totalSeconds % 3600, 60);
        return sprintf('%02d:%02d', $hours, $minutes);
    }

    public static function formatSecondsHuman(int $totalSeconds): string
    {
        $totalSeconds = max(0, $totalSeconds);
        $hours = intdiv($totalSeconds, 3600);
        $minutes = intdiv($totalSeconds % 3600, 60);

        if ($hours === 0 && $minutes === 0) {
            return '0m';
        }
        if ($hours === 0) {
            return "{$minutes}m";
        }
        if ($minutes === 0) {
            return "{$hours}h";
        }
        return "{$hours}h {$minutes}m";
    }

    public function getFormattedWorkTimeAttribute(): string
    {
        $seconds = $this->current_work_seconds;
        if ($seconds > 0) {
            return self::formatSecondsToTime($seconds);
        }
        $minutes = $this->working_minutes;
        return sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60);
    }

    public function getFormattedBreakTimeAttribute(): string
    {
        return self::formatSecondsToTime($this->break_seconds);
    }

    public function getFormattedLunchTimeAttribute(): string
    {
        return self::formatSecondsToTime($this->lunch_seconds);
    }

    public function getFormattedTotalBreakTimeAttribute(): string
    {
        return self::formatSecondsToTime($this->combined_break_seconds);
    }
}
