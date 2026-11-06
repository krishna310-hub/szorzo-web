<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    use HasFactory;

    public const TYPES = ['Casual Leave', 'Sick Leave', 'Earned Leave', 'Unpaid Leave'];
    public const STATUSES = ['pending', 'approved', 'rejected'];
    public const DAY_TYPES = ['full_day', 'half_day'];
    public const HALF_DAY_TYPES = ['morning', 'afternoon'];

    protected $fillable = [
        'user_id',
        'leave_type',
        'day_type',
        'half_day_type',
        'from_date',
        'to_date',
        'number_of_days',
        'reason',
        'attachment',
        'status',
        'review_remarks',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'from_date' => 'date',
            'to_date' => 'date',
            'number_of_days' => 'decimal:1',
            'reviewed_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function scopeEligible(Builder $query): Builder
    {
        return $query->whereHas('user', fn (Builder $q) => $q->eligibleForAttendance());
    }

    /**
     * Check if this leave request is for a half-day.
     */
    public function isHalfDay(): bool
    {
        return ($this->day_type === 'half_day') || ((float) $this->number_of_days === 0.5);
    }

    /**
     * Accessor for half_day_type with fallback to reason string if column wasn't migrated yet.
     */
    public function getHalfDayTypeAttribute($value): ?string
    {
        if (!empty($value)) {
            return strtolower($value);
        }

        return $this->extractHalfDaySessionFromReason();
    }

    /**
     * Helper to extract session (morning/afternoon) from reason if embedded as fallback.
     */
    public function extractHalfDaySessionFromReason(): ?string
    {
        if (preg_match('/\[Half Day - (Morning|Afternoon)\]/i', (string) $this->reason, $matches)) {
            return strtolower($matches[1]);
        }

        return null;
    }

    /**
     * User-facing duration badge label.
     */
    public function getDurationLabel(): string
    {
        if ($this->isHalfDay()) {
            $session = $this->half_day_type ? ' (' . ucfirst($this->half_day_type) . ')' : '';
            return '0.5 Day' . $session;
        }

        $days = (float) $this->number_of_days;
        return $days . ($days == 1 ? ' Day' : ' Days');
    }
}
