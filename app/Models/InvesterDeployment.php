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
     * Compute Margin in Rupees and Commission Splits based on Contract Report:
     * - Employee CTC (Sum of salaries from Contract Report)
     * - Margin Amount (Sum of Contract Revenues from Contract Report: Salary * Billing %)
     * - Profit Sharing Model: SZ (70%) / Investor (30%)
     */
    public function computeCommissions(): void
    {
        try {
            $assignedEmps = $this->assigned_employees;
        } catch (\Throwable $e) {
            $assignedEmps = collect();
        }
        if ($assignedEmps->isNotEmpty()) {
            $sumSalary = 0.0;
            $sumRevenue = 0.0;
            foreach ($assignedEmps as $emp) {
                $salData = $emp->getContractReportSalary();
                $sal = (float) $salData['salary'];
                $bp = (float) ($salData['billing_percentage'] ?? $emp->getClientRequirementBillingPercentage() ?? 15.0);
                $rev = (float) ($salData['revenue'] ?? round($sal * ($bp / 100), 2));
                $sumSalary += $sal;
                $sumRevenue += $rev;
            }

            if ($sumSalary > 0 && ((float) $this->employee_ctc <= 0 || empty($this->employee_ctc))) {
                $this->employee_ctc = round($sumSalary, 2);
            }

            if ($sumRevenue > 0) {
                // If margin amount is empty or was legacy calculated on rate card
                if (empty($this->margin_amount) || (float) $this->margin_amount <= 0 || ((float) $this->approved_rate_card > 0 && (float) $this->margin_amount == round((float) $this->approved_rate_card * ((float) $this->margin_percentage / 100), 2))) {
                    $this->margin_amount = round($sumRevenue, 2);
                }
            }

            $baseAmount = (float) ($this->employee_ctc > 0 ? $this->employee_ctc : $sumSalary);
            if ($baseAmount > 0 && (float) $this->margin_amount > 0) {
                $this->margin_percentage = round(((float) $this->margin_amount / $baseAmount) * 100, 2);
                $this->billing_percentage = $this->margin_percentage;
            }
        } else {
            $baseAmount = (float) ($this->employee_ctc > 0 ? $this->employee_ctc : ($this->approved_rate_card ?? 0));
            $billingPct = isset($this->billing_percentage) && $this->billing_percentage !== null && $this->billing_percentage !== ''
                ? (float) $this->billing_percentage
                : null;
            $marginPct = isset($this->margin_percentage) && $this->margin_percentage !== null && $this->margin_percentage !== ''
                ? (float) $this->margin_percentage
                : ($billingPct ?? 15.0);

            if (empty($this->margin_amount) || (float) $this->margin_amount <= 0) {
                $this->margin_amount = round($baseAmount * ($marginPct / 100), 2);
            }
        }

        $baseAmount = (float) ($this->employee_ctc > 0 ? $this->employee_ctc : ($this->approved_rate_card ?? 0));
        $this->approved_rate_card = $baseAmount;
        $marginAmt = (float) $this->margin_amount;

        $tdsPct = isset($this->tds_percentage) && $this->tds_percentage !== null && $this->tds_percentage !== ''
            ? (float) $this->tds_percentage
            : 10.0;
        $szPct = isset($this->szorzo_share_percentage) && $this->szorzo_share_percentage !== null && $this->szorzo_share_percentage !== ''
            ? (float) $this->szorzo_share_percentage
            : 70.0;
        $invPct = isset($this->investor_share_percentage) && $this->investor_share_percentage !== null && $this->investor_share_percentage !== ''
            ? (float) $this->investor_share_percentage
            : 30.0;

        // Calculate TDS amount on base (Employee CTC * TDS %)
        $this->tds_amount = round($baseAmount * ($tdsPct / 100), 2);

        // Ensure Profit sharing percentages sum up logically (default 70% SZ / 30% Investor)
        if ($szPct + $invPct !== 100.0 && $szPct > 0) {
            $invPct = 100.0 - $szPct;
            $this->investor_share_percentage = $invPct;
        }

        // Calculate commissions on Gross Margin
        $this->szorzo_commission_amount = round($marginAmt * ($szPct / 100), 2);
        $this->investor_commission_amount = round($marginAmt * ($invPct / 100), 2);
    }

    /**
     * Get real-time financial metrics for this deployment from Contract Report.
     *
     * @param \Carbon\CarbonInterface|null $month
     * @return array
     */
    public function getContractReportMetrics(?\Carbon\CarbonInterface $month = null): array
    {
        try {
            $assignedEmps = $this->assigned_employees;
        } catch (\Throwable $e) {
            $assignedEmps = collect();
        }
        $totalSalary = 0.0;
        $totalRevenue = 0.0;
        $empDetails = [];

        if ($assignedEmps->isNotEmpty()) {
            foreach ($assignedEmps as $emp) {
                $m = $emp->getContractReportSalary($month);
                $sal = (float) $m['salary'];
                $bp = (float) ($m['billing_percentage'] ?? $emp->getClientRequirementBillingPercentage() ?? 15.0);
                $rev = (float) ($m['revenue'] ?? round($sal * ($bp / 100), 2));
                $totalSalary += $sal;
                $totalRevenue += $rev;
                $empDetails[] = [
                    'id' => $emp->id,
                    'name' => $emp->employee_name,
                    'salary' => $sal,
                    'billing_percentage' => $bp,
                    'revenue' => $rev,
                ];
            }
        } else {
            $totalSalary = (float) $this->employee_ctc;
            $bp = (float) ($this->billing_percentage ?: 15.0);
            $totalRevenue = (float) $this->margin_amount > 0 ? (float) $this->margin_amount : round($totalSalary * ($bp / 100), 2);
        }

        if ($totalSalary <= 0 && (float) $this->employee_ctc > 0) {
            $totalSalary = (float) $this->employee_ctc;
            $totalRevenue = (float) $this->margin_amount > 0 ? (float) $this->margin_amount : round($totalSalary * 0.15, 2);
        }

        $invPct = (float) ($this->investor_share_percentage ?: 30.0);
        $szPct = (float) ($this->szorzo_share_percentage ?: 70.0);

        $investorEarned = round($totalRevenue * ($invPct / 100), 2);
        $szorzoEarned = round($totalRevenue * ($szPct / 100), 2);

        return [
            'total_salary' => round($totalSalary, 2),
            'total_revenue' => round($totalRevenue, 2),
            'investor_earned' => $investorEarned,
            'szorzo_earned' => $szorzoEarned,
            'effective_billing_percentage' => $totalSalary > 0 ? round(($totalRevenue / $totalSalary) * 100, 2) : 0.0,
            'employees' => $empDetails,
        ];
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
        try {
            return Client::whereIn('id', (array) $ids)->get();
        } catch (\Throwable $e) {
            return collect();
        }
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
        try {
            return Employee::whereIn('id', (array) $ids)->get();
        } catch (\Throwable $e) {
            return collect();
        }
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

    /**
     * Retrieve array of employee IDs already chosen on other investors.
     *
     * @param int|null $excludeInvesterId Current investor ID whose employees should remain available
     * @param int|null $excludeDeploymentId Current deployment ID (if editing)
     * @return array<int>
     */
    public static function getDeployedEmployeeIds(?int $excludeInvesterId = null, ?int $excludeDeploymentId = null): array
    {
        try {
            $query = static::query();

            if ($excludeDeploymentId) {
                $query->where('id', '!=', $excludeDeploymentId);
            }

            if ($excludeInvesterId) {
                $query->where('invester_id', '!=', $excludeInvesterId);
            }

            $deployments = $query->get(['id', 'invester_id', 'employee_id', 'employee_ids']);

            $deployedIds = [];
            foreach ($deployments as $dep) {
                $ids = $dep->employee_ids;
                if (is_string($ids)) {
                    $ids = json_decode($ids, true);
                }
                if (is_array($ids)) {
                    foreach ($ids as $id) {
                        if ($id) {
                            $deployedIds[] = (int) $id;
                        }
                    }
                }
                if (!empty($dep->employee_id)) {
                    $deployedIds[] = (int) $dep->employee_id;
                }
            }

            return array_values(array_unique($deployedIds));
        } catch (\Throwable $e) {
            return [];
        }
    }
}

