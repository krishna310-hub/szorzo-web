<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Invester;
use App\Models\InvesterDeployment;
use App\Models\InvesterFinancialDetail;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvesterDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $isSuperAdmin = (int) $user->id === 1 || $user->isSuperAdmin();
        $isInvestorUser = $user->isInvestor() || (int) $user->role_id === 6;
        $linkedInvester = $user->linkedInvester();

        // Security / Scoping check
        if ($isInvestorUser && !$isSuperAdmin) {
            // Investor can only see their own profile
            if (!$linkedInvester) {
                // In case account is created but not linked to investor profile yet
                $linkedInvester = Invester::where('user_id', $user->id)->first();
            }
            $selectedInvesterId = $linkedInvester?->id;
        } else {
            // Admin can choose specific investor or see all
            $selectedInvesterId = $request->filled('invester_id') ? (int) $request->invester_id : null;
        }

        // Date filters
        $selectedFromDate = $request->filled('dashboard_from_date') ? $request->dashboard_from_date : null;
        $selectedToDate = $request->filled('dashboard_to_date') ? $request->dashboard_to_date : null;
        $selectedClientId = $request->filled('client_id') ? (int) $request->client_id : null;
        $selectedMode = $request->filled('mode_of_engagement') ? $request->mode_of_engagement : null;

        $dateFrom = $selectedFromDate ? CarbonImmutable::parse($selectedFromDate)->startOfDay() : null;
        $dateTo = $selectedToDate ? CarbonImmutable::parse($selectedToDate)->endOfDay() : null;

        // Base query for deployments
        $deploymentsQuery = InvesterDeployment::with(['invester', 'employee', 'client'])->latest();

        if ($selectedInvesterId) {
            $deploymentsQuery->where('invester_id', $selectedInvesterId);
        }

        if ($selectedClientId) {
            $deploymentsQuery->where(function ($q) use ($selectedClientId) {
                $q->where('client_id', $selectedClientId)
                    ->orWhereJsonContains('client_ids', (string) $selectedClientId)
                    ->orWhereJsonContains('client_ids', (int) $selectedClientId);
            });
        }

        if ($selectedMode) {
            $deploymentsQuery->where('mode_of_engagement', $selectedMode);
        }

        if ($dateFrom && $dateTo) {
            $deploymentsQuery->where(function ($q) use ($dateFrom, $dateTo) {
                $q->whereBetween('created_at', [$dateFrom, $dateTo])
                    ->orWhereBetween('start_date', [$dateFrom, $dateTo]);
            });
        } elseif ($dateFrom) {
            $deploymentsQuery->where('created_at', '>=', $dateFrom);
        } elseif ($dateTo) {
            $deploymentsQuery->where('created_at', '<=', $dateTo);
        }

        $allDeployments = $deploymentsQuery->get();
        $activeDeployments = $allDeployments->where('status', true);

        // Overall Investment capital query (Financial Details / Tranches)
        $financialsQuery = InvesterFinancialDetail::with('invester')->latest();
        if ($selectedInvesterId) {
            $financialsQuery->where('invester_id', $selectedInvesterId);
        }
        if ($dateFrom && $dateTo) {
            $financialsQuery->where(function ($q) use ($dateFrom, $dateTo) {
                $q->whereBetween('investment_date', [$dateFrom, $dateTo])
                    ->orWhereBetween('created_at', [$dateFrom, $dateTo]);
            });
        }
        $allFinancials = $financialsQuery->get();

        // Base metrics calculations
        $metrics = [
            'overall_investment' => (float) $allFinancials->sum('investment_amount'),
            'total_deployments' => $activeDeployments->count(),
            'total_clients' => $activeDeployments->flatMap(function ($d) {
                $ids = $d->client_ids;
                if (is_string($ids)) {
                    $ids = json_decode($ids, true);
                }
                if (empty($ids) && $d->client_id) {
                    $ids = [$d->client_id];
                }
                return (array) $ids;
            })->filter()->unique()->count(),
            'total_employees' => $activeDeployments->flatMap(function ($d) {
                $ids = $d->employee_ids;
                if (is_string($ids)) {
                    $ids = json_decode($ids, true);
                }
                if (empty($ids) && $d->employee_id) {
                    $ids = [$d->employee_id];
                }
                return (array) $ids;
            })->filter()->unique()->count(),
            'total_rate_card' => (float) $activeDeployments->sum('approved_rate_card'),
            'total_employee_ctc' => (float) $activeDeployments->sum('employee_ctc'),
            'total_margin' => (float) $activeDeployments->sum('margin_amount'),
            'total_szorzo_share' => (float) $activeDeployments->sum('szorzo_commission_amount'),
            'total_investor_share' => (float) $activeDeployments->sum('investor_commission_amount'),
        ];

        // Build month-by-month spend, earnings, and balance investment ledger directly from Contract Report
        $monthlyLedgerData = $this->buildMonthlyLedger(
            $activeDeployments,
            $allFinancials,
            $metrics['overall_investment'],
            $selectedFromDate,
            $selectedToDate
        );

        $currentMonthRow = $monthlyLedgerData['ledger']->firstWhere('is_current', true) ?: $monthlyLedgerData['ledger']->first();

        // Reconcile top metrics with Contract Report exact calculations
        if ($currentMonthRow) {
            $metrics['total_employee_ctc'] = (float) $currentMonthRow['spend'];
            $metrics['total_rate_card'] = (float) $currentMonthRow['spend'];
            $metrics['total_margin'] = (float) $currentMonthRow['gross_margin'];
            $metrics['total_tds'] = (float) ($currentMonthRow['tds'] ?? 0);
            $metrics['total_tds_percentage'] = (float) ($currentMonthRow['tds_percentage'] ?? 10.0);
            $metrics['total_net_margin'] = (float) ($currentMonthRow['net_margin'] ?? 0);
            $metrics['total_investor_share'] = (float) $currentMonthRow['investor_earned'];
            $metrics['total_szorzo_share'] = (float) $currentMonthRow['szorzo_earned'];
            $metrics['monthly_employee_cost'] = (float) $currentMonthRow['spend'];
        } else {
            $metrics['monthly_employee_cost'] = (float) $activeDeployments->sum('employee_ctc');
            $metrics['total_tds'] = 0.0;
            $metrics['total_tds_percentage'] = 10.0;
            $metrics['total_net_margin'] = 0.0;
        }

        $metrics['total_spend_to_date'] = $monthlyLedgerData['total_spend_to_date'];
        $metrics['balance_investment'] = $monthlyLedgerData['balance_investment'];
        $metrics['balance_is_negative'] = $monthlyLedgerData['balance_is_negative'];
        $metrics['balance_deficit_amount'] = $monthlyLedgerData['balance_deficit_amount'];
        $metrics['months_paid_count'] = $monthlyLedgerData['months_paid_count'];
        $metrics['total_projected_spend'] = $monthlyLedgerData['total_projected_spend'];
        $metrics['total_projected_investor_earned'] = $monthlyLedgerData['total_projected_investor_earned'];

        $monthlyLedger = $monthlyLedgerData['ledger'];
        $monthlyLedgerSummary = $monthlyLedgerData;

        // Related Clients Aggregation
        $relatedClients = $activeDeployments->groupBy('client_id')->map(function ($group) {
            $client = $group->first()->client;
            return [
                'client_id' => $group->first()->client_id,
                'client_name' => $client?->client ?? 'Unassigned',
                'employee_count' => $group->count(),
                'total_rate_card' => (float) $group->sum('approved_rate_card'),
                'total_margin' => (float) $group->sum('margin_amount'),
                'investor_share' => (float) $group->sum('investor_commission_amount'),
                'modes' => $group->pluck('mode_of_engagement')->unique()->filter()->values()->all(),
            ];
        })->values();

        // Dropdown data: only list clients assigned to this investor (or deployed clients in consolidated view)
        $availableInvesters = ($isSuperAdmin) ? Invester::where('status', true)->orderBy('name')->get() : collect();

        if ($selectedInvesterId) {
            $invDeployments = InvesterDeployment::where('invester_id', $selectedInvesterId)->get(['client_id', 'client_ids']);
            $assignedClientIds = [];
            foreach ($invDeployments as $dep) {
                if ($dep->client_id) {
                    $assignedClientIds[] = (int) $dep->client_id;
                }
                $ids = $dep->client_ids;
                if (is_string($ids)) {
                    $ids = json_decode($ids, true);
                }
                if (is_array($ids)) {
                    foreach ($ids as $id) {
                        if ($id) {
                            $assignedClientIds[] = (int) $id;
                        }
                    }
                }
            }
            $assignedClientIds = array_values(array_unique(array_filter($assignedClientIds)));
            $availableClients = !empty($assignedClientIds)
                ? Client::whereIn('id', $assignedClientIds)->orderBy('client')->get()
                : collect();
        } else {
            // Consolidated view (All Investors): only list clients that have deployments
            $allDeploymentsForClients = InvesterDeployment::get(['client_id', 'client_ids']);
            $assignedClientIds = [];
            foreach ($allDeploymentsForClients as $dep) {
                if ($dep->client_id) {
                    $assignedClientIds[] = (int) $dep->client_id;
                }
                $ids = $dep->client_ids;
                if (is_string($ids)) {
                    $ids = json_decode($ids, true);
                }
                if (is_array($ids)) {
                    foreach ($ids as $id) {
                        if ($id) {
                            $assignedClientIds[] = (int) $id;
                        }
                    }
                }
            }
            $assignedClientIds = array_values(array_unique(array_filter($assignedClientIds)));
            $availableClients = !empty($assignedClientIds)
                ? Client::whereIn('id', $assignedClientIds)->orderBy('client')->get()
                : Client::where('status', true)->orderBy('client')->get();
        }

        // Current Invester Details if single investor selected
        $currentInvester = $selectedInvesterId ? Invester::find($selectedInvesterId) : null;

        // If Admin is viewing consolidated "All Investors", compile investor breakdown
        $investorBreakdown = collect();
        if ($isSuperAdmin && !$selectedInvesterId) {
            $investorBreakdown = Invester::where('status', true)->get()->map(function ($inv) use ($dateFrom, $dateTo) {
                $deps = $inv->deployments()->where('status', true)->get();
                $fins = $inv->financialDetails()->get();
                $capital = (float) $fins->sum('investment_amount');
                $monthlyCost = (float) $deps->sum('employee_ctc');
                $totalEmployees = $deps->flatMap(fn ($d) => $d->assigned_employees->pluck('id'))->unique()->count();

                $invLedgerData = $this->buildMonthlyLedger($deps, $fins, $capital);
                $totalSpend = $invLedgerData['total_spend_to_date'];
                $balance = $capital - $totalSpend;

                return [
                    'id' => $inv->id,
                    'invester_id' => $inv->invester_id,
                    'name' => $inv->name,
                    'investor_type' => $inv->investor_type,
                    'capital_invested' => $capital,
                    'employees_count' => $totalEmployees,
                    'monthly_cost' => $monthlyCost,
                    'total_spend' => $totalSpend,
                    'balance_investment' => $balance,
                    'balance_is_negative' => $balance < 0,
                    'deployments_count' => $deps->count(),
                    'total_margin' => (float) $deps->sum('margin_amount'),
                    'investor_earnings' => (float) $deps->sum('investor_commission_amount'),
                    'szorzo_earnings' => (float) $deps->sum('szorzo_commission_amount'),
                ];
            });
        }

        return view('backend.invester-dashboard.index', compact(
            'metrics',
            'allDeployments',
            'activeDeployments',
            'allFinancials',
            'relatedClients',
            'availableInvesters',
            'availableClients',
            'selectedInvesterId',
            'selectedClientId',
            'selectedMode',
            'selectedFromDate',
            'selectedToDate',
            'isSuperAdmin',
            'isInvestorUser',
            'currentInvester',
            'investorBreakdown',
            'monthlyLedger',
            'monthlyLedgerSummary'
        ));
    }

    /**
     * Parse duration string to number of months.
     */
    public static function parseDurationMonths(?string $durationStr): int
    {
        if (empty($durationStr)) {
            return 6;
        }
        if (preg_match('/(\d+)\s*year/i', $durationStr, $m)) {
            return (int) $m[1] * 12;
        }
        if (preg_match('/(\d+)\s*month/i', $durationStr, $m)) {
            return (int) $m[1];
        }
        if (preg_match('/(\d+)\s*day/i', $durationStr, $m)) {
            return max(1, (int) ceil((int) $m[1] / 30));
        }
        return 6;
    }

    /**
     * Build month-by-month spend, earnings, and balance investment ledger.
     *
     * @param \Illuminate\Support\Collection $activeDeployments
     * @param \Illuminate\Support\Collection $allFinancials
     * @param float $overallInvestment
     * @param string|null $fromDate
     * @param string|null $toDate
     * @return array
     */
    protected function buildMonthlyLedger(
        $activeDeployments,
        $allFinancials,
        float $overallInvestment,
        ?string $fromDate = null,
        ?string $toDate = null
    ): array {
        if ($activeDeployments->isEmpty() && $allFinancials->isEmpty()) {
            return [
                'ledger' => collect(),
                'total_spend_to_date' => 0.0,
                'balance_investment' => $overallInvestment,
                'balance_is_negative' => $overallInvestment < 0,
                'balance_deficit_amount' => $overallInvestment < 0 ? abs($overallInvestment) : 0.0,
                'months_paid_count' => 0,
                'total_projected_spend' => 0.0,
                'total_projected_investor_earned' => 0.0,
                'total_gross_margin' => 0.0,
                'total_investor_earned' => 0.0,
            ];
        }

        $now = CarbonImmutable::now();
        $currentMonth = $now->startOfMonth();

        // 1. Determine date range
        $candidateDates = collect();

        foreach ($activeDeployments as $dep) {
            if ($dep->start_date) {
                $candidateDates->push(CarbonImmutable::parse($dep->start_date)->startOfMonth());
            } elseif ($dep->created_at) {
                $candidateDates->push(CarbonImmutable::parse($dep->created_at)->startOfMonth());
            }
        }

        foreach ($allFinancials as $fin) {
            if ($fin->investment_date) {
                $candidateDates->push(CarbonImmutable::parse($fin->investment_date)->startOfMonth());
            } elseif ($fin->created_at) {
                $candidateDates->push(CarbonImmutable::parse($fin->created_at)->startOfMonth());
            }
        }

        // Check if candidates have contract reports
        $allCandidateIds = $activeDeployments->flatMap(function ($dep) {
            return $dep->assigned_employees->map(fn ($e) => $e->getMatchingCandidate()?->id)->filter();
        })->unique();

        if ($allCandidateIds->isNotEmpty()) {
            try {
                $reportDates = \App\Models\ContractReport::whereIn('candidate_id', $allCandidateIds)
                    ->whereNotNull('salary_month')
                    ->pluck('salary_month')
                    ->map(fn ($d) => CarbonImmutable::parse($d)->startOfMonth());
                $candidateDates = $candidateDates->concat($reportDates);
            } catch (\Throwable $e) {
                // Ignore DB error if table not queried
            }
        }

        $earliestMonth = $candidateDates->filter()->min();
        if (!$earliestMonth) {
            $earliestMonth = $currentMonth;
        }

        // Limit earliest month to not exceed 24 months in past unless filtered
        if (!$fromDate && $earliestMonth->lt($currentMonth->subMonths(24))) {
            $earliestMonth = $currentMonth->subMonths(24);
        }

        // Determine end month: latest deployment end date or projected duration, at least current month
        $projectedEndDates = collect([$currentMonth]);
        foreach ($activeDeployments as $dep) {
            if ($dep->end_date) {
                $projectedEndDates->push(CarbonImmutable::parse($dep->end_date)->startOfMonth());
            } else {
                $depStart = $dep->start_date ? CarbonImmutable::parse($dep->start_date)->startOfMonth() : CarbonImmutable::parse($dep->created_at)->startOfMonth();
                $durMonths = self::parseDurationMonths($dep->contract_duration);
                $projectedEndDates->push($depStart->addMonths(max(1, $durMonths - 1))->startOfMonth());
            }
        }
        $latestMonth = $projectedEndDates->filter()->max() ?: $currentMonth;

        if ($fromDate) {
            $rangeStart = CarbonImmutable::parse($fromDate)->startOfMonth();
        } else {
            $rangeStart = $earliestMonth;
        }

        if ($toDate) {
            $rangeEnd = CarbonImmutable::parse($toDate)->startOfMonth();
        } else {
            $rangeEnd = $latestMonth;
        }

        // Prevent inverted range
        if ($rangeStart->gt($rangeEnd)) {
            $rangeStart = $rangeEnd;
        }

        // Preload reports grouped by candidate_id and Y-m for fast lookup
        $reportsByCandAndMonth = collect();
        if ($allCandidateIds->isNotEmpty()) {
            try {
                $reports = \App\Models\ContractReport::whereIn('candidate_id', $allCandidateIds)
                    ->whereBetween('salary_month', [$rangeStart->toDateString(), $rangeEnd->endOfMonth()->toDateString()])
                    ->get();
                $reportsByCandAndMonth = $reports->groupBy(function ($r) {
                    return $r->candidate_id . '_' . CarbonImmutable::parse($r->salary_month)->format('Y-m');
                });
            } catch (\Throwable $e) {
                // Continue without reports
            }
        }

        $ledger = collect();
        $cumulativeSpend = 0.0;
        $cumulativeInvestorEarned = 0.0;
        $cumulativeGrossMargin = 0.0;
        $cumulativeSzorzoEarned = 0.0;
        $totalSpendToDate = 0.0;
        $elapsedMonthsCount = 0;

        $monthCursor = $rangeStart;
        $monthIndex = 1;

        while ($monthCursor->lte($rangeEnd)) {
            $monthKey = $monthCursor->format('Y-m');
            $monthLabel = $monthCursor->format('F Y');
            $isPast = $monthCursor->endOfMonth()->isPast();
            $isCurrent = $monthCursor->format('Y-m') === $currentMonth->format('Y-m');
            $isFuture = $monthCursor->gt($currentMonth);

            $monthSpend = 0.0;
            $monthMargin = 0.0;
            $monthTds = 0.0;
            $monthNetMargin = 0.0;
            $monthInvestorEarned = 0.0;
            $monthSzorzoEarned = 0.0;
            $monthEmployees = collect();

            foreach ($activeDeployments as $dep) {
                $depStart = $dep->start_date ? CarbonImmutable::parse($dep->start_date)->startOfMonth() : CarbonImmutable::parse($dep->created_at)->startOfMonth();
                $depEnd = null;
                if ($dep->end_date) {
                    $depEnd = CarbonImmutable::parse($dep->end_date)->endOfMonth();
                } else {
                    $durMonths = self::parseDurationMonths($dep->contract_duration);
                    $depEnd = $depStart->addMonths($durMonths)->subDay()->endOfMonth();
                }

                // Check if deployment is active in this month
                if ($monthCursor->lt($depStart) || $monthCursor->gt($depEnd)) {
                    continue;
                }

                $assignedEmps = $dep->assigned_employees;
                $depSpend = 0.0;
                $depMargin = 0.0;

                if ($assignedEmps->isNotEmpty()) {
                    foreach ($assignedEmps as $emp) {
                        $cand = $emp->getMatchingCandidate();
                        $candSalary = 0.0;
                        $candBillingPct = 0.0;

                        if ($cand) {
                            $candBillingPct = (float) ($cand->clientRequirement?->billing?->value ?? $cand->client?->billing?->value ?? 0.0);
                            $lookupKey = $cand->id . '_' . $monthKey;
                            if (isset($reportsByCandAndMonth[$lookupKey])) {
                                $report = $reportsByCandAndMonth[$lookupKey]->first();
                                $candSalary = (float) ($report->payable_salary > 0 ? $report->payable_salary : $report->monthly_take_home);
                            }
                        }

                        if ($candBillingPct <= 0) {
                            $candBillingPct = (float) ($emp->getClientRequirementBillingPercentage() ?? 15.0);
                        }

                        if ($candSalary <= 0) {
                            $salData = $emp->getContractReportSalary($monthCursor);
                            $candSalary = (float) $salData['salary'];
                            $candBillingPct = (float) ($salData['billing_percentage'] ?? $candBillingPct);
                        }

                        if ($candSalary <= 0 && $dep->employee_ctc > 0) {
                            $candSalary = round((float) $dep->employee_ctc / $assignedEmps->count(), 2);
                        }

                        // Contract Revenue formula from ContractReport: Total Salary * Billing %
                        $candRevenue = round($candSalary * ($candBillingPct / 100), 2);

                        $depSpend += $candSalary;
                        $depMargin += $candRevenue;

                        $monthEmployees->push([
                            'id' => $emp->id,
                            'name' => $emp->employee_name,
                            'salary' => $candSalary,
                            'billing_percentage' => $candBillingPct,
                            'revenue' => $candRevenue,
                        ]);
                    }
                } else {
                    $depSpend = (float) $dep->employee_ctc;
                    $marginPct = (float) ($dep->margin_percentage ?: 15.0);
                    $depMargin = (float) $dep->margin_amount > 0 ? (float) $dep->margin_amount : round($depSpend * ($marginPct / 100), 2);
                }

                if ($depSpend <= 0 && (float) $dep->employee_ctc > 0) {
                    $depSpend = (float) $dep->employee_ctc;
                }
                if ($depMargin <= 0 && (float) $dep->margin_amount > 0) {
                    $depMargin = (float) $dep->margin_amount;
                }

                $szPct = (float) ($dep->szorzo_share_percentage ?: 70.0);
                $invPct = (float) ($dep->investor_share_percentage ?: 30.0);
                $tdsPct = isset($dep->tds_percentage) && $dep->tds_percentage !== null && $dep->tds_percentage !== ''
                    ? (float) $dep->tds_percentage
                    : 10.0;

                // Deduct TDS from Gross Margin (less TDS)
                $depTds = round($depMargin * ($tdsPct / 100), 2);
                $depNetMargin = max(0.0, round($depMargin - $depTds, 2));

                $depInvEarned = round($depNetMargin * ($invPct / 100), 2);
                $depSzEarned = round($depNetMargin * ($szPct / 100), 2);

                $monthSpend += $depSpend;
                $monthMargin += $depMargin;
                $monthTds += $depTds;
                $monthNetMargin += $depNetMargin;
                $monthInvestorEarned += $depInvEarned;
                $monthSzorzoEarned += $depSzEarned;
            }

            // Running calculations
            $cumulativeSpend += $monthSpend;
            $cumulativeInvestorEarned += $monthInvestorEarned;
            $cumulativeGrossMargin += $monthMargin;
            $cumulativeSzorzoEarned += $monthSzorzoEarned;

            $balance = round($overallInvestment - $cumulativeSpend, 2);

            // Track spend to date (elapsed + current months)
            if (!$isFuture) {
                $totalSpendToDate = $cumulativeSpend;
                $elapsedMonthsCount++;
            }

            $ledger->push([
                'month_index' => $monthIndex,
                'month_key' => $monthKey,
                'month_label' => $monthLabel,
                'month_date' => $monthCursor,
                'is_past' => $isPast,
                'is_current' => $isCurrent,
                'is_future' => $isFuture,
                'employees_count' => $monthEmployees->unique('id')->count(),
                'employees_detail' => $monthEmployees->unique('id')->values()->all(),
                'spend' => $monthSpend,
                'gross_margin' => $monthMargin,
                'tds_percentage' => $monthMargin > 0 ? round(($monthTds / $monthMargin) * 100, 2) : 10.0,
                'tds' => $monthTds,
                'net_margin' => $monthNetMargin,
                'investor_earned' => $monthInvestorEarned,
                'szorzo_earned' => $monthSzorzoEarned,
                'cumulative_spend' => $cumulativeSpend,
                'cumulative_investor_earned' => $cumulativeInvestorEarned,
                'cumulative_gross_margin' => $cumulativeGrossMargin,
                'balance_investment' => $balance,
                'is_balance_negative' => $balance < 0,
                'status_label' => $isCurrent ? 'Current Month' : ($isPast ? 'Paid / Processed' : 'Projected'),
                'status_class' => $isCurrent ? 'primary' : ($isPast ? 'success' : 'info'),
            ]);

            $monthCursor = $monthCursor->addMonth();
            $monthIndex++;
        }

        // If no past months occurred, total spend to date is at least first month spend or 0
        if ($elapsedMonthsCount === 0 && $ledger->isNotEmpty()) {
            $totalSpendToDate = (float) $ledger->first()['spend'];
            $elapsedMonthsCount = 1;
        }

        $balanceInvestment = round($overallInvestment - $totalSpendToDate, 2);

        return [
            'ledger' => $ledger,
            'total_spend_to_date' => $totalSpendToDate,
            'balance_investment' => $balanceInvestment,
            'balance_is_negative' => $balanceInvestment < 0,
            'balance_deficit_amount' => $balanceInvestment < 0 ? abs($balanceInvestment) : 0.0,
            'months_paid_count' => $elapsedMonthsCount,
            'total_projected_spend' => $cumulativeSpend,
            'total_projected_investor_earned' => $cumulativeInvestorEarned,
            'total_gross_margin' => $cumulativeGrossMargin,
            'total_investor_earned' => $cumulativeInvestorEarned,
        ];
    }
}

