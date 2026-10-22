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
        'employee_ids',
        'client_id',
        'client_ids',
        'mode_of_engagement',
        'contract_duration',
        'approved_rate_card',
        'employee_ctc',
        'billing_percentage',
        'margin_percentage',
        'tds_percentage',
        'tds_amount',
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
        'client_ids' => 'array',
        'employee_ids' => 'array',
        'approved_rate_card' => 'decimal:2',
        'employee_ctc' => 'decimal:2',
        'billing_percentage' => 'decimal:2',
        'margin_percentage' => 'decimal:2',
        'tds_percentage' => 'decimal:2',
        'tds_amount' => 'decimal:2',
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
     * - Billing % (Auto-fetched from client module)
     * - Margin %
     * - TDS % (Auto-fetched statutory 10%)
     * - Margin In Rupees
     * - TDS Amount (Rate Card * TDS %)
     * - Profit Sharing Model: SZ (70%) / Investor (30%)
     */
    public function computeCommissions(): void
    {
        $rateCard = (float) ($this->approved_rate_card ?? 0);
        $billingPct = isset($this->billing_percentage) && $this->billing_percentage !== null && $this->billing_percentage !== ''
            ? (float) $this->billing_percentage
            : null;
        $marginPct = isset($this->margin_percentage) && $this->margin_percentage !== null && $this->margin_percentage !== ''
            ? (float) $this->margin_percentage
            : ($billingPct ?? 15.0);
        $tdsPct = isset($this->tds_percentage) && $this->tds_percentage !== null && $this->tds_percentage !== ''
            ? (float) $this->tds_percentage
            : 10.0;
        $szPct = isset($this->szorzo_share_percentage) && $this->szorzo_share_percentage !== null && $this->szorzo_share_percentage !== ''
            ? (float) $this->szorzo_share_percentage
            : 70.0;
        $invPct = isset($this->investor_share_percentage) && $this->investor_share_percentage !== null && $this->investor_share_percentage !== ''
            ? (float) $this->investor_share_percentage
            : 30.0;

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

        // Calculate TDS amount on gross billing (Rate Card * TDS %)
        $this->tds_amount = round($rateCard * ($tdsPct / 100), 2);

        // Ensure Profit sharing percentages sum up logically (default 70% SZ / 30% Investor)
        if ($szPct + $invPct !== 100.0 && $szPct > 0) {
            $invPct = 100.0 - $szPct;
            $this->investor_share_percentage = $invPct;
        }

        // Calculate commissions
        $this->szorzo_commission_amount = round($marginAmt * ($szPct / 100), 2);
        $this->investor_commission_amount = round($marginAmt * ($invPct / 100), 2);
    }

    /**
     * Retrieve all clients assigned to this deployment (multi-select client support).
     */
    public function getAssignedClientsAttribute()
    {
        $ids = $this->client_ids;
        if (is_string($ids)) {
            $ids = json_decode($ids, true);
        }
        if (empty($ids) && $this->client_id) {
            $ids = [$this->client_id];
        }
        if (empty($ids)) {
            return collect();
        }
        return Client::whereIn('id', (array) $ids)->get();
    }

    /**
     * Retrieve all employees assigned to this deployment (multi-select employee support).
     */
    public function getAssignedEmployeesAttribute()
    {
        $ids = $this->employee_ids;
        if (is_string($ids)) {
            $ids = json_decode($ids, true);
        }
        if (empty($ids) && $this->employee_id) {
            $ids = [$this->employee_id];
        }
        if (empty($ids)) {
            return collect();
        }
        return Employee::whereIn('id', (array) $ids)->get();
    }

    /**
     * Formatted string of client names.
     */
    public function getClientNamesAttribute(): string
    {
        $clients = $this->assigned_clients;
        if ($clients->isEmpty()) {
            return $this->client?->client ?: '-';
        }
        return $clients->pluck('client')->implode(', ');
    }

    /**
     * Formatted string of employee names.
     */
    public function getEmployeeNamesAttribute(): string
    {
        $employees = $this->assigned_employees;
        if ($employees->isEmpty()) {
            return $this->employee?->employee_name ?: '-';
        }
        return $employees->pluck('employee_name')->implode(', ');
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

