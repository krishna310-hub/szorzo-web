<?php

namespace App\Models;

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
}
