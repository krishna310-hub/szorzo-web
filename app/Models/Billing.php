<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Billing extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'invoice_number',
        'title',
        'value',
        'invoice_date',
        'amount',
        'notes',
        'status',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'amount' => 'decimal:2',
        'status' => 'boolean',
    ];
}
