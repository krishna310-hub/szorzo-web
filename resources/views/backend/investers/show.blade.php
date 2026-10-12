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
                                    <span class="fw-semibold text-primary">
                                        <i class="ri-user-star-line me-1"></i>
                                        {{ $invester->accountManager?->name ?: 'Unassigned' }}
                                    </span>
                                </div>
                                <div class="col-md-6">
                                    <span class="text-muted d-block small">Residential / Office Address</span>
                                    <span class="fw-semibold">{{ $invester->address ?: '-' }}</span>
                                </div>
                                <div class="col-md-6">
                                    <span class="text-muted d-block small">Internal Notes</span>
                                    <span class="fw-normal text-secondary">{{ $invester->internal_notes ?: 'No notes available.' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
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
                                            <th>Shares / Units Held</th>
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
                                                <td>{{ $financial->shares_units_held ?: '-' }}</td>
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

                <!-- 3. Legal, Compliance & Governance -->
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

