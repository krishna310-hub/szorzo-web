<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Recruiter extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'recruiter_name',
        'location',
        'email',
        'mobile_number',
        'performance_rating',
        'delivery_lead_user_id',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function deliveryLead()
    {
        return $this->belongsTo(User::class, 'delivery_lead_user_id');
    }

}
