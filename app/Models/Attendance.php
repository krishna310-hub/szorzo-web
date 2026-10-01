<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    public const STATUSES = ['present', 'absent', 'half_day', 'on_leave', 'holiday', 'week_off'];

    protected $fillable = ['employee_id', 'user_id', 'attendance_date', 'status', 'check_in', 'check_out', 'remarks', 'leave_request_id', 'marked_by'];

    protected function casts(): array { return ['attendance_date' => 'date']; }

    public function employee() { return $this->belongsTo(Employee::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function marker() { return $this->belongsTo(User::class, 'marked_by'); }
    public function leaveRequest() { return $this->belongsTo(LeaveRequest::class); }

    public function scopeEligible(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereHas('employee', fn (Builder $eq) => $eq->eligibleForAttendance())
              ->orWhere(function ($uq) {
                  $uq->whereNull('employee_id')
                     ->whereHas('user', fn (Builder $users) => $users->eligibleForAttendance());
              });
        });
    }

    public function getWorkingMinutesAttribute(): int
    {
        if (! $this->check_in || ! $this->check_out) return 0;
        return max(0, (int) \Carbon\Carbon::parse($this->check_in)->diffInMinutes(\Carbon\Carbon::parse($this->check_out), false));
    }
}
