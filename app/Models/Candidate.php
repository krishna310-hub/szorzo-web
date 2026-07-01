<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Candidate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'profile_image',
        'location_id',
        'email',
        'mobile_no',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
