<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvesterFinancialDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'invester_financial_details';

    protected $fillable = [
        'invester_id',
        'investment_amount',
        'equity_percentage',
        'shares_units_held',
        'investment_date',
        'notes',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
        'investment_amount' => 'decimal:2',
        'equity_percentage' => 'decimal:2',
        'investment_date' => 'date',
    ];

    public function invester()
    {
        return $this->belongsTo(Invester::class, 'invester_id');
    }
}

