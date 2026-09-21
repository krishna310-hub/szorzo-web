<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class BusinessIntelligence extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'contact_id',
        'account_id',
        'account_name',
        'contact_name',
        'designation',
        'department',
        'mobile_number',
        'alternate_contact',
        'email_id',
        'contact_type',
        'last_contacted_date',
        'next_follow_up_date',
        'contact_notes',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
        'last_contacted_date' => 'date',
        'next_follow_up_date' => 'date',
    ];
}
