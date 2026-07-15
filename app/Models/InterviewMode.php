<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InterviewMode extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'interview_mode',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];
}
