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
            @php
                $isInvestor = (!empty($isInvestorUser)) || (auth()->check() && (auth()->user()->isInvestor() || (int) auth()->user()->role_id === 6));
            @endphp

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

            <!-- Row 1: Capital, Spend, Balance & Investor Earnings (Arranged in logical financial sequence) -->
            <div class="row g-3 mb-3">
                <!-- Card 1: Overall Investment -->
                <div class="col-xl-3 col-md-6">
                    <div class="card inv-stat-card border-top border-3 border-secondary shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="text-muted text-uppercase fw-bold fs-11">Overall Investment</span>
                                    <h3 class="fw-bold text-dark mt-2 mb-0">₹ {{ number_format($metrics['overall_investment'], 2) }}</h3>
                                    {{-- <small class="text-muted mt-1 d-block">{{ $allFinancials->count() }} Tranches Recorded</small> --}}
                                </div>
                                <div class="inv-stat-icon bg-secondary-subtle text-secondary">
                                    <i class="ri-bank-card-line"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Total Employees Count -->
                <div class="col-xl-3 col-md-6">
                    <div class="card inv-stat-card border-top border-3 border-info shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="text-muted text-uppercase fw-bold fs-11">Total Employees Count</span>
                                    <h3 class="fw-bold text-dark mt-2 mb-0">{{ $metrics['total_employees'] }} <span class="fs-14 fw-normal text-muted">Talent</span></h3>
                                    <small class="text-info-emphasis fw-medium mt-1 d-block">
                                        <i class="ri-user-shared-line me-1"></i> Mapped External Employees
                                    </small>
                                </div>
                                <div class="inv-stat-icon bg-info-subtle text-info">
                                    <i class="ri-team-line"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 3: Total Employee Spend -->
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

            <!-- Row 2: Deployments, Employees, Monthly Payroll Cost & Monthly Margin -->
            <div class="row g-3 mb-4">
                <!-- Card 5: Active Deployments -->
                <div class="col-xl-3 col-md-6">
                    <div class="card inv-stat-card border-top border-3 border-primary shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="text-muted text-uppercase fw-bold fs-11">No of Clients Engaged</span>
                                    <h3 class="fw-bold text-dark mt-2 mb-0">{{ $metrics['total_deployments'] }}</h3>
                                    {{-- <small class="text-primary fw-medium mt-1 d-block">{{ $metrics['total_clients'] }} Clients Deployed To</small> --}}
                                </div>
                                <div class="inv-stat-icon bg-primary-subtle text-primary">
                                    <i class="ri-building-line"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 6: Monthly Employee Cost -->
                <div class="col-xl-3 col-md-6">
                    <div class="card inv-stat-card border-top border-3 border-primary shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="text-muted text-uppercase fw-bold fs-11">Monthly Employee Cost</span>
                                    <h3 class="fw-bold text-dark mt-2 mb-0">₹ {{ number_format($metrics['monthly_employee_cost'], 2) }}</h3>
                                    <small class="text-muted mt-1 d-block">
                                        <i class="ri-calendar-check-line me-1"></i> Monthly Salary Commitment
                                    </small>
                                </div>
                                <div class="inv-stat-icon bg-primary-subtle text-primary">
                                    <i class="ri-calendar-todo-line"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 7: Gross Margin Generated (Yellow Box) -->
                <div class="col-xl-3 col-md-6">
                    <div class="card inv-stat-card border-top border-3 border-warning shadow-sm h-100">
                        <div class="card-body margin-highlight-box rounded-3 h-100">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="text-dark text-uppercase fw-bold fs-11">Margin In Rupees</span>
                                    <h3 class="fw-bold text-dark mt-2 mb-0">₹ {{ number_format($metrics['total_margin'], 2) }}</h3>
                                    <small class="text-dark-50 mt-1 d-block">
                                        Less TDS: -₹ {{ number_format($metrics['total_tds'] ?? 0, 2) }} &bull; Net: ₹ {{ number_format($metrics['total_net_margin'] ?? ($metrics['total_margin'] - ($metrics['total_tds'] ?? 0)), 2) }}
                                    </small>
                                </div>
                                <div class="inv-stat-icon bg-white text-dark shadow-sm">
                                    <i class="ri-money-rupee-circle-fill"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 8: Investor Profit Share (30%) -->
                <div class="col-xl-3 col-md-6">
                    <div class="card inv-stat-card border-top border-3 border-success shadow-sm h-100">
                        <div class="card-body" style="background: #e8f5e9;">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="text-success text-uppercase fw-bold fs-11">Investor Share (30%)</span>
                                    <h3 class="fw-bold text-success mt-2 mb-0">₹ {{ number_format($metrics['total_investor_share'], 2) }}</h3>
                                    @if(!$isInvestor)
                                        <small class="text-muted mt-1 d-block">SZ Share (70%): ₹ {{ number_format($metrics['total_szorzo_share'], 2) }} (after TDS)</small>
                                    @else
                                        <small class="text-success mt-1 d-block fw-medium"><i class="ri-checkbox-circle-line me-1"></i> Net Investor Profit Share</small>
                                    @endif
                                </div>
                                <div class="inv-stat-icon bg-success text-white shadow-sm">
                                    <i class="ri-hand-coin-line"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profit Sharing Model Visual Card (Modern UI Redesign) (Circle UI Design) -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow-sm border border-light-subtle rounded-4 overflow-hidden">
                        <div class="card-header bg-white border-bottom border-light py-3 px-3 px-md-4">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-sm d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-circle shadow-sm" style="width: 38px; height: 38px;">
                                        <i class="ri-pie-chart-2-line fs-18"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold fs-15 text-dark">
                                            @if($isInvestor)
                                                Investor Profit Sharing Model (30% Net)
                                            @else
                                                Profit Sharing Model Split (70% / 30%)
                                            @endif
                                        </h6>
                                        <small class="text-muted fs-11">
                                            @if($isInvestor)
                                                Fixed 30% profit share calculated from net margin after 10% TDS deduction
                                            @else
                                                Fixed profit share distribution (SZ 70% / Investor 30%) calculated on net margin after TDS
                                            @endif
                                        </small>
                                    </div>
                                </div>
                                <div>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle fs-12 px-3 py-1 rounded-pill shadow-sm">
                                        <i class="ri-scales-3-line me-1"></i>
                                        @if($isInvestor)
                                            30% Profit Split (After TDS)
                                        @else
                                            Split 70% / 30% After TDS
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="card-body p-3 p-md-4 bg-light-subtle">
                            @if(!$isInvestor)
                                <!-- Admin / Management View: Circular Donut Chart + Circular Metric Cards -->
                                @php
                                    $netMarginVal = $metrics['total_net_margin'] ?? ($metrics['total_margin'] - ($metrics['total_tds'] ?? 0));
                                    $totalComms = $metrics['total_szorzo_share'] + $metrics['total_investor_share'];
                                    $szPct = $totalComms > 0 ? round(($metrics['total_szorzo_share'] / $totalComms) * 100) : 70;
                                    $invPct = 100 - $szPct;
                                @endphp

                                <div class="row align-items-stretch g-3 g-xl-4">
                                    <!-- Left Column: Circular Donut Split Chart -->
                                    <div class="col-lg-4 col-md-5">
                                        <div class="p-3 rounded-4 border bg-white shadow-sm h-100 d-flex flex-column justify-content-between align-items-center text-center" style="background: radial-gradient(circle at center, #ffffff 65%, #f8fafc 100%); min-height: 280px;">
                                            <div class="d-flex align-items-center justify-content-between w-100 mb-1">
                                                <span class="fs-11 fw-bold text-uppercase text-muted" style="letter-spacing: 0.5px;">
                                                    <i class="ri-donut-chart-line text-primary me-1"></i> Allocation Circle
                                                </span>
                                                <span class="badge bg-light text-dark border rounded-pill fs-11">
                                                    70 : 30 Ratio
                                                </span>
                                            </div>

                                            <!-- ApexCharts Container -->
                                            <div id="profit-sharing-donut-chart" class="apex-charts w-100 my-auto" style="min-height: 220px;"></div>

                                            <!-- Sleek Circular Legend Badges -->
                                            <div class="d-flex justify-content-center align-items-center gap-2 flex-wrap w-100 pt-2 border-top border-light">
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 fs-11 rounded-pill d-inline-flex align-items-center">
                                                    <span class="rounded-circle me-1" style="width: 8px; height: 8px; background-color: #2563eb; display: inline-block;"></span>
                                                    SZ: {{ $szPct }}% (₹ {{ number_format($metrics['total_szorzo_share'], 2) }})
                                                </span>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 fs-11 rounded-pill d-inline-flex align-items-center">
                                                    <span class="rounded-circle me-1" style="width: 8px; height: 8px; background-color: #10b981; display: inline-block;"></span>
                                                    Inv: {{ $invPct }}% (₹ {{ number_format($metrics['total_investor_share'], 2) }})
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Right Column: 3 Circular Financial Cards -->
                                    <div class="col-lg-8 col-md-7">
                                        <div class="d-flex flex-column gap-3 h-100 justify-content-between">
                                            <!-- Card 1: Net Margin Base Pool (Full Width) -->
                                            <div class="p-3 rounded-4 border bg-white shadow-sm">
                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="avatar-sm rounded-circle bg-warning-subtle text-warning d-flex align-items-center justify-content-center shadow-sm flex-shrink-0" style="width: 36px; height: 36px;">
                                                            <i class="ri-scales-3-line fs-18"></i>
                                                        </div>
                                                        <div>
                                                            <span class="text-muted text-uppercase fw-bold fs-11 d-block" style="letter-spacing: 0.5px;">Net Margin Base Pool</span>
                                                            <span class="fs-12 text-muted">Distributable pool after statutory deductions</span>
                                                        </div>
                                                    </div>
                                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2 py-1 fs-11 fw-semibold">
                                                        <i class="ri-checkbox-blank-circle-fill fs-8 me-1 text-warning"></i> 100% Base Pool
                                                    </span>
                                                </div>

                                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 pt-2 border-top border-light">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="flex-shrink-0" style="width: 44px; height: 44px; display: inline-flex; align-items: center; justify-content: center;">
                                                            <svg viewBox="0 0 36 36" class="w-100 h-100" style="transform: rotate(-90deg);">
                                                                <path stroke="#fef3c7" stroke-width="3.5" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                                                <path stroke="#f59e0b" stroke-width="3.5" stroke-dasharray="100, 100" stroke-linecap="round" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                                            </svg>
                                                        </div>
                                                        <div>
                                                            <h3 class="fw-bold text-dark mb-0">₹ {{ number_format($netMarginVal, 2) }}</h3>
                                                            <small class="text-muted fs-11">Net profit after 10% TDS deduction</small>
                                                        </div>
                                                    </div>

                                                    <div class="d-flex flex-column align-items-end fs-12 text-muted">
                                                        <span>Gross Margin: <strong class="text-dark">₹ {{ number_format($metrics['total_margin'], 2) }}</strong></span>
                                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-0 fs-11 mt-1">
                                                            Less TDS (10%): -₹ {{ number_format($metrics['total_tds'] ?? 0, 2) }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Cards 2 & 3: Side by Side (Szorzo 70% & Investor 30%) -->
                                            <div class="row g-3">
                                                <!-- Szorzo Share (70%) -->
                                                <div class="col-md-6">
                                                    <div class="p-3 rounded-4 border h-100 shadow-sm" style="background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); border-color: #bfdbfe !important;">
                                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                                            <div class="avatar-sm rounded-circle bg-primary text-white d-flex align-items-center justify-content-center shadow-sm" style="width: 36px; height: 36px;">
                                                                <i class="ri-building-line fs-18"></i>
                                                            </div>
                                                            <span class="badge bg-primary text-white rounded-pill px-2 py-1 fs-11 fw-semibold shadow-sm">
                                                                <i class="ri-checkbox-blank-circle-fill fs-8 me-1 text-white"></i> {{ $szPct }}% Share
                                                            </span>
                                                        </div>

                                                        <div class="d-flex align-items-center justify-content-between gap-2">
                                                            <div>
                                                                <span class="text-primary text-uppercase fw-bold fs-11 d-block" style="letter-spacing: 0.5px;">Szorzo Operations Share</span>
                                                                <h4 class="fw-bold text-primary mb-0">₹ {{ number_format($metrics['total_szorzo_share'], 2) }}</h4>
                                                                <small class="text-primary-emphasis fs-11 d-block mt-1">Company retained operations & talent management</small>
                                                            </div>

                                                            <div class="flex-shrink-0" style="width: 44px; height: 44px; display: inline-flex; align-items: center; justify-content: center;">
                                                                <svg viewBox="0 0 36 36" class="w-100 h-100" style="transform: rotate(-90deg);">
                                                                    <path stroke="#bfdbfe" stroke-width="3.5" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                                                    <path stroke="#2563eb" stroke-width="3.5" stroke-dasharray="{{ $szPct }}, 100" stroke-linecap="round" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                                                </svg>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Investor Share (30%) -->
                                                <div class="col-md-6">
                                                    <div class="p-3 rounded-4 border h-100 shadow-sm" style="background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%); border-color: #a7f3d0 !important;">
                                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                                            <div class="avatar-sm rounded-circle bg-success text-white d-flex align-items-center justify-content-center shadow-sm" style="width: 36px; height: 36px;">
                                                                <i class="ri-user-star-line fs-18"></i>
                                                            </div>
                                                            <span class="badge bg-success text-white rounded-pill px-2 py-1 fs-11 fw-semibold shadow-sm">
                                                                <i class="ri-checkbox-blank-circle-fill fs-8 me-1 text-white"></i> {{ $invPct }}% Share
                                                            </span>
                                                        </div>

                                                        <div class="d-flex align-items-center justify-content-between gap-2">
                                                            <div>
                                                                <span class="text-success text-uppercase fw-bold fs-11 d-block" style="letter-spacing: 0.5px;">Investor Profit Share</span>
                                                                <h4 class="fw-bold text-success mb-0">₹ {{ number_format($metrics['total_investor_share'], 2) }}</h4>
                                                                <small class="text-success-emphasis fs-11 d-block mt-1">Net profit share payable to investor post TDS</small>
                                                            </div>

                                                            <div class="flex-shrink-0" style="width: 44px; height: 44px; display: inline-flex; align-items: center; justify-content: center;">
                                                                <svg viewBox="0 0 36 36" class="w-100 h-100" style="transform: rotate(-90deg);">
                                                                    <path stroke="#a7f3d0" stroke-width="3.5" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                                                    <path stroke="#10b981" stroke-width="3.5" stroke-dasharray="{{ $invPct }}, 100" stroke-linecap="round" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                                                </svg>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <!-- Investor Login View: Zero Szorzo exposure, Circular Radial Share Gauge + 2 Cards -->
                                @php
                                    $netMarginVal = $metrics['total_net_margin'] ?? ($metrics['total_margin'] - ($metrics['total_tds'] ?? 0));
                                @endphp

                                <div class="row align-items-stretch g-3 g-xl-4">
                                    <!-- Left Column: Circular Radial Gauge (30% Share) -->
                                    <div class="col-lg-4 col-md-5">
                                        <div class="p-3 rounded-4 border bg-white shadow-sm h-100 d-flex flex-column justify-content-between align-items-center text-center" style="background: radial-gradient(circle at center, #ffffff 65%, #f0fdf4 100%); min-height: 280px;">
                                            <div class="d-flex align-items-center justify-content-between w-100 mb-1">
                                                <span class="fs-11 fw-bold text-uppercase text-muted" style="letter-spacing: 0.5px;">
                                                    <i class="ri-compass-3-line text-success me-1"></i> Profit Share Gauge
                                                </span>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill fs-11">
                                                    30% Net Model
                                                </span>
                                            </div>

                                            <!-- ApexCharts Radial Container -->
                                            <div id="investor-radial-chart" class="apex-charts w-100 my-auto" style="min-height: 220px;"></div>

                                            <div class="w-100 pt-2 border-top border-light">
                                                <span class="badge bg-success text-white px-3 py-1 fs-11 rounded-pill shadow-sm d-inline-flex align-items-center">
                                                    <i class="ri-checkbox-circle-fill me-1"></i> Fixed 30% Net Profit Share
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Right Column: 2 Modern Investor Cards -->
                                    <div class="col-lg-8 col-md-7">
                                        <div class="d-flex flex-column gap-3 h-100 justify-content-between">
                                            <!-- Card 1: Net Margin Generated -->
                                            <div class="p-3 rounded-4 border bg-white shadow-sm">
                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="avatar-sm rounded-circle bg-secondary-subtle text-secondary d-flex align-items-center justify-content-center shadow-sm flex-shrink-0" style="width: 36px; height: 36px;">
                                                            <i class="ri-scales-3-line fs-18"></i>
                                                        </div>
                                                        <div>
                                                            <span class="text-muted text-uppercase fw-bold fs-11 d-block" style="letter-spacing: 0.5px;">Net Margin Generated</span>
                                                            <span class="fs-12 text-muted">Across {{ $metrics['total_employees'] }} active mapped talent deployment(s)</span>
                                                        </div>
                                                    </div>
                                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2 py-1 fs-11 fw-semibold">
                                                        After 10% TDS
                                                    </span>
                                                </div>

                                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 pt-2 border-top border-light">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="flex-shrink-0" style="width: 44px; height: 44px; display: inline-flex; align-items: center; justify-content: center;">
                                                            <svg viewBox="0 0 36 36" class="w-100 h-100" style="transform: rotate(-90deg);">
                                                                <path stroke="#e2e8f0" stroke-width="3.5" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                                                <path stroke="#64748b" stroke-width="3.5" stroke-dasharray="100, 100" stroke-linecap="round" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                                            </svg>
                                                        </div>
                                                        <div>
                                                            <h3 class="fw-bold text-dark mb-0">₹ {{ number_format($netMarginVal, 2) }}</h3>
                                                            <small class="text-muted fs-11">Net pool basis after TDS deduction</small>
                                                        </div>
                                                    </div>

                                                    <div class="d-flex flex-column align-items-end fs-12 text-muted">
                                                        <span>Gross Margin: <strong class="text-dark">₹ {{ number_format($metrics['total_margin'], 2) }}</strong></span>
                                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-0 fs-11 mt-1">
                                                            Less TDS (10%): -₹ {{ number_format($metrics['total_tds'] ?? 0, 2) }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Card 2: Your Net Profit Share (30%) -->
                                            <div class="p-3 rounded-4 border shadow-sm" style="background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%); border-color: #a7f3d0 !important;">
                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                    <div class="avatar-sm rounded-circle bg-success text-white d-flex align-items-center justify-content-center shadow-sm" style="width: 36px; height: 36px;">
                                                        <i class="ri-hand-coin-line fs-18"></i>
                                                    </div>
                                                    <span class="badge bg-success text-white rounded-pill px-3 py-1 fs-11 fw-semibold shadow-sm">
                                                        <i class="ri-checkbox-blank-circle-fill fs-8 me-1 text-white"></i> 30.0% Allocation
                                                    </span>
                                                </div>

                                                <div class="d-flex align-items-center justify-content-between gap-2">
                                                    <div>
                                                        <span class="text-success text-uppercase fw-bold fs-11 d-block" style="letter-spacing: 0.5px;">Your Net Profit Share (30%)</span>
                                                        <h2 class="fw-bold text-success mb-0">₹ {{ number_format($metrics['total_investor_share'], 2) }}</h2>
                                                        <small class="text-success-emphasis fs-11 d-block mt-1">Net profit share payable directly to your investor account</small>
                                                    </div>

                                                    <div class="flex-shrink-0" style="width: 50px; height: 50px; display: inline-flex; align-items: center; justify-content: center;">
                                                        <svg viewBox="0 0 36 36" class="w-100 h-100" style="transform: rotate(-90deg);">
                                                            <path stroke="#a7f3d0" stroke-width="3.5" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                                            <path stroke="#10b981" stroke-width="3.5" stroke-dasharray="30, 100" stroke-linecap="round" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                                        </svg>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
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
                                                    @if($row['employees_count'] > 0)
                                                        <button type="button" 
                                                                class="btn btn-sm btn-soft-primary py-1 px-2 d-inline-flex align-items-center view-ledger-employees-btn"
                                                                data-month="{{ $row['month_label'] }}"
                                                                data-employees="{{ json_encode($row['employees_detail']) }}"
                                                                title="Click to view employees in popup">
                                                            <i class="ri-team-line me-1"></i>
                                                            <span>{{ $row['employees_count'] }} {{ Str::plural('Employee', $row['employees_count']) }}</span>
                                                            <i class="ri-eye-line ms-1 text-primary"></i>
                                                        </button>
                                                    @else
                                                        <span class="text-muted fs-12">—</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="fw-bold text-dark">₹ {{ number_format($row['spend'], 2) }}</span>
                                                </td>
                                                <td>
                                                    <span class="text-dark fw-bold">₹ {{ number_format($row['gross_margin'], 2) }}</span>
                                                    <small class="text-danger d-block fs-11">Less TDS ({{ number_format($row['tds_percentage'] ?? 10, 1) }}%): -₹ {{ number_format($row['tds'] ?? 0, 2) }}</small>
                                                    <small class="text-muted d-block fs-11">Net: ₹ {{ number_format($row['net_margin'] ?? ($row['gross_margin'] - ($row['tds'] ?? 0)), 2) }}</small>
                                                    @if(!$isInvestor)
                                                        <small class="text-primary d-block fs-11 mt-1">SZ (70%): ₹ {{ number_format($row['szorzo_earned'], 2) }}</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="fw-bold text-success fs-6">₹ {{ number_format($row['investor_earned'], 2) }}</span>
                                                    <small class="text-muted d-block fs-11">30% after TDS</small>
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

            <!-- Sections hidden per user request: Investors Performance Breakdown, Deployed Employees Funded by Investor, and Associated Clients / Investment Tranches -->
            @if(false)
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
            @endif

            <style>
            /* High z-index & backdrop stacking fix for Ledger Employees Modal */
            #ledgerEmployeesModal {
                z-index: 1080 !important;
            }
            #ledgerEmployeesModal .modal-dialog {
                z-index: 1085 !important;
                position: relative !important;
            }
            #ledgerEmployeesModal .modal-content {
                background-color: #ffffff !important;
                position: relative !important;
                z-index: 1090 !important;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4) !important;
            }

            /* Salary Breakup Modal High z-index */
            #salaryBreakupModal {
                z-index: 1095 !important;
            }
            #salaryBreakupModal .modal-dialog {
                z-index: 1100 !important;
                position: relative !important;
            }
            #salaryBreakupModal .modal-content {
                background-color: #ffffff !important;
                position: relative !important;
                z-index: 1105 !important;
                box-shadow: 0 12px 35px rgba(0, 0, 0, 0.45) !important;
            }
            </style>

            <!-- Mapped Employees List Modal -->
            <div class="modal fade" id="ledgerEmployeesModal" tabindex="-1" aria-labelledby="ledgerEmployeesModalLabel" aria-hidden="true" style="z-index: 1080 !important;">
                <div class="modal-dialog modal-dialog-centered modal-xl" style="z-index: 1085 !important; max-width: 1250px;">
                    <div class="modal-content border-0 shadow" style="background-color: #ffffff !important; position: relative !important; z-index: 1090 !important;">
                        <div class="modal-header bg-primary text-white py-3">
                            <div class="d-flex align-items-center gap-2">
                                <i class="ri-team-line fs-20"></i>
                                <div>
                                    <h5 class="modal-title text-white mb-0" id="ledgerEmployeesModalLabel">Mapped External Employees</h5>
                                    <small class="text-white-50" id="ledgerEmployeesModalSub">—</small>
                                </div>
                            </div>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-3">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover align-middle mb-0" id="ledgerEmployeesTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 40px;" class="text-center">#</th>
                                            <th>Employee</th>
                                            <th>Client & Job Role</th>
                                            <th>Job Location</th>
                                            <th class="text-center">Tenure (DOJ / Relieving)</th>
                                            <th class="text-end">Salary & Breakup</th>
                                            <th class="text-center">Offer Letter</th>
                                            <th class="text-end">Margin Generated (₹)</th>
                                        </tr>
                                    </thead>
                                    <tbody id="ledgerEmployeesTbody"></tbody>
                                    <tfoot class="table-light fw-bold" id="ledgerEmployeesTfoot"></tfoot>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer bg-light py-2">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dedicated Salary Breakup Modal -->
            <div class="modal fade" id="salaryBreakupModal" tabindex="-1" aria-labelledby="salaryBreakupModalLabel" aria-hidden="true" style="z-index: 1095 !important;">
                <div class="modal-dialog modal-dialog-centered modal-md" style="z-index: 1100 !important;">
                    <div class="modal-content border-0 shadow-lg" style="background-color: #ffffff !important; position: relative !important; z-index: 1105 !important;">
                        <div class="modal-header bg-dark text-white py-3">
                            <div class="d-flex align-items-center gap-2">
                                <i class="ri-file-list-3-line fs-20 text-info"></i>
                                <div>
                                    <h5 class="modal-title text-white mb-0" id="salaryBreakupModalLabel">Salary Breakup & Statutory Details</h5>
                                    <small class="text-white-50" id="salaryBreakupEmpInfo">—</small>
                                </div>
                            </div>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-3" id="salaryBreakupModalBody">
                            <!-- Populated dynamically via JS -->
                        </div>
                        <div class="modal-footer bg-light py-2">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@section('script')
<script>
function ensureLedgerModalInBody() {
    var modal = document.getElementById('ledgerEmployeesModal');
    if (modal && modal.parentElement !== document.body) {
        document.body.appendChild(modal);
    }
}

function ensureSalaryModalInBody() {
    var modal = document.getElementById('salaryBreakupModal');
    if (modal && modal.parentElement !== document.body) {
        document.body.appendChild(modal);
    }
}

var currentLedgerEmployees = [];

function showSalaryBreakupModal(emp) {
    if (!emp) return;
    var b = emp.salary_breakup || {};
    var sal = parseFloat(emp.salary) || 0;
    var basic = parseFloat(b.basic) || (sal * 0.50);
    var hra = parseFloat(b.hra) || (sal * 0.30);
    var conveyance = parseFloat(b.conveyance) || 0;
    var medical = parseFloat(b.medical) || 0;
    var special = parseFloat(b.special) || (sal * 0.20);
    var otherEarn = parseFloat(b.overtime || 0) + parseFloat(b.lta || 0) + parseFloat(b.arrears || 0);
    var gross = parseFloat(b.gross) || sal;

    var pf = parseFloat(b.pf) || 0;
    var esi = parseFloat(b.esi) || 0;
    var pt = parseFloat(b.pt) || 0;
    var tds = parseFloat(b.tds) || (sal * 0.10);
    var otherDed = parseFloat(b.salary_advance || 0) + parseFloat(b.fines || 0) + parseFloat(b.labour_welfare || 0) + parseFloat(b.other_deductions || 0);
    var totalDed = pf + esi + pt + tds + otherDed;
    var net = Math.max(0, gross - totalDed);

    $('#salaryBreakupEmpInfo').html('<strong>' + (emp.name || 'Employee') + '</strong> &bull; ' + (emp.employee_no || '') + ' (' + (emp.designation || 'Staff') + ')');

    var html = `
        <div class="card border-0 bg-light mb-3">
            <div class="card-body p-2 d-flex align-items-center gap-3">
                <img src="${emp.avatar_url || '/admin/images/users/user-dummy-img.jpg'}" class="rounded-circle border" style="width: 44px; height: 44px; object-fit: cover;" onerror="this.onerror=null;this.src='/admin/images/users/user-dummy-img.jpg';">
                <div class="flex-grow-1">
                    <h6 class="mb-0 fw-bold fs-14 text-dark">${emp.name || '—'}</h6>
                    <small class="text-muted d-block">${emp.client || '—'} &bull; <i class="ri-map-pin-line text-danger"></i> ${emp.job_location || 'Bengaluru, India'}</small>
                </div>
                ${emp.offer_letter_url ? `
                    <a href="${emp.offer_letter_url}" target="_blank" rel="noopener" class="btn btn-sm btn-soft-primary">
                        <i class="ri-file-text-line me-1"></i> Offer Letter
                    </a>
                ` : ''}
            </div>
        </div>

        <div class="row g-2">
            <!-- Earnings -->
            <div class="col-6">
                <div class="border rounded-2 p-2 h-100 bg-white shadow-sm">
                    <h6 class="fs-11 text-uppercase fw-bold text-primary mb-2 pb-1 border-bottom">Earnings (₹)</h6>
                    <div class="d-flex justify-content-between fs-12 mb-1"><span class="text-muted">Basic Salary:</span><span class="fw-semibold">₹ ${basic.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span></div>
                    <div class="d-flex justify-content-between fs-12 mb-1"><span class="text-muted">HRA:</span><span class="fw-semibold">₹ ${hra.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span></div>
                    <div class="d-flex justify-content-between fs-12 mb-1"><span class="text-muted">Conveyance:</span><span class="fw-semibold">₹ ${conveyance.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span></div>
                    <div class="d-flex justify-content-between fs-12 mb-1"><span class="text-muted">Medical Allow:</span><span class="fw-semibold">₹ ${medical.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span></div>
                    <div class="d-flex justify-content-between fs-12 mb-1"><span class="text-muted">Special Allow:</span><span class="fw-semibold">₹ ${special.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span></div>
                    ${otherEarn > 0 ? `<div class="d-flex justify-content-between fs-12 mb-1"><span class="text-muted">Other Earnings:</span><span class="fw-semibold">₹ ${otherEarn.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span></div>` : ''}
                    <div class="d-flex justify-content-between fs-12 mt-2 pt-1 border-top fw-bold text-dark"><span>Total Gross:</span><span>₹ ${gross.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span></div>
                </div>
            </div>

            <!-- Deductions -->
            <div class="col-6">
                <div class="border rounded-2 p-2 h-100 bg-white shadow-sm">
                    <h6 class="fs-11 text-uppercase fw-bold text-danger mb-2 pb-1 border-bottom">Deductions (₹)</h6>
                    <div class="d-flex justify-content-between fs-12 mb-1"><span class="text-muted">PF Deduction:</span><span class="text-danger fw-semibold">- ₹ ${pf.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span></div>
                    <div class="d-flex justify-content-between fs-12 mb-1"><span class="text-muted">ESI Deduction:</span><span class="text-danger fw-semibold">- ₹ ${esi.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span></div>
                    <div class="d-flex justify-content-between fs-12 mb-1"><span class="text-muted">Prof. Tax (PT):</span><span class="text-danger fw-semibold">- ₹ ${pt.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span></div>
                    <div class="d-flex justify-content-between fs-12 mb-1"><span class="text-muted">TDS / Income Tax:</span><span class="text-danger fw-semibold">- ₹ ${tds.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span></div>
                    ${otherDed > 0 ? `<div class="d-flex justify-content-between fs-12 mb-1"><span class="text-muted">Other Deductions:</span><span class="text-danger fw-semibold">- ₹ ${otherDed.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span></div>` : ''}
                    <div class="d-flex justify-content-between fs-12 mt-2 pt-1 border-top fw-bold text-danger"><span>Total Deductions:</span><span>- ₹ ${totalDed.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span></div>
                </div>
            </div>
        </div>

        <div class="mt-3 p-3 rounded-3 text-center border" style="background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%); border-color: #a7f3d0 !important;">
            <span class="text-muted text-uppercase fw-semibold fs-11 d-block mb-1">Net Monthly Take-Home (After PF & TDS)</span>
            <h3 class="fw-bold text-success mb-0">₹ ${net.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</h3>
        </div>
    `;

    $('#salaryBreakupModalBody').html(html);
    ensureSalaryModalInBody();
    var modalEl = document.getElementById('salaryBreakupModal');
    if (modalEl && window.bootstrap) {
        var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    }
}

$(document).ready(function() {
    ensureLedgerModalInBody();
    ensureSalaryModalInBody();

    $(document).on('click', '.view-salary-breakup-btn', function() {
        var idx = $(this).data('emp-idx');
        var emp = currentLedgerEmployees[idx];
        if (emp) {
            showSalaryBreakupModal(emp);
        }
    });

    $(document).on('click', '.view-ledger-employees-btn', function() {
        ensureLedgerModalInBody();

        var month = $(this).data('month') || '';
        var emps = $(this).data('employees');
        if (typeof emps === 'string') {
            try { emps = JSON.parse(emps); } catch(e) { emps = []; }
        }
        if (!Array.isArray(emps)) emps = [];

        currentLedgerEmployees = emps;

        $('#ledgerEmployeesModalSub').text(month ? 'Month: ' + month + ' (' + emps.length + ' Employees)' : emps.length + ' Employees');
        
        var tbody = '';
        var totalSalary = 0;
        var totalMargin = 0;

        emps.forEach(function(emp, idx) {
            var sal = parseFloat(emp.salary) || 0;
            var rev = parseFloat(emp.revenue) || 0;
            totalSalary += sal;
            totalMargin += rev;

            var avatarImg = emp.avatar_url || '/admin/images/users/user-dummy-img.jpg';
            var empName = emp.name || '—';
            var empNo = emp.employee_no || ('SZ' + String(emp.id).padStart(3, '0'));
            var clientName = emp.client || '—';
            var roleName = emp.designation || 'Staff';
            var locationName = emp.job_location || 'Bengaluru, India';
            var dojStr = emp.date_of_joining || '—';
            var relievingStr = emp.relieving_date || 'Active';
            var isRelievingActive = !emp.relieving_date || emp.relieving_date === 'Active' || emp.relieving_date === 'Active / Ongoing';

            var offerLetterHtml = '';
            if (emp.offer_letter_url) {
                offerLetterHtml = `
                    <div class="btn-group btn-group-sm">
                        <a href="${emp.offer_letter_url}" target="_blank" rel="noopener" class="btn btn-sm btn-soft-primary py-1 px-2 d-inline-flex align-items-center" title="View Offer Letter">
                            <i class="ri-file-text-line me-1"></i> View
                        </a>
                        <a href="${emp.offer_letter_url}" download class="btn btn-sm btn-soft-success py-1 px-2" title="Download Offer Letter">
                            <i class="ri-download-2-line"></i>
                        </a>
                    </div>
                `;
            } else {
                offerLetterHtml = `<span class="badge bg-light text-muted border py-1 px-2 fs-11"><i class="ri-file-forbid-line me-1"></i> Not Uploaded</span>`;
            }

            tbody += `<tr>
                <td class="text-center text-muted fw-semibold">${idx + 1}</td>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <img src="${avatarImg}" alt="${empName}" class="rounded-circle border shadow-sm flex-shrink-0" style="width: 40px; height: 40px; object-fit: cover;" onerror="this.onerror=null;this.src='/admin/images/users/user-dummy-img.jpg';">
                        <div>
                            <span class="fw-bold text-dark d-block fs-13">${empName}</span>
                            <span class="badge bg-light text-dark border fs-11">${empNo}</span>
                        </div>
                    </div>
                </td>
                <td>
                    <span class="fw-semibold text-primary d-block">
                        <i class="ri-building-line me-1 text-muted"></i>${clientName}
                    </span>
                    <span class="badge bg-primary-subtle text-primary mt-1 fs-11">
                        <i class="ri-briefcase-line me-1"></i>${roleName}
                    </span>
                </td>
                <td>
                    <span class="text-dark fs-12 text-nowrap">
                        <i class="ri-map-pin-2-line text-danger me-1"></i>${locationName}
                    </span>
                </td>
                <td class="text-center text-nowrap fs-12">
                    <div><span class="text-muted">DOJ:</span> <strong class="text-dark">${dojStr}</strong></div>
                    <div class="mt-1"><span class="text-muted">Relieving:</span> <span class="badge ${isRelievingActive ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary'} fs-11">${relievingStr}</span></div>
                </td>
                <td class="text-end">
                    <span class="fw-bold text-dark fs-14 d-block">₹ ${sal.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
                    <div class="mt-1">
                        <button type="button" class="btn btn-sm btn-soft-info py-0 px-2 fs-11 view-salary-breakup-btn" data-emp-idx="${idx}" title="View detailed salary breakup including PF, TDS, etc.">
                            <i class="ri-file-list-3-line me-1"></i> Salary Breakup
                        </button>
                    </div>
                </td>
                <td class="text-center">
                    ${offerLetterHtml}
                </td>
                <td class="text-end fw-bold text-success fs-14">
                    ₹ ${rev.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}
                </td>
            </tr>`;
        });

        if (emps.length === 0) {
            tbody = '<tr><td colspan="8" class="text-center text-muted py-4">No employee details available.</td></tr>';
            $('#ledgerEmployeesTfoot').html('');
        } else {
            var tfoot = `<tr>
                <td colspan="5" class="text-end">Total (${emps.length} Employees):</td>
                <td class="text-end text-dark fs-14">₹ ${totalSalary.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                <td></td>
                <td class="text-end text-success fs-14">₹ ${totalMargin.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
            </tr>`;
            $('#ledgerEmployeesTfoot').html(tfoot);
        }

        $('#ledgerEmployeesTbody').html(tbody);

        var modalEl = document.getElementById('ledgerEmployeesModal');
        if (modalEl && window.bootstrap) {
            var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        }
    });

    $('#ledgerEmployeesModal').on('hidden.bs.modal', function () {
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open').css({ overflow: '', paddingRight: '' });
    });

    $('#salaryBreakupModal').on('hidden.bs.modal', function () {
        if ($('#ledgerEmployeesModal').hasClass('show')) {
            $('body').addClass('modal-open');
        }
    });

    // Profit Sharing Model Circular Chart Initialization
    @if(!$isInvestor)
    (function initProfitSharingDonut() {
        var chartEl = document.querySelector("#profit-sharing-donut-chart");
        if (!chartEl || typeof ApexCharts === 'undefined') return;

        var szVal = {{ (float) ($metrics['total_szorzo_share'] ?? 0) }};
        var invVal = {{ (float) ($metrics['total_investor_share'] ?? 0) }};
        var isZero = (szVal <= 0 && invVal <= 0);
        var seriesData = isZero ? [70, 30] : [szVal, invVal];

        var options = {
            series: seriesData,
            labels: ['Szorzo Share (70%)', 'Investor Share (30%)'],
            chart: {
                type: 'donut',
                height: 220,
                fontFamily: 'inherit'
            },
            colors: isZero ? ['#cbd5e1', '#94a3b8'] : ['#2563eb', '#10b981'],
            stroke: {
                width: 3,
                colors: ['#ffffff']
            },
            dataLabels: {
                enabled: false
            },
            legend: {
                show: false
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '76%',
                        labels: {
                            show: true,
                            name: {
                                show: true,
                                fontSize: '11px',
                                fontWeight: 600,
                                color: '#64748b',
                                offsetY: -4
                            },
                            value: {
                                show: true,
                                fontSize: '18px',
                                fontWeight: 700,
                                color: '#0f172a',
                                offsetY: 4,
                                formatter: function (val) {
                                    return isZero ? '0%' : '₹ ' + Number(val).toLocaleString('en-IN', {maximumFractionDigits: 0});
                                }
                            },
                            total: {
                                show: true,
                                showAlways: true,
                                label: 'NET POOL',
                                fontSize: '10px',
                                fontWeight: 700,
                                color: '#94a3b8',
                                formatter: function (w) {
                                    var total = isZero ? 0 : w.globals.seriesTotals.reduce(function (a, b) { return a + b; }, 0);
                                    return '₹ ' + Number(total).toLocaleString('en-IN', {maximumFractionDigits: 0});
                                }
                            }
                        }
                    }
                }
            },
            tooltip: {
                enabled: !isZero,
                y: {
                    formatter: function (val) {
                        return '₹ ' + Number(val).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    }
                }
            }
        };

        var chart = new ApexCharts(chartEl, options);
        chart.render();
    })();
    @else
    (function initInvestorRadialChart() {
        var chartEl = document.querySelector("#investor-radial-chart");
        if (!chartEl || typeof ApexCharts === 'undefined') return;

        var options = {
            series: [30],
            chart: {
                type: 'radialBar',
                height: 220,
                sparkline: {
                    enabled: true
                }
            },
            plotOptions: {
                radialBar: {
                    startAngle: -135,
                    endAngle: 135,
                    hollow: {
                        margin: 0,
                        size: '70%',
                        background: '#ffffff',
                        dropShadow: {
                            enabled: true,
                            top: 3,
                            left: 0,
                            blur: 4,
                            opacity: 0.08
                        }
                    },
                    track: {
                        background: '#e2e8f0',
                        strokeWidth: '100%',
                        margin: 0
                    },
                    dataLabels: {
                        show: true,
                        name: {
                            offsetY: -10,
                            show: true,
                            color: '#64748b',
                            fontSize: '11px',
                            fontWeight: 600
                        },
                        value: {
                            formatter: function(val) {
                                return parseInt(val) + "%";
                            },
                            color: '#10b981',
                            fontSize: '24px',
                            fontWeight: 700,
                            show: true,
                            offsetY: 6
                        }
                    }
                }
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shade: 'dark',
                    type: 'horizontal',
                    shadeIntensity: 0.5,
                    gradientToColors: ['#059669'],
                    inverseColors: true,
                    opacityFrom: 1,
                    opacityTo: 1,
                    stops: [0, 100]
                }
            },
            stroke: {
                lineCap: 'round'
            },
            labels: ['YOUR SHARE']
        };

        var chart = new ApexCharts(chartEl, options);
        chart.render();
    })();
    @endif
});
</script>
@endsection

