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
        $breakSecs = $this->combined_break_seconds;

        // If both check_in and check_out are present, calculate net elapsed work time
        if ($this->check_in && $this->check_out) {
            $datePrefix = '';
            if ($this->attendance_date instanceof \Carbon\CarbonInterface) {
                $datePrefix = $this->attendance_date->toDateString() . ' ';
            } elseif (is_string($this->attendance_date) && !empty($this->attendance_date)) {
                $datePrefix = substr($this->attendance_date, 0, 10) . ' ';
            }
            $inStr = $datePrefix . $this->check_in;
            $outStr = $datePrefix . $this->check_out;

            $grossSeconds = max(0, (int) \Carbon\Carbon::parse($inStr)->diffInSeconds(\Carbon\Carbon::parse($outStr), false));
            $netCheckSeconds = max(0, $grossSeconds - $breakSecs);

            $recorded = (int) ($this->total_work_seconds ?? 0);
            if ($this->timer_status === self::TIMER_RUNNING) {
                $startedAt = $this->timer_started_at ?: ($this->check_in ? \Carbon\Carbon::parse($inStr) : null);
                if ($startedAt) {
                    $recorded += max(0, (int) \Carbon\Carbon::parse($startedAt)->diffInSeconds(now(), false));
                }
            }

            // If recorded work seconds is 0, significantly short of netCheckSeconds (>30 mins short, e.g. missed timer session like afternoon restart),
            // or exceeds gross time span, use netCheckSeconds to accurately reflect actual work hours.
            if ($recorded <= 0 || ($netCheckSeconds - $recorded) > 1800 || $recorded > $grossSeconds) {
                return $netCheckSeconds;
            }

            return $recorded;
        }

        $seconds = (int) ($this->total_work_seconds ?? 0);
        if ($this->timer_status === self::TIMER_RUNNING) {
            $datePrefix = '';
            if ($this->attendance_date instanceof \Carbon\CarbonInterface) {
                $datePrefix = $this->attendance_date->toDateString() . ' ';
            } elseif (is_string($this->attendance_date) && !empty($this->attendance_date)) {
                $datePrefix = substr($this->attendance_date, 0, 10) . ' ';
            }
            $startedAt = $this->timer_started_at ?: ($this->check_in ? \Carbon\Carbon::parse($datePrefix . $this->check_in) : null);
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
        return (int) round($this->current_work_seconds / 60);
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
        return self::formatSecondsToTime($this->current_work_seconds);
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
