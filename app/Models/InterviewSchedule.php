<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InterviewSchedule extends Model
{
    use SoftDeletes;

    public const STATUSES = [
        'scheduled' => 'Scheduled',
        'completed' => 'Completed',
        'selected' => 'Selected',
        'rejected' => 'Rejected',
        'cancelled' => 'Cancelled',
    ];

    protected $fillable = [
        'candidate_id',
        'client_id',
        'job_role_id',
        'interview_mode_id',
        'level_of_interview_id',
        'schedule_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'schedule_date' => 'datetime',
    ];

    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function jobRole()
    {
        return $this->belongsTo(JobRole::class);
    }

    public function interviewLevel()
    {
        return $this->belongsTo(InterviewLevel::class, 'level_of_interview_id');
    }

    public function interviewMode()
    {
        return $this->belongsTo(InterviewMode::class, 'interview_mode_id');
    }

    /** Limit schedules to candidates visible to the logged-in user. */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $query->whereHas(
            'candidate',
            fn (Builder $candidateQuery) => $candidateQuery->visibleTo($user)
        );
    }
}
