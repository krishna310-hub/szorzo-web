<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class ClientProfile extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'account_id',
        'client_id',
        'lead_generation_id',
        'assigned_to',
        'service_id',
        'legal_entity_name',
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
        'account_source',
        'customer_domain',
        'account_owner',
        'relationship_manager',
        'customer_since',
        'account_created_date',
        'last_updated_date',
        'relationship_status',
        'primary_contact_name_designation',
        'primary_email',
        'primary_contact_number',
        'status',
        'is_converted_to_client'
    ];

    protected $casts = [
        'status' => 'boolean',
        'is_converted_to_client' => 'boolean',
        'account_created_date' => 'date',
        'last_updated_date' => 'date',
    ];

    public function leadGeneration()
    {
        return $this->belongsTo(LeadGeneration::class, 'lead_generation_id');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
