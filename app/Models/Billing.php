<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Billing extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'value',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];
}
