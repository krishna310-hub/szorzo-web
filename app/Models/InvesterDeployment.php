<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvesterDeployment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'invester_deployments';

    protected $fillable = [
        'invester_id',
        'employee_id',
        'client_id',
        'mode_of_engagement',
        'contract_duration',
        'approved_rate_card',
        'employee_ctc',
        'margin_percentage',
        'tds_percentage',
        'margin_amount',
        'szorzo_share_percentage',
        'investor_share_percentage',
        'szorzo_commission_amount',
        'investor_commission_amount',
        'start_date',
        'end_date',
        'notes',
        'status',
    ];

    protected $casts = [
        'approved_rate_card' => 'decimal:2',
        'employee_ctc' => 'decimal:2',
        'margin_percentage' => 'decimal:2',
        'tds_percentage' => 'decimal:2',
        'margin_amount' => 'decimal:2',
        'szorzo_share_percentage' => 'decimal:2',
        'investor_share_percentage' => 'decimal:2',
        'szorzo_commission_amount' => 'decimal:2',
        'investor_commission_amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'status' => 'boolean',
    ];

    /**
     * Boot model events for automatic financial commission calculations.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function (InvesterDeployment $deployment) {
            $deployment->computeCommissions();
        });
    }

    /**
     * Compute Margin in Rupees and Commission Splits based on photo requirements:
     * - Rate Card
     * - Margin %
     * - Margin In Rupees
     * - Profit Sharing Model: SZ (70%) / Investor (30%)
     */
    public function computeCommissions(): void
    {
        $rateCard = (float) ($this->approved_rate_card ?? 0);
        $marginPct = (float) ($this->margin_percentage ?? 15);
        $tdsPct = (float) ($this->tds_percentage ?? 10);
        $szPct = (float) ($this->szorzo_share_percentage ?? 70);
        $invPct = (float) ($this->investor_share_percentage ?? 30);

        // If Margin in Rupees was not manually supplied, compute: Rate Card * (Margin % / 100)
        if (empty($this->margin_amount) || (float) $this->margin_amount <= 0) {
            $this->margin_amount = round($rateCard * ($marginPct / 100), 2);
        } else {
            // If margin amount is supplied and rate card > 0, ensure margin percentage is synced
            if ($rateCard > 0 && empty($this->margin_percentage)) {
                $this->margin_percentage = round(((float) $this->margin_amount / $rateCard) * 100, 2);
            }
        }

        $marginAmt = (float) $this->margin_amount;

        // Ensure Profit sharing percentages sum up logically (default 70% SZ / 30% Investor)
        if ($szPct + $invPct !== 100.0 && $szPct > 0) {
            $invPct = 100.0 - $szPct;
            $this->investor_share_percentage = $invPct;
        }

        // Calculate commissions
        $this->szorzo_commission_amount = round($marginAmt * ($szPct / 100), 2);
        $this->investor_commission_amount = round($marginAmt * ($invPct / 100), 2);
    }

    public function invester()
    {
        return $this->belongsTo(Invester::class, 'invester_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeForInvester($query, $investerId)
    {
        return $query->where('invester_id', $investerId);
    }
}

