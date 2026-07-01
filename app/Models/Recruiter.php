<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Recruiter extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
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
