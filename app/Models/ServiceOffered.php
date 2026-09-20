<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceOffered extends Model
{
    use SoftDeletes;

    protected $table = 'service_offereds';

    protected $fillable = [
        'service_name',
        'service_code',
        'category',
        'description',
        'display_order',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];
}
