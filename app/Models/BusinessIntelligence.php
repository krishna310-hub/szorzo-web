<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class BusinessIntelligence extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category',
        'metric_name',
        'description',
        'value_type',
        'display_order',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];
}
