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
        'position_level',
        'revenue_amount',
        'payment_cycle',
        'job_description_id',
        'mode_id',
        'mode_ids',
        'requirement_open_date',
        'job_role_id',
        'number_of_position',
        'closure_target_date',
        'cv_required',
        'cv_uploaded',
        'project_owner',
        'project_owner_ids',
        'is_priority',
        'ctc',
        'location_id',
        'location_ids',
        'status',
    ];

    protected $casts = [
        'requirement_open_date' => 'date',
        'closure_target_date' => 'date',
        'mode_ids' => 'array',
        'location_ids' => 'array',
        'project_owner_ids' => 'array',
        'is_priority' => 'boolean',
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

    public function modes()
    {
        return Mode::whereIn('id', $this->mode_ids ?: array_filter([$this->mode_id]))->get();
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

    public function locations()
    {
        return Location::whereIn('id', $this->location_ids ?: array_filter([$this->location_id]))->get();
    }

    public function projectOwner()
    {
        return $this->belongsTo(Recruiter::class, 'project_owner');
    }

    public function projectOwners()
    {
        return Recruiter::whereIn(
            'id',
            $this->project_owner_ids ?: array_filter([$this->project_owner])
        )->get();
    }

    public function billing()
    {
        return $this->belongsTo(Billing::class);
    }
}
