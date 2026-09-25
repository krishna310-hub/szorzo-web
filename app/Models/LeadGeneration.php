<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class LeadGeneration extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'account_id',
        'account_source',
        'account_name',
        'industry',
        'sub_industry',
        'website_url',
        'country_of_origin',
        'region',
        'state',
        'city',
        'registered_address',
        'pin_code',
        'ownership_type',
        'registration_id',
        'gstin',
        'account_owner',
        'relationship_manager',
        'assigned_to',
        'pipeline_stage',
        'opportunity_status',
        'next_follow_up_at',
        'follow_up_notes',
        'first_contact_at',
        'client_profile_id',
        'customer_since',
        'account_created_date',
        'last_updated_date',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
        'account_created_date' => 'date',
        'last_updated_date' => 'date',
        'next_follow_up_at' => 'datetime',
        'first_contact_at' => 'datetime',
    ];

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function clientProfile()
    {
        return $this->belongsTo(ClientProfile::class);
    }
}
