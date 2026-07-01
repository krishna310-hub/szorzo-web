<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InterviewLevel extends Model
{
    use SoftDeletes;

    protected $table = 'level_of_interviews';

    protected $fillable = [
        'level',
        'sort_order',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];
}
