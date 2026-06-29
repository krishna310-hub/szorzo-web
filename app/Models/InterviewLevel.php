<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InterviewLevel extends Model
{
    protected $fillable = [
        'name',
        'sort_order',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];
}
