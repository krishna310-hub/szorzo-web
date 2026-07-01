<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Location extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function recruiters()
    {
        return $this->hasMany(Recruiter::class);
    }

    public function candidates()
    {
        return $this->hasMany(Candidate::class);
    }
}
