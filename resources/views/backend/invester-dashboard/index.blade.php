@extends('backend.layouts.master')
@section('title', 'Investor Dashboard')

@section('content')
<style>
    .inv-hero-box {
        border-radius: 20px;
        background: linear-gradient(135deg, #18080c 0%, #2b0c12 50%, #ea1c26 100%);
        color: #fff;
        padding: 28px;
        box-shadow: 0 12px 30px rgba(234, 28, 38, 0.22);
        position: relative;
        overflow: hidden;
    }
    .inv-hero-box::after {
        content: '';
        position: absolute;
        width: 280px;
        height: 280px;
        right: -60px;
        top: -80px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.08);
        pointer-events: none;
    }
    .inv-stat-card {
        border: 0;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
        transition: transform 0.2s, box-shadow 0.2s;
        height: 100%;
        overflow: hidden;
    }
    .inv-stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }
    .inv-stat-icon {
        width: 54px;
        height: 54px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
    }
    .margin-highlight-box {
        background: #fff9c4 !important;
        border: 2px solid #ecd83c !important;
        color: #212529 !important;
    }
    .avatar-sm-circle {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        object-fit: cover;
    }
</style>

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">

            <!-- Breadcrumb Header -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Investor Portfolio Dashboard</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('admin.investers.index') }}">Investers</a></li>
                                <li class="breadcrumb-item active">Dashboard</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            @include('backend.investers.partials.nav-buttons')

            <!-- Hero Welcome Card -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="inv-hero-box">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                            <div>
                                <span class="badge bg-white text-danger fw-bold px-3 py-1 mb-2 fs-12">
                                    <i class="ri-funds-line me-1"></i> SZORZO INVESTOR NETWORK
                                </span>
                                <h2 class="text-white fw-bold mb-1">
                                    @if($currentInvester)
                                        {{ $currentInvester->name }} Portfolio
                                    @else
                                        Consolidated Investor Overview
                                    @endif
                                </h2>
                                <p class="text-white-50 mb-0">
                                    @if($currentInvester)
                                        Investor ID: <strong>{{ $currentInvester->invester_id }}</strong> | Type: <strong>{{ $currentInvester->investor_type ?: 'General Investor' }}</strong>
                                    @else
                                        Viewing aggregated performance, deployed talent, client billings, and profit commissions.
                                    @endif
                                </p>
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                @if($currentInvester)
                                    <a href="{{ route('admin.investers.show', $currentInvester->id) }}" class="btn btn-light text-dark fw-semibold">
                                        <i class="ri-eye-line me-1"></i> View Full Profile
                                    </a>
                                @endif
                                @can('create', \App\Models\InvesterDeployment::class)
                                    <a href="{{ route('admin.invester-deployments.create') }}{{ $currentInvester ? '?invester_id=' . $currentInvester->id : '' }}" class="btn btn-danger fw-semibold">
                                        <i class="ri-add-line me-1"></i> New Invest Module
                                    </a>
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Controls Card (Point 4 & Point 5: Admin selects investor / same options like admin dashboard) -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border">
                        <div class="card-header bg-light py-2">
                            <h6 class="card-title mb-0 fs-13"><i class="ri-filter-3-line me-1 text-primary"></i> Dashboard Filters</h6>
                        </div>
                        <div class="card-body">
                            <form method="GET" action="{{ route('admin.investers.dashboard') }}" id="dashboardFilterForm">
                                <div class="row g-3 align-items-end">
                                    @if($isSuperAdmin)
                                        <!-- Point 5: Admin see all investers data choose based give -->
                                        <div class="col-md-3">
                                            <label class="form-label fw-semibold small">Choose Investor</label>
                                            <select name="invester_id" class="form-select">
                                                <option value="">All Investors (Consolidated Portfolio)</option>
                                                @foreach($availableInvesters as $inv)
                                                    <option value="{{ $inv->id }}" {{ (string)$selectedInvesterId === (string)$inv->id ? 'selected' : '' }}>
                                                        {{ $inv->name }} ({{ $inv->invester_id }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif

                                    <div class="col-md-{{ $currentInvester ? '4' : '3' }}">
                                        <label class="form-label fw-semibold small">Filter by Client</label>
                                        <select name="client_id" class="form-select">
                                            <option value="">All Deployed Clients</option>
                                            @foreach($availableClients as $cli)
                                                <option value="{{ $cli->id }}" {{ (string)$selectedClientId === (string)$cli->id ? 'selected' : '' }}>
                                                    {{ $cli->client }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-{{ $currentInvester ? '4' : '3' }}">
                                        <label class="form-label fw-semibold small">From Date</label>
                                        <input type="date" name="dashboard_from_date" class="form-control" value="{{ $selectedFromDate }}">
                                    </div>

                                    <div class="col-md-{{ $currentInvester ? '4' : '3' }}">
                                        <label class="form-label fw-semibold small">To Date</label>
                                        <input type="date" name="dashboard_to_date" class="form-control" value="{{ $selectedToDate }}">
                                    </div>

                                    <div class="col-12 d-flex gap-2 justify-content-end pt-2">
                                        <button type="submit" class="btn btn-primary btn-sm px-4">
                                            <i class="ri-filter-line me-1"></i> Apply Filters
                                        </button>
                                        <a href="{{ route('admin.investers.dashboard') }}" class="btn btn-outline-secondary btn-sm px-3">
                                            <i class="ri-refresh-line me-1"></i> Reset
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Row 1: Capital Spend, Balance & Mapped Talent (4 New Cards) -->
            <div class="row g-3 mb-3">
                <!-- Card 1: Total Employees Count -->
                <div class="col-xl-3 col-md-6">
                    <div class="card inv-stat-card border-top border-3 border-primary shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="text-muted text-uppercase fw-bold fs-11">Total Employees Count</span>
                                    <h3 class="fw-bold text-dark mt-2 mb-0">{{ $metrics['total_employees'] }} <span class="fs-14 fw-normal text-muted">Talent</span></h3>
                                    <small class="text-primary fw-medium mt-1 d-block">
                                        <i class="ri-user-shared-line me-1"></i> Mapped External Employees
                                    </small>
                                </div>
                                <div class="inv-stat-icon bg-primary-subtle text-primary">
                                    <i class="ri-team-line"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Monthly Employee Cost / Spend -->
                <div class="col-xl-3 col-md-6">
                    <div class="card inv-stat-card border-top border-3 border-info shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="text-muted text-uppercase fw-bold fs-11">Monthly Employee Cost</span>
                                    <h3 class="fw-bold text-dark mt-2 mb-0">₹ {{ number_format($metrics['monthly_employee_cost'], 2) }}</h3>
                                    <small class="text-muted mt-1 d-block">
                                        <i class="ri-calendar-check-line me-1"></i> Monthly Salary Commitment
                                    </small>
                                </div>
                                <div class="inv-stat-icon bg-info-subtle text-info">
                                    <i class="ri-calendar-todo-line"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 3: Total Spend for Mapped Employees -->
                <div class="col-xl-3 col-md-6">
                    <div class="card inv-stat-card border-top border-3 border-warning shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="text-muted text-uppercase fw-bold fs-11">Total Employee Spend</span>
                                    <h3 class="fw-bold text-dark mt-2 mb-0">₹ {{ number_format($metrics['total_spend_to_date'], 2) }}</h3>
                                    <small class="text-muted mt-1 d-block">
                                        <i class="ri-funds-line me-1"></i> Cumulative Paid Across {{ $metrics['months_paid_count'] }} Month(s)
                                    </small>
                                </div>
                                <div class="inv-stat-icon bg-warning-subtle text-warning">
                                    <i class="ri-exchange-dollar-line"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 4: Balance Investment (Shows - minus when spend exceeds investment) -->
                <div class="col-xl-3 col-md-6">
                    @if($metrics['balance_investment'] < 0)
                        <div class="card inv-stat-card border-top border-3 border-danger shadow-sm h-100" style="background: #fff5f5;">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <span class="text-danger text-uppercase fw-bold fs-11">Balance Investment</span>
                                        <h3 class="fw-bold text-danger mt-2 mb-0">- ₹ {{ number_format(abs($metrics['balance_investment']), 2) }}</h3>
                                        <small class="text-danger mt-1 d-block fw-semibold">
                                            <i class="ri-error-warning-fill me-1"></i> Capital Exceeded by ₹ {{ number_format(abs($metrics['balance_investment']), 2) }}
                                        </small>
                                    </div>
                                    <div class="inv-stat-icon bg-danger text-white shadow-sm">
                                        <i class="ri-alert-line"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="card inv-stat-card border-top border-3 border-success shadow-sm h-100" style="background: #f0fdf4;">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <span class="text-success text-uppercase fw-bold fs-11">Balance Investment</span>
                                        <h3 class="fw-bold text-success mt-2 mb-0">₹ {{ number_format($metrics['balance_investment'], 2) }}</h3>
                                        <small class="text-success mt-1 d-block fw-medium">
                                            <i class="ri-checkbox-circle-fill me-1"></i> Capital Buffer Available
                                        </small>
                                    </div>
                                    <div class="inv-stat-icon bg-success-subtle text-success">
                                        <i class="ri-wallet-3-line"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Row 2: Investment Capital & Deal Economics (Existing Metrics) -->
            <div class="row g-3 mb-4">
                <!-- Overall Investment (Right side of photo) -->
                <div class="col-xl-3 col-md-6">
                    <div class="card inv-stat-card border-top border-3 border-secondary">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="text-muted text-uppercase fw-bold fs-11">Overall Investment</span>
                                    <h3 class="fw-bold text-dark mt-2 mb-0">₹ {{ number_format($metrics['overall_investment'], 2) }}</h3>
                                    <small class="text-muted mt-1 d-block">{{ $allFinancials->count() }} Tranches Recorded</small>
                                </div>
                                <div class="inv-stat-icon bg-secondary-subtle text-secondary">
                                    <i class="ri-bank-card-line"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active Deployed Employees -->
                <div class="col-xl-3 col-md-6">
                    <div class="card inv-stat-card border-top border-3 border-primary">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="text-muted text-uppercase fw-bold fs-11">Active Deployments</span>
                                    <h3 class="fw-bold text-dark mt-2 mb-0">{{ $metrics['total_deployments'] }}</h3>
                                    <small class="text-primary fw-medium mt-1 d-block">{{ $metrics['total_clients'] }} Clients Deployed To</small>
                                </div>
                                <div class="inv-stat-icon bg-primary-subtle text-primary">
                                    <i class="ri-team-line"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gross Margin Generated (Yellow Box in Excel photo) -->
                <div class="col-xl-3 col-md-6">
                    <div class="card inv-stat-card border-top border-3 border-warning">
                        <div class="card-body margin-highlight-box rounded-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="text-dark text-uppercase fw-bold fs-11">Margin In Rupees</span>
                                    <h3 class="fw-bold text-dark mt-2 mb-0">₹ {{ number_format($metrics['total_margin'], 2) }}</h3>
                                    <small class="text-dark-50 mt-1 d-block">Monthly Total Margin</small>
                                </div>
                                <div class="inv-stat-icon bg-white text-dark shadow-sm">
                                    <i class="ri-money-rupee-circle-fill"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Investor Profit Share (30% in photo) -->
                <div class="col-xl-3 col-md-6">
                    <div class="card inv-stat-card border-top border-3 border-success">
                        <div class="card-body" style="background: #e8f5e9;">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="text-success text-uppercase fw-bold fs-11">Investor Share (30%)</span>
                                    <h3 class="fw-bold text-success mt-2 mb-0">₹ {{ number_format($metrics['total_investor_share'], 2) }}</h3>
                                    <small class="text-muted mt-1 d-block">SZ Share (70%): ₹ {{ number_format($metrics['total_szorzo_share'], 2) }}</small>
                                </div>
                                <div class="inv-stat-icon bg-success text-white shadow-sm">
                                    <i class="ri-hand-coin-line"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profit Sharing Model Visual Card -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border">
                        <div class="card-body p-3">
                            <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
                                <h6 class="mb-0 fw-bold"><i class="ri-pie-chart-2-line text-primary me-1"></i> Profit Sharing Model Split (Monthly)</h6>
                                <span class="badge bg-secondary-subtle text-secondary fs-12">Based on Contract Report Revenue</span>
                            </div>
                            @php
                                $totalComms = $metrics['total_szorzo_share'] + $metrics['total_investor_share'];
                                $szPct = $totalComms > 0 ? round(($metrics['total_szorzo_share'] / $totalComms) * 100) : 70;
                                $invPct = 100 - $szPct;
                            @endphp
                            <div class="progress" style="height: 18px; border-radius: 9px;">
                                <div class="progress-bar bg-primary fw-bold" role="progressbar" style="width: {{ $szPct }}%;" aria-valuenow="{{ $szPct }}" aria-valuemin="0" aria-valuemax="100">
                                    Szorzo (SZ) {{ $szPct }}% — ₹ {{ number_format($metrics['total_szorzo_share'], 2) }}
                                </div>
                                <div class="progress-bar bg-success fw-bold" role="progressbar" style="width: {{ $invPct }}%;" aria-valuenow="{{ $invPct }}" aria-valuemin="0" aria-valuemax="100">
                                    Investor {{ $invPct }}% — ₹ {{ number_format($metrics['total_investor_share'], 2) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Monthly Capital Spend & Earnings Ledger (Point 1: Monthly list for spend and earned) -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border">
                        <div class="card-header bg-light d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
                            <div>
                                <h5 class="card-title mb-0">
                                    <i class="ri-calendar-todo-line me-1 text-primary"></i> Monthly Spend & Earned Ledger
                                </h5>
                                <small class="text-muted">Month-by-month list tracking mapped external talent salaries funded (spend), profit sharing earned, and remaining investment balance.</small>
                            </div>
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <span class="badge bg-light text-dark border fs-12">
                                    Overall Capital: <strong>₹ {{ number_format($metrics['overall_investment'], 2) }}</strong>
                                </span>
                                @if($metrics['balance_investment'] < 0)
                                    <span class="badge bg-danger text-white fs-12">
                                        <i class="ri-alert-line me-1"></i> Balance: - ₹ {{ number_format(abs($metrics['balance_investment']), 2) }} (Exceeded)
                                    </span>
                                @else
                                    <span class="badge bg-success-subtle text-success fs-12">
                                        <i class="ri-checkbox-circle-line me-1"></i> Balance: ₹ {{ number_format($metrics['balance_investment'], 2) }}
                                    </span>
                                @endif
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()" title="Print Ledger">
                                    <i class="ri-printer-line me-1"></i> Print
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered align-middle mb-0" id="monthlyLedgerTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 50px;">#</th>
                                            <th>Month & Year</th>
                                            <th>Mapped External Employees</th>
                                            <th>Monthly Spend (Salary Paid)</th>
                                            <th>Gross Margin Generated</th>
                                            <th>Monthly Earned (Investor Share)</th>
                                            <th>Cumulative Spend</th>
                                            <th>Cumulative Earned</th>
                                            <th class="table-warning">Balance Investment</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($monthlyLedger as $row)
                                            <tr class="{{ $row['is_current'] ? 'table-primary-subtle' : '' }}">
                                                <td class="fw-semibold text-muted text-center">{{ $row['month_index'] }}</td>
                                                <td>
                                                    <strong>{{ $row['month_label'] }}</strong>
                                                    @if($row['is_current'])
                                                        <span class="badge bg-primary text-white ms-1 fs-10">Current</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge bg-primary-subtle text-primary">
                                                        <i class="ri-team-line me-1"></i> {{ $row['employees_count'] }} Employees
                                                    </span>
                                                    @if(!empty($row['employees_detail']))
                                                        <div class="small text-muted mt-1">
                                                            @foreach(array_slice($row['employees_detail'], 0, 4) as $ed)
                                                                <span class="d-inline-block me-2">• {{ $ed['name'] }} (₹{{ number_format($ed['salary'], 2) }} @ {{ number_format($ed['billing_percentage'] ?? 15, 1) }}%)</span>
                                                            @endforeach
                                                            @if(count($row['employees_detail']) > 4)
                                                                <span class="text-muted">+{{ count($row['employees_detail']) - 4 }} more</span>
                                                            @endif
                                                        </div>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="fw-bold text-dark">₹ {{ number_format($row['spend'], 2) }}</span>
                                                </td>
                                                <td>
                                                    <span class="text-dark">₹ {{ number_format($row['gross_margin'], 2) }}</span>
                                                    <small class="text-muted d-block fs-11">SZ: ₹ {{ number_format($row['szorzo_earned'], 2) }}</small>
                                                </td>
                                                <td>
                                                    <span class="fw-bold text-success fs-6">₹ {{ number_format($row['investor_earned'], 2) }}</span>
                                                </td>
                                                <td>
                                                    <span class="fw-semibold text-dark">₹ {{ number_format($row['cumulative_spend'], 2) }}</span>
                                                </td>
                                                <td>
                                                    <span class="fw-semibold text-success">₹ {{ number_format($row['cumulative_investor_earned'], 2) }}</span>
                                                </td>
                                                <td class="table-warning">
                                                    @if($row['balance_investment'] < 0)
                                                        <span class="fw-bold text-danger fs-6">- ₹ {{ number_format(abs($row['balance_investment']), 2) }}</span>
                                                        <span class="badge bg-danger text-white ms-1 fs-10">Exceeded</span>
                                                    @else
                                                        <span class="fw-bold text-success fs-6">₹ {{ number_format($row['balance_investment'], 2) }}</span>
                                                        <span class="badge bg-success-subtle text-success ms-1 fs-10">Funded</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge bg-{{ $row['status_class'] }}-subtle text-{{ $row['status_class'] }}">
                                                        {{ $row['status_label'] }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="10" class="text-center text-muted py-4">
                                                    <i class="ri-information-line fs-18 d-block mb-1"></i>
                                                    No monthly spend or earnings data recorded yet. Deploy employees to start tracking monthly salary spend and profit share.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    @if($monthlyLedger->isNotEmpty())
                                        <tfoot class="table-light fw-bold">
                                            <tr>
                                                <td colspan="3" class="text-end">Ledger Totals:</td>
                                                <td class="text-dark">₹ {{ number_format($monthlyLedgerSummary['total_projected_spend'], 2) }}</td>
                                                <td class="text-dark">₹ {{ number_format($monthlyLedgerSummary['total_gross_margin'], 2) }}</td>
                                                <td class="text-success">₹ {{ number_format($monthlyLedgerSummary['total_projected_investor_earned'], 2) }}</td>
                                                <td class="text-dark">₹ {{ number_format($monthlyLedgerSummary['total_spend_to_date'], 2) }} (To Date)</td>
                                                <td class="text-success">₹ {{ number_format($monthlyLedgerSummary['total_investor_earned'], 2) }}</td>
                                                <td class="table-warning">
                                                    @if($metrics['balance_investment'] < 0)
                                                        <span class="text-danger fs-6">- ₹ {{ number_format(abs($metrics['balance_investment']), 2) }}</span>
                                                    @else
                                                        <span class="text-success fs-6">₹ {{ number_format($metrics['balance_investment'], 2) }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($metrics['balance_investment'] < 0)
                                                        <span class="badge bg-danger text-white">Deficit</span>
                                                    @else
                                                        <span class="badge bg-success text-white">Surplus</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        </tfoot>
                                    @endif
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- If Admin is viewing Consolidated Overview: Show Investers Breakdown Table (Point 5) -->
            @if($isSuperAdmin && !$selectedInvesterId && $investorBreakdown->isNotEmpty())
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card border">
                            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">
                                    <i class="ri-user-star-line me-1 text-primary"></i> Investors Performance Breakdown
                                </h5>
                                <span class="badge bg-primary fs-12">{{ $investorBreakdown->count() }} Investors</span>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Investor ID</th>
                                                <th>Investor Name</th>
                                                <th>Investor Type</th>
                                                <th>Capital Invested</th>
                                                <th>Mapped Talent</th>
                                                <th>Monthly Cost</th>
                                                <th>Total Spend</th>
                                                <th>Balance Investment</th>
                                                <th>Margin Generated</th>
                                                <th>Investor Share (30%)</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($investorBreakdown as $ib)
                                                <tr>
                                                    <td class="fw-semibold">{{ $ib['invester_id'] }}</td>
                                                    <td>
                                                        <a href="{{ route('admin.investers.dashboard') }}?invester_id={{ $ib['id'] }}" class="fw-bold text-primary">
                                                            {{ $ib['name'] }}
                                                        </a>
                                                    </td>
                                                    <td><span class="badge bg-secondary-subtle text-secondary">{{ $ib['investor_type'] ?: 'Angel Investor' }}</span></td>
                                                    <td class="fw-bold text-dark">₹ {{ number_format($ib['capital_invested'], 2) }}</td>
                                                    <td><span class="badge bg-primary-subtle text-primary">{{ $ib['employees_count'] ?? $ib['deployments_count'] }} Employees</span></td>
                                                    <td class="fw-bold text-info-emphasis">₹ {{ number_format($ib['monthly_cost'] ?? 0, 2) }}</td>
                                                    <td class="fw-bold text-dark">₹ {{ number_format($ib['total_spend'] ?? 0, 2) }}</td>
                                                    <td>
                                                        @if(!empty($ib['balance_is_negative']))
                                                            <span class="fw-bold text-danger">- ₹ {{ number_format(abs($ib['balance_investment']), 2) }}</span>
                                                            <span class="badge bg-danger text-white ms-1 fs-10">Exceeded</span>
                                                        @else
                                                            <span class="fw-bold text-success">₹ {{ number_format($ib['balance_investment'] ?? 0, 2) }}</span>
                                                        @endif
                                                    </td>
                                                    <td class="fw-bold text-warning-emphasis">₹ {{ number_format($ib['total_margin'], 2) }}</td>
                                                    <td class="fw-bold text-success">₹ {{ number_format($ib['investor_earnings'], 2) }}</td>
                                                    <td>
                                                        <a href="{{ route('admin.investers.dashboard') }}?invester_id={{ $ib['id'] }}" class="btn btn-sm btn-outline-primary" title="View Specific Dashboard">
                                                            <i class="ri-dashboard-2-line me-1"></i> Dashboard
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Section: Related Deployed Employee List (Point 4: with image, rate card, margin, commissions) -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border">
                        <div class="card-header bg-light d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <div>
                                <h5 class="card-title mb-0">
                                    <i class="ri-team-line me-1 text-primary"></i> Deployed Employees Funded by Investor
                                </h5>
                                <small class="text-muted">Directly reflecting employment details, rate card, margin %, and commission split</small>
                            </div>
                            <span class="badge bg-primary fs-12">{{ $activeDeployments->count() }} Active Deployments</span>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Employee</th>
                                            @if(!$currentInvester)
                                                <th>Investor</th>
                                            @endif
                                            <th>Deployed To (Client)</th>
                                            <th>Contract Duration</th>
                                            <th>Employee CTC (₹)</th>
                                            <th>Margin %</th>
                                            <th class="table-warning">Margin In Rupees</th>
                                            <th>Investor Share (30%)</th>
                                            <th>SZ Share (70%)</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($allDeployments as $dep)
                                            @php
                                                $depEmps = $dep->assigned_employees;
                                                $depClients = $dep->assigned_clients;
                                            @endphp
                                            <tr>
                                                <td>
                                                    @if($depEmps->count() > 1)
                                                        <div>
                                                            <span class="badge bg-primary-subtle text-primary mb-1">
                                                                <i class="ri-team-line me-1"></i> {{ $depEmps->count() }} External Employees
                                                            </span>
                                                            <small class="text-muted d-block">{{ Str::limit($dep->employee_names, 40) }}</small>
                                                        </div>
                                                    @else
                                                        <div class="d-flex align-items-center gap-2">
                                                            @if($dep->employee && $dep->employee->employee_image && file_exists(public_path($dep->employee->employee_image)))
                                                                <img src="{{ asset($dep->employee->employee_image) }}" class="avatar-sm-circle border" alt="">
                                                            @else
                                                                <div class="avatar-sm-circle bg-primary-subtle text-primary fw-bold d-flex align-items-center justify-content-center">
                                                                    {{ strtoupper(substr($dep->employee?->employee_name ?? 'E', 0, 1)) }}
                                                                </div>
                                                            @endif
                                                            <div>
                                                                <span class="fw-bold d-block">{{ $dep->employee?->employee_name ?? 'Unassigned' }}</span>
                                                                <small class="text-muted">{{ $dep->employee?->designation ?: 'Staff' }} ({{ $dep->employee?->employee_no ?: '-' }})</small>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </td>
                                                @if(!$currentInvester)
                                                    <td>
                                                        <span class="badge bg-primary-subtle text-primary">{{ $dep->invester?->name ?? '-' }}</span>
                                                    </td>
                                                @endif
                                                <td>
                                                    @if($depClients->count() > 1)
                                                        <div>
                                                            <span class="badge bg-info-subtle text-info mb-1">
                                                                <i class="ri-building-line me-1"></i> {{ $depClients->count() }} Clients
                                                            </span>
                                                            <small class="text-muted d-block">{{ Str::limit($dep->client_names, 35) }}</small>
                                                        </div>
                                                    @else
                                                        <span class="fw-semibold text-dark">{{ $dep->client?->client ?? 'Unassigned' }}</span>
                                                    @endif
                                                </td>
                                                <td>{{ $dep->contract_duration ?: '-' }}</td>
                                                <td>₹ {{ number_format((float) $dep->employee_ctc, 2) }}</td>
                                                <td>{{ number_format((float) $dep->margin_percentage, 2) }}%</td>
                                                <td class="table-warning fw-bold text-dark">
                                                    ₹ {{ number_format((float) $dep->margin_amount, 2) }}
                                                </td>
                                                <td class="fw-bold text-success">
                                                    ₹ {{ number_format((float) $dep->investor_commission_amount, 2) }}
                                                </td>
                                                <td class="fw-medium text-primary">
                                                    ₹ {{ number_format((float) $dep->szorzo_commission_amount, 2) }}
                                                </td>
                                                <td>
                                                    @if($dep->status)
                                                        <span class="badge bg-success-subtle text-success">Active</span>
                                                    @else
                                                        <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="{{ $currentInvester ? 9 : 10 }}" class="text-center text-muted py-4">
                                                    No deployed employees found matching the selected filters.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Two-Column Section: Related Clients List & Overall Investment Tranches -->
            <div class="row g-4">
                <!-- Related Clients List (Point 4) -->
                <div class="col-lg-6">
                    <div class="card border h-100">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="ri-building-line me-1 text-primary"></i> Associated Clients
                            </h5>
                            <span class="badge bg-info-subtle text-info fs-12">{{ $relatedClients->count() }} Clients</span>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Client Name</th>
                                            <th>Employees</th>
                                            <th>Monthly Billing</th>
                                            <th>Margin</th>
                                            <th>Investor Share</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($relatedClients as $rc)
                                            <tr>
                                                <td class="fw-bold text-dark">{{ $rc['client_name'] }}</td>
                                                <td><span class="badge bg-primary-subtle text-primary">{{ $rc['employee_count'] }}</span></td>
                                                <td>₹ {{ number_format($rc['total_rate_card'], 2) }}</td>
                                                <td class="fw-bold text-warning-emphasis">₹ {{ number_format($rc['total_margin'], 2) }}</td>
                                                <td class="fw-bold text-success">₹ {{ number_format($rc['investor_share'], 2) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-3">No associated clients yet.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Overall Investment Tranches (Matching Right Side of photo: 500000 Micro Investor 15th of Sep) -->
                <div class="col-lg-6">
                    <div class="card border h-100">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="ri-money-dollar-circle-line me-1 text-primary"></i> Overall Investment Tranches
                            </h5>
                            <span class="badge bg-success-subtle text-success fs-12">₹ {{ number_format($metrics['overall_investment'], 2) }} Total</span>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            @if(!$currentInvester)
                                                <th>Investor</th>
                                            @endif
                                            <th>Tranche Amount</th>
                                            <th>Type / Notes</th>
                                            <th>Date of Investment</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($allFinancials as $fin)
                                            <tr>
                                                @if(!$currentInvester)
                                                    <td>{{ $fin->invester?->name ?? '-' }}</td>
                                                @endif
                                                <td class="fw-bold text-success fs-6">
                                                    ₹ {{ number_format((float) $fin->investment_amount, 2) }}
                                                </td>
                                                <td>
                                                    <span class="badge bg-light text-dark border">
                                                        {{ $fin->notes ?: ($fin->shares_units_held ?: 'Micro Investor') }}
                                                    </span>
                                                </td>
                                                <td>
                                                    {{ $fin->investment_date ? $fin->investment_date->format('jS \o\f M Y') : ($fin->created_at ? $fin->created_at->format('jS \o\f M Y') : '-') }}
                                                </td>
                                                <td>
                                                    @if($fin->status)
                                                        <span class="badge bg-success-subtle text-success">Active</span>
                                                    @else
                                                        <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-3">No investment tranches recorded.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

