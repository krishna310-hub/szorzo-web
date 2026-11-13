<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    use HasFactory;

    public const TYPES = [
        'Company',
        'National',
        'Festival',
        'Public',
        'Restricted',
    ];

    protected $fillable = [
        'title',
        'date',
        'year',
        'type',
        'description',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
        'year' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (Holiday $holiday) {
            $rawDate = $holiday->attributes['date'] ?? $holiday->date;
            if ($rawDate) {
                $dateObj = $rawDate instanceof CarbonInterface
                    ? $rawDate
                    : Carbon::parse($rawDate);
                $holiday->year = (int) $dateObj->year;
            }
        });

        static::updating(function (Holiday $holiday) {
            if ($holiday->isDirty('date')) {
                $oldDate = $holiday->getOriginal('date');
                if ($oldDate) {
                    $oldDateStr = $oldDate instanceof CarbonInterface ? $oldDate->toDateString() : substr((string)$oldDate, 0, 10);
                    static::removeHolidayAttendanceForDate($oldDateStr);
                }
            }
        });

        static::saved(function (Holiday $holiday) {
            $rawDate = $holiday->attributes['date'] ?? $holiday->date;
            $dateStr = $rawDate instanceof CarbonInterface ? $rawDate->toDateString() : substr((string)$rawDate, 0, 10);
            static::syncAttendanceForDate($dateStr, $holiday->title, $holiday->created_by);
        });

        static::deleted(function (Holiday $holiday) {
            $rawDate = $holiday->attributes['date'] ?? $holiday->date;
            $dateStr = $rawDate instanceof CarbonInterface ? $rawDate->toDateString() : substr((string)$rawDate, 0, 10);
            static::removeHolidayAttendanceForDate($dateStr);
        });
    }

    public function fromDateTime($value)
    {
        if (empty($value)) {
            return $value;
        }

        if ($value instanceof CarbonInterface) {
            return $value->format('Y-m-d');
        }

        try {
            return parent::fromDateTime($value);
        } catch (\Throwable) {
            return substr((string) $value, 0, 10);
        }
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeForYear(Builder $query, int $year): Builder
    {
        return $query->where('year', $year);
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->whereDate('date', '>=', now()->toDateString())->orderBy('date', 'asc');
    }

    public function scopePast(Builder $query): Builder
    {
        return $query->whereDate('date', '<', now()->toDateString())->orderBy('date', 'desc');
    }

    public function getFormattedDateAttribute(): string
    {
        $d = $this->attributes['date'] ?? null;
        if (!$d) {
            return '';
        }
        return ($d instanceof CarbonInterface ? $d : Carbon::parse($d))->format('d M Y');
    }

    public function getDayNameAttribute(): string
    {
        $d = $this->attributes['date'] ?? null;
        if (!$d) {
            return '';
        }
        return ($d instanceof CarbonInterface ? $d : Carbon::parse($d))->format('l');
    }

    public function getIsUpcomingAttribute(): bool
    {
        $d = $this->attributes['date'] ?? null;
        if (!$d) {
            return false;
        }
        $dateStr = $d instanceof CarbonInterface ? $d->toDateString() : substr((string)$d, 0, 10);
        return $dateStr > now()->toDateString();
    }

    public function getIsPastAttribute(): bool
    {
        $d = $this->attributes['date'] ?? null;
        if (!$d) {
            return false;
        }
        $dateStr = $d instanceof CarbonInterface ? $d->toDateString() : substr((string)$d, 0, 10);
        return $dateStr < now()->toDateString();
    }

    public function getIsTodayAttribute(): bool
    {
        $d = $this->attributes['date'] ?? null;
        if (!$d) {
            return false;
        }
        $dateStr = $d instanceof CarbonInterface ? $d->toDateString() : substr((string)$d, 0, 10);
        return $dateStr === now()->toDateString();
    }

    /**
     * Automatically sync Attendance records for all eligible employees on this holiday date.
     */
    public static function syncAttendanceForDate(string $date, string $title, ?int $markedBy = null): void
    {
        try {
            if (!static::getConnectionResolver()) {
                return;
            }

            $users = User::eligibleForAttendance()->get();
            if ($users->isEmpty()) {
                return;
            }

            foreach ($users as $user) {
                $employee = $user->linkedEmployee() ?? $user->getOrCreateEmployee();
                $employeeId = $employee?->id;

                $attendance = null;
                if ($employeeId) {
                    $attendance = Attendance::where('employee_id', $employeeId)->whereDate('attendance_date', $date)->first();
                }
                if (!$attendance) {
                    $attendance = Attendance::where('user_id', $user->id)->whereDate('attendance_date', $date)->first();
                }

                // If an attendance record exists, we do NOT overwrite if the employee actually logged in/worked
                if ($attendance) {
                    if (empty($attendance->check_in) && (int) ($attendance->total_work_seconds ?? 0) === 0) {
                        $attendance->update([
                            'status' => 'holiday',
                            'timer_status' => Attendance::TIMER_COMPLETED,
                            'remarks' => 'Holiday: ' . $title,
                            'marked_by' => $markedBy ?? auth()->id() ?? $attendance->marked_by,
                        ]);
                    }
                } else {
                    Attendance::create([
                        'employee_id' => $employeeId,
                        'user_id' => $user->id,
                        'attendance_date' => $date,
                        'status' => 'holiday',
                        'timer_status' => Attendance::TIMER_COMPLETED,
                        'check_in' => null,
                        'check_out' => null,
                        'remarks' => 'Holiday: ' . $title,
                        'marked_by' => $markedBy ?? auth()->id(),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            // Suppress if DB connection not ready during unit tests
        }
    }

    /**
     * Remove auto-marked holiday attendance records for a specific date if no holiday remains.
     */
    public static function removeHolidayAttendanceForDate(string $date): void
    {
        try {
            if (!static::getConnectionResolver()) {
                return;
            }

            if (static::whereDate('date', $date)->exists()) {
                return;
            }

            Attendance::whereDate('attendance_date', $date)
                ->where('status', 'holiday')
                ->where(function ($q) {
                    $q->where('remarks', 'like', 'Holiday:%')
                      ->orWhereNull('check_in');
                })
                ->delete();
        } catch (\Throwable $e) {
            // Suppress
        }
    }
}
