<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Candidate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'created_at',
        'recruiter_id',
        'client_id',
        'job_role_id',
        'mode_id',
        'contract_from_date',
        'contract_to_date',
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
        'onboarding_ctc',
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
        'contract_from_date' => 'date',
        'contract_to_date' => 'date',
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

    public function mode()
    {
        return $this->belongsTo(Mode::class);
    }

    public function interviewLevel()
    {
        return $this->belongsTo(InterviewLevel::class, 'level_of_interview_id');
    }

    public function interviewSchedules()
    {
        return $this->hasMany(InterviewSchedule::class);
    }

    public function revenue()
    {
        return $this->hasOne(Revenue::class);
    }

    public function contractReports()
    {
        return $this->hasMany(ContractReport::class);
    }

    /** Limit candidates to the recruiter team available to the user. */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        $accessLevel = str_replace('_', '-', strtolower((string) $user->role?->access_level));

        if ($accessLevel === 'super-admin') {
            return $query;
        }

        if (! in_array($accessLevel, ['delivery-lead', 'recruiter-dl', 'recruiter'], true)) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn(
            'candidates.recruiter_id',
            Recruiter::query()->visibleTo($user)->select('id')
        );
    }
}
