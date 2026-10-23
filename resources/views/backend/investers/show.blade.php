@extends('backend.layouts.master')
@section('title', 'Investor Profile - ' . $invester->name)

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <!-- Page Title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Investor Profile</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('admin.investers.index') }}">Investers</a></li>
                                <li class="breadcrumb-item active">{{ $invester->name }}</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            @include('backend.investers.partials.nav-buttons')

            <!-- Profile Summary Card -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                                <div class="d-flex align-items-center gap-3">
                                    @if($invester->profile_picture && file_exists(public_path($invester->profile_picture)))
                                        <img src="{{ asset($invester->profile_picture) }}" alt="{{ $invester->name }}" class="rounded-circle avatar-lg img-thumbnail" style="width:72px;height:72px;object-fit:cover;">
                                    @else
                                        <div class="avatar-lg">
                                            <div class="avatar-title rounded-circle bg-primary-subtle text-primary fs-2 fw-bold" style="width:72px;height:72px;line-height:72px;text-align:center;">
                                                {{ strtoupper(substr($invester->name, 0, 1)) }}
                                            </div>
                                        </div>
                                    @endif
                                    <div>
                                        <h4 class="mb-1">{{ $invester->name }}</h4>
                                        <div class="d-flex flex-wrap align-items-center gap-2">
                                            <span class="badge bg-primary fs-12">{{ $invester->invester_id }}</span>
                                            @if($invester->investor_type)
                                                <span class="badge bg-info-subtle text-info fs-12">{{ $invester->investor_type }}</span>
                                            @endif
                                            @if($invester->status)
                                                <span class="badge bg-success-subtle text-success fs-12">Active</span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger fs-12">Inactive</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex flex-wrap gap-2">
                                    <a href="{{ route('admin.investers.dashboard') }}?invester_id={{ $invester->id }}" class="btn btn-success">
                                        <i class="ri-dashboard-2-line align-middle me-1"></i> Investor Dashboard
                                    </a>
                                    @can('edit', \App\Models\Invester::class)
                                        <a href="{{ route('admin.investers.edit', $invester->id) }}" class="btn btn-primary">
                                            <i class="bx bxs-edit align-middle me-1"></i> Edit Investor
                                        </a>
                                    @endcan
                                    <a href="{{ route('admin.investers.index') }}" class="btn btn-secondary">
                                        <i class="ri-arrow-left-line align-middle me-1"></i> Back
                                    </a>
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- Contact & Relationship Info -->
                            <div class="row gy-3">
                                <div class="col-md-3">
                                    <span class="text-muted d-block small">Contact Number</span>
                                    <span class="fw-semibold">{{ $invester->contact ?: '-' }}</span>
                                </div>
                                <div class="col-md-3">
                                    <span class="text-muted d-block small">Primary Email</span>
                                    <span class="fw-semibold">{{ $invester->primary_email ?: '-' }}</span>
                                </div>
                                <div class="col-md-3">
                                    <span class="text-muted d-block small">Alternate Email</span>
                                    <span class="fw-semibold">{{ $invester->alternate_email ?: '-' }}</span>
                                </div>
                                <div class="col-md-3">
                                    <span class="text-muted d-block small">Account Manager / Internal POC</span>
                                    @if(!empty($invester->account_manager_name))
                                        <span class="fw-semibold text-primary d-block">
                                            <i class="ri-user-star-line me-1"></i> {{ $invester->account_manager_name }}
                                        </span>
                                        @if(!empty($invester->account_manager_role))
                                            <span class="badge bg-secondary-subtle text-secondary fs-7 mt-1">{{ $invester->account_manager_role }}</span>
                                        @endif
                                        @if(!empty($invester->account_manager_email) || !empty($invester->account_manager_contact))
                                            <div class="text-muted small mt-1">
                                                @if(!empty($invester->account_manager_email))
                                                    <div><i class="ri-mail-line me-1"></i> {{ $invester->account_manager_email }}</div>
                                                @endif
                                                @if(!empty($invester->account_manager_contact))
                                                    <div><i class="ri-phone-line me-1"></i> {{ $invester->account_manager_contact }}</div>
                                                @endif
                                            </div>
                                        @endif
                                    @elseif($invester->assigned_account_managers->isNotEmpty())
                                        <div class="d-flex flex-wrap gap-1 mt-1">
                                            @foreach($invester->assigned_account_managers as $mgr)
                                                <span class="badge bg-primary-subtle text-primary fs-7">
                                                    <i class="ri-user-star-line me-1"></i>
                                                    {{ $mgr->employee_name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @elseif($invester->accountManager)
                                        <span class="fw-semibold text-primary">
                                            <i class="ri-user-star-line me-1"></i>
                                            {{ $invester->accountManager->employee_name ?: ($invester->accountManager->name ?: 'Unassigned') }}
                                        </span>
                                    @else
                                        <span class="fw-semibold text-muted">Unassigned</span>
                                    @endif
                                </div>
                                <div class="col-md-4">
                                    <span class="text-muted d-block small">Residential / Office Address</span>
                                    <span class="fw-semibold">{{ $invester->address ?: '-' }}</span>
                                </div>
                                <div class="col-md-4">
                                    <span class="text-muted d-block small">Portal Login Account (Role ID 6)</span>
                                    @php $linkedUser = $invester->linkedUser(); @endphp
                                    @if($linkedUser)
                                        <div class="d-flex flex-column gap-1 mt-1">
                                            <div>
                                                <span class="badge bg-success-subtle text-success fs-12">
                                                    <i class="ri-checkbox-circle-line me-1"></i> {{ $linkedUser->email }}
                                                </span>
                                            </div>
                                            <div class="small text-muted">
                                                <span>Username: <strong>{{ $invester->portal_username ?: $linkedUser->name }}</strong></span>
                                                @if(!empty($invester->portal_password))
                                                    <span class="ms-2">| Password: <code class="text-dark">{{ $invester->portal_password }}</code></span>
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning fs-12 mt-1">
                                            <i class="ri-alert-line me-1"></i> Not Created
                                        </span>
                                    @endif
                                </div>
                                <div class="col-md-4">
                                    <span class="text-muted d-block small">Internal Notes</span>
                                    <span class="fw-normal text-secondary">{{ $invester->internal_notes ?: 'No notes available.' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Investor Capital Spend & Balance Summary (4 Cards) -->
            <div class="row g-3 mb-4">
                <!-- Card 1: Total Mapped Talent -->
                <div class="col-xl-3 col-md-6">
                    <div class="card border-top border-3 border-primary shadow-sm h-100 mb-0">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="text-muted text-uppercase fw-bold fs-11">Mapped Talent Count</span>
                                    <h4 class="fw-bold text-dark mt-2 mb-0">{{ $invester->total_mapped_employees }} <span class="fs-13 fw-normal text-muted">Employees</span></h4>
                                    <small class="text-primary mt-1 d-block"><i class="ri-user-shared-line me-1"></i> External Staff Funded</small>
                                </div>
                                <div class="avatar-sm rounded bg-primary-subtle text-primary d-flex align-items-center justify-content-center fs-3">
                                    <i class="ri-team-line"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Monthly Employee Cost / Salary -->
                <div class="col-xl-3 col-md-6">
                    <div class="card border-top border-3 border-info shadow-sm h-100 mb-0">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="text-muted text-uppercase fw-bold fs-11">Monthly Employee Cost</span>
                                    <h4 class="fw-bold text-dark mt-2 mb-0">₹ {{ number_format($invester->monthly_employee_cost, 2) }}</h4>
                                    <small class="text-muted mt-1 d-block"><i class="ri-calendar-check-line me-1"></i> Monthly Salary Spend</small>
                                </div>
                                <div class="avatar-sm rounded bg-info-subtle text-info d-flex align-items-center justify-content-center fs-3">
                                    <i class="ri-calendar-todo-line"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 3: Total Employee Spend (Paid) -->
                <div class="col-xl-3 col-md-6">
                    <div class="card border-top border-3 border-warning shadow-sm h-100 mb-0">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="text-muted text-uppercase fw-bold fs-11">Total Employee Spend</span>
                                    <h4 class="fw-bold text-dark mt-2 mb-0">₹ {{ number_format($invester->total_spend_to_date, 2) }}</h4>
                                    <small class="text-muted mt-1 d-block"><i class="ri-funds-line me-1"></i> Cumulative Paid to Date</small>
                                </div>
                                <div class="avatar-sm rounded bg-warning-subtle text-warning d-flex align-items-center justify-content-center fs-3">
                                    <i class="ri-exchange-dollar-line"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 4: Balance Investment (Minus if spend exceeds capital) -->
                <div class="col-xl-3 col-md-6">
                    @php
                        $bal = $invester->balance_investment;
                    @endphp
                    @if($bal < 0)
                        <div class="card border-top border-3 border-danger shadow-sm h-100 mb-0" style="background: #fff5f5;">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <span class="text-danger text-uppercase fw-bold fs-11">Balance Investment</span>
                                        <h4 class="fw-bold text-danger mt-2 mb-0">- ₹ {{ number_format(abs($bal), 2) }}</h4>
                                        <small class="text-danger mt-1 d-block fw-semibold"><i class="ri-error-warning-fill me-1"></i> Capital Exceeded by ₹ {{ number_format(abs($bal), 2) }}</small>
                                    </div>
                                    <div class="avatar-sm rounded bg-danger text-white d-flex align-items-center justify-content-center fs-3 shadow-sm">
                                        <i class="ri-alert-line"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="card border-top border-3 border-success shadow-sm h-100 mb-0" style="background: #f0fdf4;">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <span class="text-success text-uppercase fw-bold fs-11">Balance Investment</span>
                                        <h4 class="fw-bold text-success mt-2 mb-0">₹ {{ number_format($bal, 2) }}</h4>
                                        <small class="text-success mt-1 d-block fw-medium"><i class="ri-checkbox-circle-fill me-1"></i> Capital Buffer Available</small>
                                    </div>
                                    <div class="avatar-sm rounded bg-success-subtle text-success d-flex align-items-center justify-content-center fs-3">
                                        <i class="ri-wallet-3-line"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Scope Tabs & Associated Masters -->
            <div class="row">
                <!-- 1. Team & Management Scope -->
                <div class="col-lg-12 mb-4">
                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between bg-light">
                            <h5 class="card-title mb-0">
                                <i class="ri-team-line me-2 text-primary"></i> Team & Management Scope
                            </h5>
                            @can('create', \App\Models\InvesterTeamManagement::class)
                                <a href="{{ route('admin.invester-team-managements.create') }}?invester_id={{ $invester->id }}" class="btn btn-sm btn-outline-primary">
                                    <i class="ri-add-line align-middle me-1"></i> Add Team Record
                                </a>
                            @endcan
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Team / Organization Name</th>
                                            <th>Managed Employee Count</th>
                                            <th>Key Operational Contacts / Delegates</th>
                                            <th>Management / Governance Role</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($invester->teamManagements as $team)
                                            <tr>
                                                <td class="fw-semibold">{{ $team->team_name }}</td>
                                                <td><span class="badge bg-secondary-subtle text-secondary">{{ $team->managed_employee_count ?: '-' }}</span></td>
                                                <td>{{ $team->operational_contacts ?: '-' }}</td>
                                                <td><span class="badge bg-info-subtle text-info">{{ $team->governance_role ?: '-' }}</span></td>
                                                <td>
                                                    @if($team->status)
                                                        <span class="badge bg-success-subtle text-success">Active</span>
                                                    @else
                                                        <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @can('edit', \App\Models\InvesterTeamManagement::class)
                                                        <a href="{{ route('admin.invester-team-managements.edit', $team->id) }}" class="text-info fs-4 me-2" title="Edit"><i class="bx bxs-edit"></i></a>
                                                    @endcan
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted py-3">No team & management records linked yet.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Financial & Investment Details -->
                <div class="col-lg-12 mb-4">
                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between bg-light">
                            <h5 class="card-title mb-0">
                                <i class="ri-money-dollar-circle-line me-2 text-primary"></i> Financial & Investment Details
                            </h5>
                            @can('create', \App\Models\InvesterFinancialDetail::class)
                                <a href="{{ route('admin.invester-financial-details.create') }}?invester_id={{ $invester->id }}" class="btn btn-sm btn-outline-primary">
                                    <i class="ri-add-line align-middle me-1"></i> Add Investment Record
                                </a>
                            @endcan
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Total Investment / Committed Capital</th>
                                            <th>Equity Ownership %</th>
                                            <th>Date of Investment</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($invester->financialDetails as $financial)
                                            <tr>
                                                <td class="fw-semibold text-success">
                                                    {{ $financial->investment_amount !== null ? number_format((float) $financial->investment_amount, 2) : '-' }}
                                                </td>
                                                <td>{{ $financial->equity_percentage !== null ? number_format((float) $financial->equity_percentage, 2) . '%' : '-' }}</td>
                                                <td>{{ $financial->investment_date ? $financial->investment_date->format('Y-m-d') : '-' }}</td>
                                                <td>
                                                    @if($financial->status)
                                                        <span class="badge bg-success-subtle text-success">Active</span>
                                                    @else
                                                        <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @can('edit', \App\Models\InvesterFinancialDetail::class)
                                                        <a href="{{ route('admin.invester-financial-details.edit', $financial->id) }}" class="text-info fs-4 me-2" title="Edit"><i class="bx bxs-edit"></i></a>
                                                    @endcan
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted py-3">No financial & investment details records linked yet.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. Invest Modules: Employment Details & Profit Sharing -->
                <div class="col-lg-12 mb-4">
                    <div class="card">
                        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2 bg-light">
                            <div>
                                <h5 class="card-title mb-0">
                                    <i class="ri-briefcase-4-line me-2 text-primary"></i> Employment Details & Profit Sharing Deployments
                                </h5>
                                <small class="text-muted">Deployed employees funded by this investor with margin calculation and profit sharing model</small>
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                <a href="{{ route('admin.invester-deployments.create') }}?invester_id={{ $invester->id }}" class="btn btn-sm btn-primary">
                                    <i class="ri-add-line align-middle me-1"></i> Add Deployment / Invest Module
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Quick totals -->
                            @php
                                $activeDeployments = $invester->deployments->where('status', true);
                                $totalRateCard = $activeDeployments->sum('approved_rate_card');
                                $totalMargin = $activeDeployments->sum('margin_amount');
                                $totalSzShare = $activeDeployments->sum('szorzo_commission_amount');
                                $totalInvShare = $activeDeployments->sum('investor_commission_amount');
                            @endphp
                            <div class="row g-2 mb-3">
                                <div class="col-md-3">
                                    <div class="p-2 border rounded bg-light text-center">
                                        <div class="text-muted small">Active Deployments</div>
                                        <div class="fw-bold fs-5">{{ $activeDeployments->count() }} Employees</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="p-2 border rounded bg-light text-center">
                                        <div class="text-muted small">Total Margin Generated</div>
                                        <div class="fw-bold fs-5 text-warning-emphasis">₹ {{ number_format($totalMargin, 2) }}</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="p-2 border rounded bg-light text-center">
                                        <div class="text-muted small">SZ Share (70%)</div>
                                        <div class="fw-bold fs-5 text-primary">₹ {{ number_format($totalSzShare, 2) }}</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="p-2 border rounded bg-light text-center">
                                        <div class="text-muted small">Investor Share (30%)</div>
                                        <div class="fw-bold fs-5 text-success">₹ {{ number_format($totalInvShare, 2) }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Employee Name</th>
                                            <th>Deployed To (Client)</th>
                                            <th>Duration</th>
                                            <th>Employee CTC (₹)</th>
                                            <th>Margin %</th>
                                            <th class="table-warning">Margin In Rupees</th>
                                            <th>Profit Sharing (SZ / Investor)</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($invester->deployments as $dep)
                                            <tr>
                                                <td class="fw-semibold">
                                                    @php
                                                        $showEmps = $dep->assigned_employees;
                                                        $showClients = $dep->assigned_clients;
                                                    @endphp
                                                    @if($showEmps->count() > 1)
                                                        <span class="badge bg-primary-subtle text-primary mb-1">
                                                            <i class="ri-team-line me-1"></i> {{ $showEmps->count() }} Employees
                                                        </span>
                                                        <small class="text-muted d-block">{{ Str::limit($dep->employee_names, 40) }}</small>
                                                    @elseif($showEmps->count() === 1)
                                                        {{ $showEmps->first()->employee_name }}
                                                        <span class="badge bg-secondary-subtle text-secondary fs-11 d-block mt-1">
                                                            {{ $showEmps->first()->employee_no ?: 'SZ' . str_pad($showEmps->first()->id, 3, '0', STR_PAD_LEFT) }}
                                                        </span>
                                                    @elseif($dep->employee)
                                                        {{ $dep->employee->employee_name }}
                                                        <span class="badge bg-secondary-subtle text-secondary fs-11 d-block mt-1">
                                                            {{ $dep->employee->employee_no ?: 'SZ' . str_pad($dep->employee->id, 3, '0', STR_PAD_LEFT) }}
                                                        </span>
                                                    @else
                                                        <span class="text-muted">Unassigned</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($showClients->count() > 1)
                                                        <span class="badge bg-info-subtle text-info mb-1">
                                                            <i class="ri-building-line me-1"></i> {{ $showClients->count() }} Clients
                                                        </span>
                                                        <small class="text-muted d-block">{{ Str::limit($dep->client_names, 35) }}</small>
                                                    @elseif($showClients->count() === 1)
                                                        <span class="fw-medium">{{ $showClients->first()->client }}</span>
                                                    @elseif($dep->client)
                                                        <span class="fw-medium">{{ $dep->client->client }}</span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>{{ $dep->contract_duration ?: '-' }}</td>
                                                <td>₹ {{ number_format((float) $dep->employee_ctc, 2) }}</td>
                                                <td>{{ number_format((float) $dep->margin_percentage, 2) }}%</td>
                                                <td class="table-warning fw-bold text-dark">
                                                    ₹ {{ number_format((float) $dep->margin_amount, 2) }}
                                                </td>
                                                <td>
                                                    <div class="small">
                                                        <span class="text-primary fw-semibold">SZ ({{ number_format((float) $dep->szorzo_share_percentage, 0) }}%):</span> ₹ {{ number_format((float) $dep->szorzo_commission_amount, 2) }}
                                                    </div>
                                                    <div class="small">
                                                        <span class="text-success fw-semibold">Inv ({{ number_format((float) $dep->investor_share_percentage, 0) }}%):</span> ₹ {{ number_format((float) $dep->investor_commission_amount, 2) }}
                                                    </div>
                                                </td>
                                                <td>
                                                    @if($dep->status)
                                                        <span class="badge bg-success-subtle text-success">Active</span>
                                                    @else
                                                        <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ route('admin.invester-deployments.edit', $dep->id) }}" class="text-info fs-5 me-2" title="Edit"><i class="bx bxs-edit"></i></a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="9" class="text-center text-muted py-4">
                                                    No deployment records linked yet. Click "Add Deployment / Invest Module" to link employee employment details.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 4. Legal, Compliance & Governance -->
                <div class="col-lg-12 mb-4">
                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between bg-light">
                            <h5 class="card-title mb-0">
                                <i class="ri-shield-check-line me-2 text-primary"></i> Legal, Compliance & Governance
                            </h5>
                            @can('create', \App\Models\InvesterLegalCompliance::class)
                                <a href="{{ route('admin.invester-legal-compliances.create') }}?invester_id={{ $invester->id }}" class="btn btn-sm btn-outline-primary">
                                    <i class="ri-add-line align-middle me-1"></i> Add Legal / Compliance Record
                                </a>
                            @endcan
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Tax ID / PAN / SSN</th>
                                            <th>KYC / AML Status</th>
                                            <th>NDA / Legal Agreements Signed (Document)</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($invester->legalCompliances as $legal)
                                            <tr>
                                                <td class="fw-semibold">{{ $legal->tax_id_number ?: '-' }}</td>
                                                <td>
                                                    @php
                                                        $badgeClass = match ($legal->kyc_aml_status) {
                                                            'Verified' => 'bg-success-subtle text-success',
                                                            'Rejected' => 'bg-danger-subtle text-danger',
                                                            default => 'bg-warning-subtle text-warning',
                                                        };
                                                    @endphp
                                                    <span class="badge {{ $badgeClass }}">{{ $legal->kyc_aml_status }}</span>
                                                </td>
                                                <td>
                                                    @if($legal->nda_signed_doc && file_exists(public_path($legal->nda_signed_doc)))
                                                        <a href="{{ asset($legal->nda_signed_doc) }}" target="_blank" class="btn btn-sm btn-soft-primary">
                                                            <i class="ri-download-2-line align-middle me-1"></i> Download Agreement
                                                        </a>
                                                    @else
                                                        <span class="text-muted">No attachment</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($legal->status)
                                                        <span class="badge bg-success-subtle text-success">Active</span>
                                                    @else
                                                        <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @can('edit', \App\Models\InvesterLegalCompliance::class)
                                                        <a href="{{ route('admin.invester-legal-compliances.edit', $legal->id) }}" class="text-info fs-4 me-2" title="Edit"><i class="bx bxs-edit"></i></a>
                                                    @endcan
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-3">No legal, compliance & governance records linked yet.</td>
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

