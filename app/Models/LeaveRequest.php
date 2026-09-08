<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    use HasFactory;
    public const TYPES = ['Casual Leave', 'Sick Leave', 'Earned Leave', 'Unpaid Leave', 'Other'];
    public const STATUSES = ['pending', 'approved', 'rejected'];
    protected $fillable = ['user_id', 'leave_type', 'from_date', 'to_date', 'number_of_days', 'reason', 'attachment', 'status', 'review_remarks', 'reviewed_by', 'reviewed_at'];
    protected function casts(): array { return ['from_date' => 'date', 'to_date' => 'date', 'number_of_days' => 'decimal:1', 'reviewed_at' => 'datetime']; }
    public function user() { return $this->belongsTo(User::class); }
    public function reviewer() { return $this->belongsTo(User::class, 'reviewed_by'); }
    public function attendances() { return $this->hasMany(Attendance::class); }
    public function scopeEligible(Builder $query): Builder { return $query->whereHas('user', fn (Builder $q) => $q->eligibleForAttendance()); }
}
