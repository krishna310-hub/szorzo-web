<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Candidate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'recruiter_id',
        'client_id',
        'job_role_id',
        'candidate_name',
        'mobile_no',
        'email',
        'qualification',
        'total_experience',
        'relevant_experience',
        'take_home',
        'variable',
        'current_ctc',
        'expected_ctc',
        'notice_period',
        'current_company',
        'current_location',
        'preferred_location',
        'reason_for_change',
        'level_of_interview_id',
        'status',
        'upload_cv',
        'onboarding_date',
    ];

    protected $casts = [
        'status' => 'boolean',
        'onboarding_date' => 'date',
    ];

    public function recruiter()
    {
        return $this->belongsTo(Recruiter::class);
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

    public function interviewSchedules()
    {
        return $this->hasMany(InterviewSchedule::class);
    }
}
