<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientRequirement extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'client_id',
        'billing_percentage',
        'job_description_id',
        'mode_id',
        'open_date',
        'job_role_id',
        'ctc',
        'location_id',
        'no_of_positions',
        'closure_target_date',
        'cvs_required',
        'cvs_uploaded',
        'project_owner_id',
        'status',
    ];

    protected $casts = [
        'open_date' => 'date',
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
        return $this->belongsTo(Recruiter::class, 'project_owner_id');
    }
}
