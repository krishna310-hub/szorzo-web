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

        // Metrics calculations
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

        // Dropdown data
        $availableInvesters = ($isSuperAdmin) ? Invester::where('status', true)->orderBy('name')->get() : collect();
        $availableClients = Client::where('status', true)->orderBy('client')->get();

        // Current Invester Details if single investor selected
        $currentInvester = $selectedInvesterId ? Invester::find($selectedInvesterId) : null;

        // If Admin is viewing consolidated "All Investors", compile investor breakdown
        $investorBreakdown = collect();
        if ($isSuperAdmin && !$selectedInvesterId) {
            $investorBreakdown = Invester::where('status', true)->get()->map(function ($inv) {
                $deps = $inv->deployments()->where('status', true)->get();
                return [
                    'id' => $inv->id,
                    'invester_id' => $inv->invester_id,
                    'name' => $inv->name,
                    'investor_type' => $inv->investor_type,
                    'capital_invested' => (float) $inv->financialDetails()->sum('investment_amount'),
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
            'investorBreakdown'
        ));
    }
}

