<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientRequirement extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'client_id',
        'billing_id',
        'revenue_amount',
        'job_description_id',
        'mode_id',
        'requirement_open_date',
        'job_role_id',
        'number_of_position',
        'closure_target_date',
        'cv_required',
        'cv_uploaded',
        'project_owner',
        'ctc',
        'location_id',
        'status',
    ];

    protected $casts = [
        'requirement_open_date' => 'date',
        'closure_target_date' => 'date',
        'status' => 'boolean',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function mode()
    {
        return $this->belongsTo(Mode::class);
    }

    public function jobDescription()
    {
        return $this->belongsTo(ClientJobRole::class, 'job_description_id');
    }

    public function jobRole()
    {
        return $this->belongsTo(JobRole::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function projectOwner()
    {
        return $this->belongsTo(Recruiter::class, 'project_owner');
    }

    public function billing()
    {
        return $this->belongsTo(Billing::class);
    }
}
