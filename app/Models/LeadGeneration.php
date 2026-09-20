<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class LeadGeneration extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'lead_source',
        'lead_owner',
        'relationship_manager',
        'contact_info',
        'client_profile_id',
        'service_id',
        'lead_date',
        'follow_up_date',
        'notes',
        'status',
    ];

    protected $casts = [
        'lead_date' => 'date',
        'follow_up_date' => 'date',
        'status' => 'boolean',
    ];

    public function clientProfile()
    {
        return $this->belongsTo(ClientProfile::class);
    }

    public function serviceOffered()
    {
        return $this->belongsTo(ServiceOffered::class, 'service_id');
    }
}
