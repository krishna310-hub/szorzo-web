<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class ClientProfile extends Model
{
    use SoftDeletes;

    protected $fillable = [
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
        'customer_since',
        'status',
        'is_converted_to_client'
    ];

    protected $casts = [
        'status' => 'boolean',
        'is_converted_to_client' => 'boolean',
    ];
}
