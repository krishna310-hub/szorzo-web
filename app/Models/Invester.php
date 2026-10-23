<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invester extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'investers';

    protected $fillable = [
        'invester_id',
        'user_id',
        'portal_username',
        'portal_password',
        'name',
        'profile_picture',
        'investor_type',
        'contact',
        'primary_email',
        'alternate_email',
        'address',
        'account_manager_id',
        'account_manager_ids',
        'account_manager_name',
        'account_manager_email',
        'account_manager_contact',
        'account_manager_role',
        'internal_notes',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
        'account_manager_ids' => 'array',
    ];

    /**
     * Account Manager / Point of Contact (External Employee - Primary).
     */
    public function accountManager()
    {
        return $this->belongsTo(Employee::class, 'account_manager_id');
    }

    /**
     * Collection of all assigned Account Managers (External Employees).
     */
    public function getAssignedAccountManagersAttribute()
    {
        $ids = $this->account_manager_ids;
        if (empty($ids) && !empty($this->account_manager_id)) {
            $ids = [(int) $this->account_manager_id];
        }
        if (empty($ids) || !is_array($ids)) {
            return collect();
        }
        return Employee::whereIn('id', $ids)->get();
    }

    /**
     * Comma-separated names of assigned Account Managers.
     */
    public function getAccountManagerNamesAttribute(): string
    {
        if (!empty($this->account_manager_name)) {
            return (string) $this->account_manager_name;
        }
        $managers = $this->assigned_account_managers;
        if ($managers->isNotEmpty()) {
            return $managers->pluck('employee_name')->filter()->join(', ');
        }
        return (string) ($this->accountManager?->employee_name ?? $this->accountManager?->name ?? 'Unassigned');
    }

    /**
     * Team & Management Scope records.
     */
    public function teamManagements()
    {
        return $this->hasMany(InvesterTeamManagement::class, 'invester_id');
    }

    /**
     * Linked Login Account (User with Role ID 6).
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Locate linked user record via user_id, ref (invester_id), or email.
     */
    public function linkedUser(): ?User
    {
        if ($this->user) {
            return $this->user;
        }

        if (!empty($this->invester_id)) {
            $u = User::where('ref', $this->invester_id)->first();
            if ($u) {
                return $u;
            }
        }

        if (!empty($this->portal_username)) {
            $u = User::where('name', $this->portal_username)->first();
            if ($u) {
                return $u;
            }
        }

        $email = mb_strtolower(trim((string) $this->primary_email));
        if ($email !== '') {
            $u = User::whereRaw('LOWER(email) = ?', [$email])->first();
            if ($u) {
                return $u;
            }
        }

        return null;
    }

    /**
     * Invest Modules / Employment Details & Profit Sharing Deployments.
     */
    public function deployments()
    {
        return $this->hasMany(InvesterDeployment::class, 'invester_id');
    }

    /**
     * Financial & Investment Details records.
     */
    public function financialDetails()
    {
        return $this->hasMany(InvesterFinancialDetail::class, 'invester_id');
    }

    /**
     * Total Overall Investment capital (sum of tranches).
     */
    public function getTotalOverallInvestmentAttribute(): float
    {
        return (float) $this->financialDetails()->sum('investment_amount');
    }

    /**
     * Total unique external employees mapped across active deployments.
     */
    public function getTotalMappedEmployeesAttribute(): int
    {
        return $this->deployments()->where('status', true)->get()->flatMap(function ($d) {
            $ids = $d->employee_ids;
            if (is_string($ids)) {
                $ids = json_decode($ids, true);
            }
            if (empty($ids) && $d->employee_id) {
                $ids = [$d->employee_id];
            }
            return (array) $ids;
        })->filter()->unique()->count();
    }

    /**
     * Monthly payroll commitment (sum of employee CTC) across active deployments.
     */
    public function getMonthlyEmployeeCostAttribute(): float
    {
        return (float) $this->deployments()->where('status', true)->sum('employee_ctc');
    }

    /**
     * Total cumulative employee salary spend across elapsed active months.
     */
    public function getTotalSpendToDateAttribute(): float
    {
        $activeDeps = $this->deployments()->where('status', true)->get();
        if ($activeDeps->isEmpty()) {
            return 0.0;
        }

        $now = \Carbon\CarbonImmutable::now()->startOfMonth();
        $totalSpend = 0.0;

        foreach ($activeDeps as $dep) {
            $start = $dep->start_date ? \Carbon\CarbonImmutable::parse($dep->start_date)->startOfMonth() : \Carbon\CarbonImmutable::parse($dep->created_at)->startOfMonth();
            $monthsElapsed = max(1, $start->diffInMonths($now) + 1);
            $durMonths = \App\Http\Controllers\backend\InvesterDashboardController::parseDurationMonths($dep->contract_duration);
            $monthsCount = min($monthsElapsed, $durMonths);
            $totalSpend += ((float) $dep->employee_ctc) * $monthsCount;
        }

        return round($totalSpend, 2);
    }

    /**
     * Remaining Investment Balance (Overall Investment - Total Spend To Date).
     * If total spend exceeds investment, returns a negative value.
     */
    public function getBalanceInvestmentAttribute(): float
    {
        return round($this->total_overall_investment - $this->total_spend_to_date, 2);
    }

    /**
     * Check if investment balance is negative (spend exceeds capital).
     */
    public function getIsBalanceNegativeAttribute(): bool
    {
        return $this->balance_investment < 0;
    }

    /**
     * Total Gross Margin Generated across active deployments (from Contract Report).
     */
    public function getTotalMarginGeneratedAttribute(): float
    {
        return (float) $this->deployments()->where('status', true)->get()->sum(function ($d) {
            $m = $d->getContractReportMetrics();
            return $m['total_revenue'] > 0 ? $m['total_revenue'] : (float) $d->margin_amount;
        });
    }

    /**
     * Total Investor Commission Earnings (e.g. 30% share from Contract Report).
     */
    public function getTotalInvestorEarningsAttribute(): float
    {
        return (float) $this->deployments()->where('status', true)->get()->sum(function ($d) {
            $m = $d->getContractReportMetrics();
            return $m['investor_earned'] > 0 ? $m['investor_earned'] : (float) $d->investor_commission_amount;
        });
    }

    /**
     * Total Szorzo Commission Earnings (e.g. 70% share from Contract Report).
     */
    public function getTotalSzorzoEarningsAttribute(): float
    {
        return (float) $this->deployments()->where('status', true)->get()->sum(function ($d) {
            $m = $d->getContractReportMetrics();
            return $m['szorzo_earned'] > 0 ? $m['szorzo_earned'] : (float) $d->szorzo_commission_amount;
        });
    }

    /**
     * Legal, Compliance & Governance records.
     */
    public function legalCompliances()
    {
        return $this->hasMany(InvesterLegalCompliance::class, 'invester_id');
    }

    /**
     * Generate sequential Invester ID (e.g., SZ INV 0001).
     */
    public static function nextInvesterId(): string
    {
        $prefix = 'SZ INV ';
        try {
            $last = static::withTrashed()
                ->where('invester_id', 'LIKE', $prefix . '%')
                ->orderBy('id', 'desc')
                ->first();

            $nextNumber = 1;

            if ($last && preg_match('/' . preg_quote($prefix, '/') . '(\d+)/', $last->invester_id, $matches)) {
                $nextNumber = (int) $matches[1] + 1;
            }

            return $prefix . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
        } catch (\Throwable $e) {
            return $prefix . '0001';
        }
    }
}
