@extends('backend.layouts.master')

@section('title', 'Recruitment Dashboard')

@section('content')
    <style>
        .dashboard-shell {
            background: linear-gradient(145deg, #f8fafc 0%, #fff7f7 48%, #f1f5f9 100%);
            min-height: 100vh;
        }

        .dashboard-hero {
            border-radius: 24px;
            color: #fff;
            background: linear-gradient(120deg, #171923, #4a1621 65%, #b91c1c);
            box-shadow: 0 18px 45px rgba(31, 41, 55, .16);
            overflow: hidden;
            position: relative;
        }

        .dashboard-hero:after {
            content: '';
            position: absolute;
            width: 260px;
            height: 260px;
            right: -65px;
            top: -100px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .09);
        }

        .scope-pill {
            display: inline-flex;
            gap: 7px;
            align-items: center;
            padding: 7px 12px;
            border-radius: 20px;
            background: rgba(255, 255, 255, .13);
            font-size: .8rem;
        }

        .metric-card,
        .panel-card {
            border: 0;
            border-radius: 20px;
            box-shadow: 0 8px 30px rgba(15, 23, 42, .06);
            height: 100%;
        }

        .metric-card {
            transition: transform .2s, box-shadow .2s;
            overflow: hidden;
        }

        .metric-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 14px 35px rgba(15, 23, 42, .1);
        }

        .metric-icon {
            width: 70px;
            height: 70px;
            border-radius: 18px;
            /* background: #fde8e8; */
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: visible;
        }

        .metric-logo {
            width: 80px;
            height: auto;
            transform: translate(0px, 8px);
        }

        .metric-value {
            font-size: 1.9rem;
            font-weight: 800;
            color: #111827;
            line-height: 1;
        }

        .metric-label {
            color: #64748b;
            font-size: .78rem;
            text-transform: uppercase;
            letter-spacing: .55px;
            font-weight: 700;
        }

        .requirements-card {
            position: relative;
            background: linear-gradient(145deg, #ffffff 0%, #fff7f7 100%);
        }

        .requirements-card:before {
            content: '';
            position: absolute;
            inset: 0 auto 0 0;
            width: 4px;
            background: linear-gradient(180deg, #ef4444, #991b1b);
        }

        .requirement-stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 8px;
        }

        .requirement-stat {
            min-width: 0;
            padding: 10px;
            border: 1px solid #fee2e2;
            border-radius: 12px;
            background: rgba(255, 255, 255, .8);
            text-align: center;
        }

        .requirement-stat-label {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            margin-bottom: 5px;
            color: #64748b;
            font-size: .7rem;
            font-weight: 700;
        }

        .requirement-status-dot {
            width: 7px;
            height: 7px;
            flex: 0 0 7px;
            border-radius: 50%;
        }

        .requirement-stat-value {
            overflow: hidden;
            color: #111827;
            font-size: 1.35rem;
            font-weight: 800;
            line-height: 1;
            text-overflow: ellipsis;
        }

        .interview-summary {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 10px;
        }

        .interview-stats {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 8px;
            margin-top: 20px;
        }

        .interview-stat {
            min-width: 0;
            padding: 8px 6px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: #f8fafc;
            text-align: center;
        }

        .interview-stat-label {
            display: block;
            margin-bottom: 3px;
            color: #64748b;
            font-size: .7rem;
            font-weight: 700;
        }

        .interview-stat-value {
            color: #111827;
            font-size: 1rem;
            font-weight: 800;
            line-height: 1;
        }

        @media (max-width: 767.98px) {
            .interview-stats {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        .section-title {
            font-weight: 800;
            color: #172033;
        }

        .pipeline-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
        }

        .pipeline-item {
            min-width: 0;
            padding: 16px;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            background: linear-gradient(145deg, #fff 0%, #f8fafc 100%);
        }

        .pipeline-item-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 12px;
        }

        .pipeline-item-label {
            min-width: 0;
            overflow: hidden;
            color: #334155;
            font-size: .82rem;
            font-weight: 700;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .pipeline-item-value {
            flex: 0 0 auto;
            color: #111827;
            font-size: 1.2rem;
            font-weight: 800;
            line-height: 1;
        }

        .pipeline-track {
            height: 9px;
            border-radius: 20px;
            background: #f1f5f9;
            overflow: hidden;
        }

        .pipeline-fill {
            height: 100%;
            border-radius: 20px;
            background: linear-gradient(90deg, #ef4444, #7f1d1d);
        }

        .pipeline-empty {
            grid-column: 1 / -1;
        }

        @media (max-width: 991.98px) {
            .pipeline-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 575.98px) {
            .pipeline-grid {
                grid-template-columns: 1fr;
            }
        }

        .interview-item {
            padding: 14px 0;
            border-bottom: 1px solid #edf2f7;
        }

        .interview-item:last-child {
            border: 0;
        }

        .date-box {
            min-width: 52px;
            text-align: center;
            border-radius: 13px;
            padding: 7px;
            background: #fff1f2;
            color: #991b1b;
        }

        .target-panel {
            position: relative;
            overflow: hidden;
            background: #fff;
        }

        .target-panel:before {
            content: '';
            position: absolute;
            inset: 0 0 auto 0;
            height: 4px;
            background: linear-gradient(90deg, #b91c1c, #ef4444, #f59e0b);
        }

        .target-month {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 13px;
            border: 1px solid #fee2e2;
            border-radius: 12px;
            color: #991b1b;
            background: #fff7f7;
            font-size: .82rem;
            font-weight: 700;
        }

        .target-summary {
            height: 100%;
            padding: 20px;
            border: 1px solid #edf2f7;
            border-radius: 16px;
            background: linear-gradient(145deg, #fff, #fafafa);
        }

        .target-summary-value {
            color: #172033;
            font-size: 1.65rem;
            font-weight: 800;
            line-height: 1.1;
        }

        .target-progress {
            height: 8px;
            overflow: hidden;
            border-radius: 20px;
            background: #f1f5f9;
        }

        .target-progress-bar {
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, #991b1b, #ef4444);
        }

        .individual-target-summary-value.percentage-green {
            color: #15803d;
        }

        .individual-target-summary-value.percentage-blue {
            color: #2563eb;
        }

        .individual-target-summary-value.percentage-red {
            color: #dc2626;
        }

        .target-progress-bar.percentage-green {
            background: linear-gradient(90deg, #16a34a, #22c55e);
        }

        .target-progress-bar.percentage-blue {
            background: linear-gradient(90deg, #2563eb, #60a5fa);
        }

        .target-progress-bar.percentage-red {
            background: linear-gradient(90deg, #dc2626, #ef4444);
        }

        .individual-target-summary-value {
            color: #172033;
            font-size: 1.65rem;
            font-weight: 800;
            line-height: 1.1;
        }

        .kpi-card {
            height: 100%;
            padding: 18px;
            border: 1px solid #edf2f7;
            border-radius: 16px;
            transition: border-color .2s, transform .2s;
        }

        .kpi-card:hover {
            border-color: #fecaca;
            transform: translateY(-2px);
        }

        .kpi-icon {
            width: 40px;
            height: 40px;
            display: grid;
            place-items: center;
            flex: 0 0 40px;
            border-radius: 12px;
            color: #b91c1c;
            background: #fff1f2;
            font-size: 1.15rem;
        }

        .role-performance-row {
            display: grid;
            grid-template-columns: minmax(150px, 1.4fr) 2fr 70px 60px 110px;
            gap: 16px;
            align-items: center;
            padding: 14px 0;
            border-bottom: 1px solid #edf2f7;
        }

        .role-performance-row:last-child {
            border-bottom: 0;
        }

        .role-avatar {
            width: 38px;
            height: 38px;
            display: grid;
            place-items: center;
            flex: 0 0 38px;
            border-radius: 50%;
            color: #fff;
            background: linear-gradient(145deg, #172033, #b91c1c);
            font-weight: 800;
        }

        .analytics-chart-wrap {
            min-height: 330px;
            padding: 8px 4px 0;
        }

        .analytics-person {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 15px;
            border: 1px solid #fee2e2;
            border-radius: 14px;
            background: #fff7f7;
        }

        .analytics-note {
            padding: 12px 14px;
            border-radius: 12px;
            color: #475569;
            background: #f8fafc;
            font-size: .78rem;
        }

        @media (max-width: 576px) {
            .dashboard-hero {
                border-radius: 16px;
            }

            .role-performance-row {
                grid-template-columns: 1fr 60px;
            }

            .role-performance-row .role-progress-column {
                grid-column: 1 / -1;
                grid-row: 2;
            }

            .role-performance-row .role-target-column {
                display: none;
            }

            .role-performance-row>strong {
                grid-column: 1;
                grid-row: 3;
                text-align: left !important;
            }

            .role-performance-row>.btn {
                grid-column: 2;
                grid-row: 3;
            }
        }
    </style>

    <div class="main-content dashboard-shell">
        <div class="page-content">
            <div class="container-fluid">
                <div class="dashboard-hero p-4 p-lg-5 mb-4">
                    <div class="position-relative" style="z-index:1">
                        <div class="scope-pill mb-3"><i class="ri-shield-user-line"></i>{{ $scopeLabel }}</div>
                        <h2 class="fw-bold text-white mb-2">Welcome back, {{ auth()->user()->name }}</h2>
                        <p class="mb-0 text-white-50">A live view of requirements, applicants and interviews available to
                            your role.</p>
                    </div>
                </div>

                <div class="card panel-card mb-4">
                    <div class="card-body p-3 p-lg-4">
                        <form method="GET" action="{{ route('admin.dashboard') }}" class="row g-3 align-items-end">
                            <div class="col-md-6 col-xl-{{ $showClientFilter ? '2' : '3' }}">
                                <label for="dashboard_recruiter_id" class="form-label fw-semibold">Recruiter</label>
                                @if ($isRecruiterDashboard)
                                    <select id="dashboard_recruiter_id" class="form-select" disabled>
                                        <option selected>
                                            {{ $linkedRecruiter?->recruiter_name ?? 'Recruiter not linked' }}
                                        </option>
                                    </select>
                                    @if ($linkedRecruiter)
                                        <input type="hidden" name="recruiter_id" value="{{ $linkedRecruiter->id }}">
                                    @endif
                                @else
                                    <select id="dashboard_recruiter_id" name="recruiter_id" class="form-select">
                                        <option value="">All recruiters</option>
                                        @foreach ($recruiters as $recruiter)
                                            <option value="{{ $recruiter->id }}" @selected((int) $selectedRecruiterId === $recruiter->id)>
                                            {{ $recruiter->recruiter_name }}</option>
                                        @endforeach
                                    </select>
                                @endif
                            </div>
                            @if ($showClientFilter)
                                <div class="col-md-6 col-xl-2">
                                    <label for="dashboard_client_id" class="form-label fw-semibold">Client</label>
                                    <select id="dashboard_client_id" name="client_id" class="form-select">
                                        <option value="">All clients</option>
                                        @foreach ($clients as $client)
                                            <option value="{{ $client->id }}" @selected((int) $selectedClientId === $client->id)>
                                                {{ $client->client }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                            <div class="col-md-6 col-xl-{{ $isRecruiterDashboard ? '4' : '3' }}">
                                <label for="dashboard_from_date" class="form-label fw-semibold">From Date</label>
                                <input type="date" id="dashboard_from_date" name="dashboard_from_date"
                                    class="form-control {{ $fromDateError ? 'is-invalid' : '' }}"
                                    value="{{ $selectedFromDate }}">
                                @if ($fromDateError)
                                    <div class="invalid-feedback">{{ $fromDateError }}</div>
                                @endif
                            </div>
                            <div class="col-md-6 col-xl-{{ $isRecruiterDashboard ? '4' : '3' }}">
                                <label for="dashboard_to_date" class="form-label fw-semibold">To Date</label>
                                <input type="date" id="dashboard_to_date" name="dashboard_to_date"
                                    class="form-control {{ $toDateError ? 'is-invalid' : '' }}"
                                    value="{{ $selectedToDate }}">
                                @if ($toDateError)
                                    <div class="invalid-feedback">{{ $toDateError }}</div>
                                @endif
                            </div>
                            <div
                                class="col-md-6 col-xl-{{ $isRecruiterDashboard ? '4' : ($showClientFilter ? '2' : '3') }} d-flex gap-2">
                                <button type="submit" class="btn btn-danger flex-grow-1">
                                    <i class="ri-filter-3-line me-1"></i>Apply
                                </button>
                                @if ($selectedFromDate || $selectedToDate)
                                    <a href="{{ route(
                                        'admin.dashboard',
                                        array_filter([
                                            'recruiter_id' => $isRecruiterDashboard ? null : $selectedRecruiterId,
                                            'client_id' => $selectedClientId,
                                        ]),
                                    ) }}"
                                        class="btn btn-outline-primary" title="Remove date range"
                                        aria-label="Remove date range">
                                        <i class="ri-calendar-close-line"></i>
                                    </a>
                                @endif
                                <a href="{{ route('admin.dashboard') }}" class="btn btn-light" title="Clear all filters"
                                    aria-label="Clear all filters">
                                    <i class="ri-refresh-line"></i>
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                @unless ($recruiterLinked)
                    <div class="alert alert-warning border-0 shadow-sm"><i class="ri-alert-line me-2"></i>Your login email is
                        not linked to a recruiter record, so your personal dashboard currently has no records.</div>
                @endunless

                <div class="row g-3 mb-4">
                    @can('read', \App\Models\ClientRequirement::class)
                        <div class="col-6 col-xl-3">
                            <div class="card metric-card requirements-card">
                                <div class="card-body p-3 p-lg-4">
                                    <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                                        <div class="metric-label">Requirements</div>
                                        <div class="metric-icon">
                                            <img src="{{ asset('frontend/images/adminlogos.png') }}" alt="SZORZO Logo"
                                                class="metric-logo">
                                        </div>
                                    </div>
                                    <div class="requirement-stats">
                                        <div class="requirement-stat">
                                            <div class="requirement-stat-label">
                                                <span class="requirement-status-dot bg-success"></span>Active
                                            </div>
                                            <div class="requirement-stat-value">{{ number_format($activeRequirements ?? 0) }}
                                            </div>
                                        </div>
                                        <div class="requirement-stat">
                                            <div class="requirement-stat-label">
                                                <span class="requirement-status-dot bg-warning"></span>Priority
                                            </div>
                                            <div class="requirement-stat-value">{{ number_format($priorityRequirements ?? 0) }}
                                            </div>
                                        </div>
                                        <div class="requirement-stat">
                                            <div class="requirement-stat-label">
                                                <span class="requirement-status-dot bg-secondary"></span>Inactive
                                            </div>
                                            <div class="requirement-stat-value">{{ number_format($inActiveRequirements ?? 0) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endcan
                    @can('read', \App\Models\Candidate::class)
                        <div class="col-6 col-xl-3">
                            <div class="card metric-card">
                                <div class="card-body p-3 p-lg-4">
                                    <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                                        <div class="metric-label">Candidate Stage</div>
                                        <div class="metric-icon">
                                            <img src="{{ asset('frontend/images/adminlogos.png') }}" alt="SZORZO Logo"
                                                class="metric-logo">
                                        </div>
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <div class="border rounded-3 bg-light p-2 h-100">
                                                <div class="small text-muted mb-1">Applicants</div>
                                                <div class="metric-value">{{ number_format($myApplicants ?? 0) }}</div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="border rounded-3 bg-light p-2 h-100">
                                                <div class="small text-muted mb-1">HR Selected</div>
                                                <div class="metric-value">{{ number_format($hrSelected ?? 0) }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-xl-3">
                            <div class="card metric-card">
                                <div class="card-body p-3 p-lg-4">
                                    <div class="interview-summary">
                                        <div>
                                            <div class="metric-label mb-2">Interview Stage</div>
                                            <div class="metric-value">
                                                {{ number_format($candidateInterviewStages->count()) }}
                                            </div>
                                        </div>
                                        <div class="metric-icon">
                                            <img src="{{ asset('frontend/images/adminlogos.png') }}" alt="SZORZO Logo"
                                                class="metric-logo">
                                        </div>
                                    </div>

                                    <div class="interview-stats">
                                        <div class="interview-stat">
                                            <span class="interview-stat-label">L1</span>
                                            <div class="interview-stat-value">
                                                {{ number_format($candidateInterviewStages->whereIn('level_of_interview_id', [7, 8])->count()) }}
                                            </div>
                                        </div>
                                        <div class="interview-stat">
                                            <span class="interview-stat-label">L2</span>
                                            <div class="interview-stat-value">
                                                {{ number_format($candidateInterviewStages->whereIn('level_of_interview_id', [11, 12])->count()) }}
                                            </div>
                                        </div>
                                        <div class="interview-stat">
                                            <span class="interview-stat-label">L3</span>
                                            <div class="interview-stat-value">
                                                {{ number_format($candidateInterviewStages->whereIn('level_of_interview_id', [23, 25])->count()) }}
                                            </div>
                                        </div>
                                        <div class="interview-stat">
                                            <span class="interview-stat-label">L4</span>
                                            <div class="interview-stat-value">
                                                {{ number_format($candidateInterviewStages->whereIn('level_of_interview_id', [27, 28])->count()) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-xl-3">
                            <div class="card metric-card">
                                <div class="card-body p-3 p-lg-4">
                                    <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                                        <div class="metric-label">Onboarding Stage</div>
                                        <div class="metric-icon">
                                            <img src="{{ asset('frontend/images/adminlogos.png') }}" alt="SZORZO Logo"
                                                class="metric-logo">
                                        </div>
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <div class="border rounded-3 bg-light p-2 h-100">
                                                <div class="small text-muted mb-1">Offered</div>
                                                <div class="metric-value">{{ number_format($offered ?? 0) }}</div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="border rounded-3 bg-light p-2 h-100">
                                                <div class="small text-muted mb-1">Onboarded</div>
                                                <div class="metric-value">{{ number_format($onboarded ?? 0) }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endcan
                </div>

                @php
                    $dashboardRoleId = (int) auth()->user()->role_id;
                    $isSuperAdminDashboard = $dashboardRoleId === 1;
                    $isDeliveryLeadDashboard = $dashboardRoleId === 2;
                    $selectedDashboardRecruiter = $selectedRecruiterId
                        ? $recruiters->firstWhere('id', (int) $selectedRecruiterId)
                        : null;
                    $individualAnalyticsVisible = $isRecruiterDashboard || (bool) $selectedDashboardRecruiter;
                    $individualAnalyticsName = $isRecruiterDashboard
                        ? auth()->user()->name
                        : $selectedDashboardRecruiter?->recruiter_name ?? 'All recruiters';
                    $monthlyKpis = $monthlyTargetAnalytics['kpis'];
                @endphp

                <div class="card panel-card target-panel mb-4">
                    <div class="card-body p-3 p-lg-4">
                        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
                            <div>
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                    <h5 class="section-title mb-0">Monthly Target Analytics</h5>
                                    <span class="badge bg-success-subtle text-success">Live records</span>
                                </div>
                                <p class="text-muted small mb-0">
                                    @if ($isSuperAdminDashboard)
                                        All-role performance visibility for Super Admin
                                    @elseif ($isDeliveryLeadDashboard)
                                        {{ $selectedDashboardRecruiter ? $selectedDashboardRecruiter->recruiter_name . ' target and process completion' : 'Recruiter-DL team target and process completion' }}
                                    @else
                                        Your individual monthly target and process completion
                                    @endif
                                </p>
                            </div>
                            <div class="target-month"><i class="ri-calendar-2-line"></i>{{ now()->format('F Y') }}</div>
                        </div>

                        {{-- <div class="row g-3 mb-4">
                            <div class="col-sm-6 col-xl-3">
                                <div class="target-summary">
                                    <div class="metric-label mb-2">Overall Completion</div>
                                    <div class="d-flex justify-content-between align-items-end mb-3">
                                        <div class="target-summary-value">{{ $monthlyTargetAnalytics['overallPercentage'] }}%</div><small class="text-muted">{{ $monthlyTargetAnalytics['targetMultiplier'] > 1 ? $monthlyTargetAnalytics['targetMultiplier'].' recruiters' : 'Monthly target' }}</small>
                                    </div>
                                    <div class="target-progress"><div class="target-progress-bar" style="width:{{ $monthlyTargetAnalytics['overallPercentage'] }}%"></div></div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-xl-3">
                                <div class="target-summary">
                                    <div class="metric-label mb-2">Process Completed</div>
                                    <div class="target-summary-value">{{ $monthlyTargetAnalytics['completedProcesses'] }} <small class="fs-6 fw-normal text-muted">/ 7 KPIs</small></div>
                                    <div class="small text-muted mt-3"><i class="ri-checkbox-circle-line me-1"></i>Monthly workflow</div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-xl-3">
                                <div class="target-summary">
                                    <div class="metric-label mb-2">Offers Progress</div>
                                    <div class="target-summary-value">{{ $monthlyTargetAnalytics['offers']['completed'] }} <small class="fs-6 fw-normal text-muted">/ {{ $monthlyTargetAnalytics['offers']['target'] }}</small></div>
                                    <div class="small text-muted mt-3"><i class="ri-draft-line me-1"></i>Minimum to stretch target</div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-xl-3">
                                <div class="target-summary">
                                    <div class="metric-label mb-2">Joining Progress</div>
                                    <div class="target-summary-value">{{ $monthlyTargetAnalytics['joining']['completed'] }} <small class="fs-6 fw-normal text-muted">/ {{ $monthlyTargetAnalytics['joining']['target'] }}</small></div>
                                    <div class="small text-muted mt-3"><i class="ri-user-add-line me-1"></i>Monthly joiners</div>
                                </div>
                            </div>
                        </div> --}}

                        <div class="row g-3">
                            @foreach ($monthlyKpis as $kpi)
                                @php
                                    $percentageColor =
                                        $kpi['percentage'] > 80
                                            ? 'percentage-green'
                                            : ($kpi['percentage'] >= 60
                                                ? 'percentage-blue'
                                                : 'percentage-red');
                                @endphp
                                <div class="col-md-6 col-xl-4">
                                    <div class="kpi-card">
                                        <div class="d-flex justify-content-between gap-3 mb-3">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="kpi-icon"><i class="{{ $kpi['icon'] }}"></i></div>
                                                <div>
                                                    <div class="fw-bold text-dark">{{ $kpi['label'] }}</div><small
                                                        class="text-muted">Monthly target</small>
                                                </div>
                                            </div>
                                            <div class="text-end"><strong
                                                    class="text-dark">{{ $kpi['target'] }}</strong><small
                                                    class="d-block text-muted">{{ $kpi['unit'] }}</small></div>
                                        </div>
                                        <div class="d-flex justify-content-between small mb-2"><span
                                                class="text-muted">Completed:
                                                {{ number_format($kpi['completed']) }}</span><strong
                                                class="individual-target-summary-value {{ $percentageColor }}">{{ $kpi['percentage'] }}%</strong>
                                        </div>
                                        <div class="target-progress">
                                            <div class="target-progress-bar {{ $percentageColor }}"
                                                style="width:{{ $kpi['percentage'] }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @if ($isSuperAdminDashboard || $isDeliveryLeadDashboard)
                            <div class="mt-4 pt-4 border-top">
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                                    <div>
                                        <h6 class="section-title mb-1">All Roles Performance</h6><span
                                            class="text-muted small">Role-wise monthly target completion</span>
                                    </div>
                                    <span
                                        class="badge bg-light text-dark">{{ $isDeliveryLeadDashboard ? 'Recruiter-DL view' : 'Super Admin view' }}</span>
                                </div>
                                <div class="role-performance-row">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="role-avatar">DL</div>
                                        <div>
                                            <div class="fw-semibold text-dark">Recruiter - DL</div><small
                                                class="text-muted">Delivery leadership</small>
                                        </div>
                                    </div>
                                    <div class="role-progress-column">
                                        <div class="target-progress">
                                            <div class="target-progress-bar"
                                                style="width:{{ $deliveryLeadAnalytics['overallPercentage'] }}%"></div>
                                        </div>
                                    </div>
                                    <div class="role-target-column text-muted small">7 KPIs</div><strong
                                        class="text-end">{{ $deliveryLeadAnalytics['overallPercentage'] }}%</strong>
                                    <a href="{{ route('admin.dashboard') }}"
                                        class="btn btn-sm {{ $selectedRecruiterId ? 'btn-outline-danger' : 'btn-danger' }}">Overview</a>
                                </div>
                                @forelse ($recruiters as $recruiter)
                                    <div class="role-performance-row">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="role-avatar">
                                                {{ strtoupper(substr($recruiter->recruiter_name, 0, 1)) }}</div>
                                            <div>
                                                <div class="fw-semibold text-dark">{{ $recruiter->recruiter_name }}</div>
                                                <small class="text-muted">Recruiter</small>
                                            </div>
                                        </div>
                                        <div class="role-progress-column">
                                            <div class="target-progress">
                                                <div class="target-progress-bar"
                                                    style="width:{{ $recruiterPerformance[$recruiter->id] ?? 0 }}%"></div>
                                            </div>
                                        </div>
                                        <div class="role-target-column text-muted small">7 KPIs</div><strong
                                            class="text-end">{{ $recruiterPerformance[$recruiter->id] ?? 0 }}%</strong>
                                        <a href="{{ route('admin.dashboard', ['recruiter_id' => $recruiter->id]) }}"
                                            class="btn btn-sm {{ (int) $selectedRecruiterId === $recruiter->id ? 'btn-danger' : 'btn-outline-danger' }}">
                                            <i
                                                class="ri-line-chart-line me-1"></i>{{ (int) $selectedRecruiterId === $recruiter->id ? 'Viewing' : 'Analytics' }}
                                        </a>
                                    </div>
                                @empty
                                    <div class="text-center text-muted py-4"><i
                                            class="ri-team-line fs-2 d-block mb-1"></i>No recruiter rows available for
                                        preview</div>
                                @endforelse
                            </div>
                        @endif
                    </div>
                </div>

                @if ($individualAnalyticsVisible)
                    <div class="card panel-card mb-4 target-panel">
                        <div class="card-body p-3 p-lg-4">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
                                <div>
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <h5 class="section-title mb-0">Individual Recruiter Analytics</h5>
                                        <span class="badge bg-success-subtle text-success">Live records</span>
                                    </div>
                                    <p class="text-muted small mb-0">Monthly candidate-profile activity and visible
                                        recruitment pipeline</p>
                                </div>
                                <div class="analytics-person">
                                    <div class="role-avatar">{{ strtoupper(substr($individualAnalyticsName, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $individualAnalyticsName }}</div><small
                                            class="text-muted">{{ $isRecruiterDashboard ? 'My analytics' : 'Selected recruiter' }}</small>
                                    </div>
                                </div>
                            </div>

                            {{-- <div class="row g-3 mb-3">
                                <div class="col-6 col-lg-3">
                                    <div class="target-summary">
                                        <div class="metric-label mb-2">Visible Applicants</div>
                                        <div class="target-summary-value">{{ number_format($myApplicants) }}</div>
                                    </div>
                                </div>
                                <div class="col-6 col-lg-3">
                                    <div class="target-summary">
                                        <div class="metric-label mb-2">Interview Stage</div>
                                        <div class="target-summary-value">{{ number_format($candidateInterviewStages->count()) }}</div>
                                    </div>
                                </div>
                                <div class="col-6 col-lg-3">
                                    <div class="target-summary">
                                        <div class="metric-label mb-2">Offers</div>
                                        <div class="target-summary-value">{{ number_format($offered) }}</div>
                                    </div>
                                </div>
                                <div class="col-6 col-lg-3">
                                    <div class="target-summary">
                                        <div class="metric-label mb-2">Active Requirements</div>
                                        <div class="target-summary-value">{{ number_format($activeRequirements) }}</div>
                                    </div>
                                </div>
                            </div> --}}

                            <div class="row g-4 align-items-stretch">
                                <div class="col-xl-8">
                                    <div class="border rounded-4 h-100 p-3">
                                        <div
                                            class="d-flex flex-wrap justify-content-between align-items-center gap-2 px-1">
                                            <div>
                                                <h6 class="section-title mb-1">Monthly Profile Completion</h6><span
                                                    class="text-muted small">Candidate profiles recorded during the last
                                                    six months</span>
                                            </div>
                                            <span class="badge bg-danger-subtle text-danger">Target: 100/month</span>
                                        </div>
                                        <div id="individualCompletionChart" class="analytics-chart-wrap"></div>
                                    </div>
                                </div>
                                <div class="col-xl-4">
                                    <div class="border rounded-4 h-100 p-4">
                                        <h6 class="section-title mb-1">Current Month Completion</h6>
                                        <p class="text-muted small mb-4">Against the minimum profile target</p>
                                        <div id="individualRadialChart" style="min-height:220px"></div>
                                        <div class="analytics-note"><i class="ri-information-line me-1"></i>This graph
                                            uses actual candidate records. HR assessments, acceptances and joining will
                                            appear after those data fields are connected.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @elseif ($isSuperAdminDashboard)
                    <div class="card panel-card mb-4 target-panel">
                        <div class="card-body text-center py-5">
                            <div class="metric-icon bg-danger-subtle text-danger mx-auto mb-3"><i
                                    class="ri-user-search-line"></i></div>
                            <h5 class="section-title">Select a recruiter to view individual analytics</h5>
                            <p class="text-muted mb-0">Use the recruiter filter or an Analytics button in the
                                role-performance list.</p>
                        </div>
                    </div>
                @endif
                <div class="row g-4 mb-4">
                    @can('read', \App\Models\Candidate::class)
                        <div class="col-12">
                            <div class="card panel-card target-panel">
                                <div class="card-body p-4">
                                    <h5 class="section-title mb-1">Interview Pipeline</h5>
                                    <p class="text-muted small mb-4">Level-wise interview activity</p>
                                    @php
                                        $maxLevel = max(1, (int) $candidateLevels->max('candidates_count'));
                                    @endphp
                                    <div class="pipeline-groups">

                                        @foreach ($groupedLevels as $group)
                                            <div class="mb-4">

                                                <h5 class="fw-bold text-primary border-bottom pb-2 mb-3">
                                                    {{ $group['title'] }}
                                                </h5>

                                                @if ($group['title'] === 'Monthly Joining Details')
                                                    <div class="border rounded-4 bg-light p-3 p-lg-4">
                                                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                                                            <div>
                                                                <div class="fw-semibold text-dark">Monthly Joining Details</div>
                                                                <small class="text-muted">Offer Accepted (green) and Offer Declined (red) by onboarding month</small>
                                                            </div>
                                                        </div>
                                                        <div id="pipelineMonthlyJoiningBarChart" style="min-height: 250px;"></div>
                                                    </div>
                                                @elseif($group['title'] === 'Monthly Onboarding Details')
                                                    <div class="border rounded-4 bg-light p-3 p-lg-4">
                                                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                                                            <div>
                                                                <div class="fw-semibold text-dark">Monthly Onboarding Details</div>
                                                                <small class="text-muted">Onboarded with Client (green) and Joiner Declined (red) by onboarding month</small>
                                                            </div>
                                                        </div>
                                                        <div id="pipelineMonthlyOnboardingBarChart" style="min-height: 250px;"></div>
                                                    </div>
                                                @else
                                                    <div class="pipeline-grid">

                                                        @foreach ($group['levels'] as $level)
                                                            @php
                                                                $count = (int) $level->candidates_count;
                                                                $percentage = round(($count / $maxLevel) * 100);
                                                            @endphp

                                                            <div class="pipeline-item">
                                                                <div class="pipeline-item-header">
                                                                    <span class="pipeline-item-label">
                                                                        {{ $level->level }}
                                                                    </span>

                                                                    <span class="pipeline-item-value">
                                                                        {{ number_format($count) }}
                                                                    </span>
                                                                </div>

                                                                <div class="pipeline-track">
                                                                    <div class="pipeline-fill"
                                                                        style="width: {{ $percentage }}%">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach

                                                    </div>
                                                @endif

                                            </div>
                                        @endforeach

                                    </div>
                                    {{-- <div class="row g-2 mt-3 pt-3 border-top text-center">
                                        <div class="col-6">
                                            <div class="fw-bold fs-4">{{ $yetToOffer }}</div><small class="text-muted">Yet
                                                to
                                                offer</small>
                                        </div>
                                        <div class="col-6">
                                            <div class="fw-bold fs-4 text-success">{{ $offered }}</div><small
                                                class="text-muted">Offered</small>
                                        </div>
                                    </div> --}}
                                </div>
                            </div>
                        </div>
                    @endcan
                    {{-- @can('read', \App\Models\Candidate::class)
                        <div class="col-xl-8">
                            <div class="card panel-card">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div>
                                            <h5 class="section-title mb-1">Upcoming Interviews</h5><span
                                                class="text-muted small">Date-wise interview calendar</span>
                                        </div><a href="{{ route('admin.interview-schedules.index') }}"
                                            class="btn btn-sm btn-dark">View calendar</a>
                                    </div>
                                    @forelse($upcomingInterviews as $interview)
                                        <div class="interview-item d-flex gap-3 align-items-center">
                                            <div class="date-box"><strong
                                                    class="d-block fs-5 lh-1">{{ $interview->schedule_date->format('d') }}</strong><small>{{ $interview->schedule_date->format('M') }}</small>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="fw-semibold text-dark">
                                                    {{ $interview->candidate?->candidate_name ?? 'Candidate' }}</div><small
                                                    class="text-muted">{{ $interview->client?->client ?? 'No client' }} &middot;
                                                    {{ $interview->interviewLevel?->level ?? 'Interview' }}</small>
                                            </div>
                                            <div class="text-end"><span
                                                    class="badge bg-light text-dark">{{ $interview->schedule_date->format('h:i A') }}</span>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-center text-muted py-5"><i
                                                class="ri-calendar-check-line fs-1 d-block mb-2"></i>No upcoming interviews
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    @endcan --}}
                </div>
                @if ($isSuperAdminDashboard)
                    @can('read', \App\Models\ClientRequirement::class)
                        <div class="card panel-card target-panel mb-4">
                            <div class="card-body p-4">
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                                    <div>
                                        <h5 class="section-title mb-1">Monthly Revenue</h5>
                                        <p class="text-muted small mb-0">Client Requirement Revenue Amount totals for the last seven months</p>
                                    </div>
                                    <span class="badge bg-success-subtle text-success">Admin only</span>
                                </div>
                                <div id="monthlyRevenueBarChart" style="min-height: 300px;"></div>
                            </div>
                        </div>
                    @endcan
                @endif
                <div class="row g-4">
                    @can('read', \App\Models\Candidate::class)
                        <div class="col-xl-8">
                            <div class="card panel-card target-panel">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div>
                                            <h5 class="section-title mb-1">Applicant Momentum</h5><span
                                                class="text-muted small">New applicants over the last six months</span>
                                        </div><span class="badge bg-danger-subtle text-danger">Live</span>
                                    </div>
                                    <div id="applicantChart" style="min-height:310px"></div>
                                </div>
                            </div>
                        </div>
                        {{-- <div class="col-xl-4">
                            <div class="card panel-card">
                                <div class="card-body p-4">
                                    <h5 class="section-title">Revenue Overview</h5>
                                    <p class="text-muted small">Revenue recorded on visible requirements</p>
                                    <div class="py-4">
                                        <div class="metric-label mb-2">Total Pipeline Revenue</div>
                                        <div class="display-6 fw-bold text-dark">&#8377;{{ number_format($revenue, 2) }}</div>
                                    </div>
                                    <div class="rounded-4 bg-danger-subtle text-danger p-3 small"><i
                                            class="ri-information-line me-1"></i> Values follow your role-based requirement
                                        access.</div>
                                </div>
                            </div>
                        </div> --}}
                    @endcan
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var target = document.querySelector('#applicantChart');
            if (!target || typeof ApexCharts === 'undefined') return;
            new ApexCharts(target, {
                chart: {
                    type: 'area',
                    height: 310,
                    toolbar: {
                        show: false
                    },
                    zoom: {
                        enabled: false
                    }
                },
                series: [{
                    name: 'Applicants',
                    data: @json($chartApplicants)
                }],
                xaxis: {
                    categories: @json($chartMonths),
                    axisBorder: {
                        show: false
                    },
                    axisTicks: {
                        show: false
                    }
                },
                yaxis: {
                    min: 0,
                    forceNiceScale: true,
                    labels: {
                        formatter: function(v) {
                            return Math.round(v);
                        }
                    }
                },
                colors: ['#b91c1c'],
                stroke: {
                    curve: 'smooth',
                    width: 3
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: .35,
                        opacityTo: .03,
                        stops: [0, 95, 100]
                    }
                },
                dataLabels: {
                    enabled: false
                },
                grid: {
                    borderColor: '#eef2f7',
                    strokeDashArray: 4
                },
                tooltip: {
                    y: {
                        formatter: function(v) {
                            return v + ' applicant' + (v === 1 ? '' : 's');
                        }
                    }
                }
            }).render();
        });

        document.addEventListener('DOMContentLoaded', function() {
            var joiningChartTarget = document.querySelector('#pipelineMonthlyJoiningBarChart');
            var onboardingChartTarget = document.querySelector('#pipelineMonthlyOnboardingBarChart');
            if (typeof ApexCharts === 'undefined') return;

            function renderMonthlyOutcomeChart(target, positiveName, positiveData, negativeName, negativeData) {
                if (!target) return;

                new ApexCharts(target, {
                    chart: {
                        type: 'bar',
                        height: 250,
                        toolbar: { show: false },
                        sparkline: { enabled: false },
                        stacked: true
                    },
                    series: [
                        { name: positiveName, data: positiveData },
                        { name: negativeName, data: negativeData }
                    ],
                    xaxis: {
                        categories: @json($joiningChartMonths),
                        labels: {
                            rotate: -35,
                            trim: true,
                            style: { fontSize: '10px' }
                        },
                        axisBorder: { show: false },
                        axisTicks: { show: false }
                    },
                    yaxis: {
                        min: 0,
                        forceNiceScale: true,
                        labels: {
                            formatter: function(value) {
                                return Number.isInteger(value) ? value : '';
                            }
                        }
                    },
                    colors: ['#22c55e', '#ef4444'],
                    plotOptions: {
                        bar: {
                            borderRadius: 4,
                            columnWidth: '52%',
                            dataLabels: { position: 'top' }
                        }
                    },
                    dataLabels: {
                        enabled: true,
                        offsetY: -16,
                        formatter: function(value) { return value > 0 ? value : ''; },
                        style: {
                            fontSize: '10px',
                            colors: ['#334155']
                        }
                    },
                    grid: {
                        borderColor: '#eef2f7',
                        strokeDashArray: 3,
                        padding: { top: 15, right: 0, bottom: 0, left: 0 }
                    },
                    tooltip: {
                        y: {
                            formatter: function(value) {
                                return value + ' candidate' + (value === 1 ? '' : 's');
                            }
                        }
                    },
                    legend: {
                        position: 'top',
                        horizontalAlign: 'right'
                    },
                    noData: { text: 'No monthly data' }
                }).render();
            }

            renderMonthlyOutcomeChart(
                joiningChartTarget,
                'Offer Accepted',
                @json($offerAcceptedChartTotals),
                'Offer Declined',
                @json($offerDeclinedChartTotals)
            );
            renderMonthlyOutcomeChart(
                onboardingChartTarget,
                'Onboarded with Client',
                @json($onboardedChartTotals),
                'Joiner Declined',
                @json($joinerDeclinedChartTotals)
            );
        });

        document.addEventListener('DOMContentLoaded', function() {
            var revenueTarget = document.querySelector('#monthlyRevenueBarChart');
            if (!revenueTarget || typeof ApexCharts === 'undefined') return;

            new ApexCharts(revenueTarget, {
                chart: {
                    type: 'bar',
                    height: 300,
                    toolbar: { show: false }
                },
                series: [{
                    name: 'Revenue Amount',
                    data: @json($revenueChartTotals)
                }],
                xaxis: {
                    categories: @json($revenueChartMonths),
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                yaxis: {
                    min: 0,
                    labels: {
                        formatter: function(value) {
                            return '₹' + Number(value).toLocaleString('en-IN', { maximumFractionDigits: 0 });
                        }
                    }
                },
                colors: ['#2563eb'],
                plotOptions: {
                    bar: {
                        borderRadius: 5,
                        columnWidth: '48%',
                        dataLabels: { position: 'top' }
                    }
                },
                dataLabels: {
                    enabled: true,
                    offsetY: -18,
                    formatter: function(value) {
                        return value > 0 ? '₹' + Number(value).toLocaleString('en-IN', { maximumFractionDigits: 0 }) : '';
                    },
                    style: {
                        fontSize: '10px',
                        colors: ['#334155']
                    }
                },
                grid: {
                    borderColor: '#eef2f7',
                    strokeDashArray: 3,
                    padding: { top: 20 }
                },
                tooltip: {
                    y: {
                        formatter: function(value) {
                            return '₹' + Number(value).toLocaleString('en-IN', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        }
                    }
                },
                noData: { text: 'No revenue data' }
            }).render();
        });

        document.addEventListener('DOMContentLoaded', function() {
            var completionTarget = document.querySelector('#individualCompletionChart');
            var radialTarget = document.querySelector('#individualRadialChart');
            if ((!completionTarget && !radialTarget) || typeof ApexCharts === 'undefined') return;

            var monthlyProfiles = @json($chartApplicants);
            var currentMonthProfiles = Number(monthlyProfiles[monthlyProfiles.length - 1] || 0);
            var profileTarget = 100;
            var completionPercent = Math.min(100, Math.round((currentMonthProfiles / profileTarget) * 100));

            if (completionTarget) {
                new ApexCharts(completionTarget, {
                    chart: {
                        type: 'bar',
                        height: 310,
                        toolbar: {
                            show: false
                        }
                    },
                    series: [{
                        name: 'Completed profiles',
                        data: monthlyProfiles
                    }],
                    xaxis: {
                        categories: @json($chartMonths),
                        axisBorder: {
                            show: false
                        },
                        axisTicks: {
                            show: false
                        }
                    },
                    yaxis: {
                        min: 0,
                        forceNiceScale: true,
                        labels: {
                            formatter: function(value) {
                                return Math.round(value);
                            }
                        }
                    },
                    colors: ['#b91c1c'],
                    plotOptions: {
                        bar: {
                            borderRadius: 7,
                            columnWidth: '48%'
                        }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    grid: {
                        borderColor: '#eef2f7',
                        strokeDashArray: 4
                    },
                    annotations: {
                        yaxis: [{
                            y: profileTarget,
                            borderColor: '#f59e0b',
                            strokeDashArray: 5,
                            label: {
                                text: 'Minimum target 100',
                                style: {
                                    background: '#f59e0b',
                                    color: '#fff'
                                }
                            }
                        }]
                    },
                    tooltip: {
                        y: {
                            formatter: function(value) {
                                return value + ' candidate profile' + (value === 1 ? '' : 's');
                            }
                        }
                    }
                }).render();
            }

            if (radialTarget) {
                new ApexCharts(radialTarget, {
                    chart: {
                        type: 'radialBar',
                        height: 235,
                        sparkline: {
                            enabled: true
                        }
                    },
                    series: [completionPercent],
                    colors: ['#b91c1c'],
                    plotOptions: {
                        radialBar: {
                            hollow: {
                                size: '62%'
                            },
                            track: {
                                background: '#f1f5f9'
                            },
                            dataLabels: {
                                name: {
                                    show: true,
                                    offsetY: 22,
                                    color: '#64748b'
                                },
                                value: {
                                    offsetY: -14,
                                    fontSize: '28px',
                                    fontWeight: 800,
                                    color: '#172033',
                                    formatter: function(value) {
                                        return Math.round(value) + '%';
                                    }
                                }
                            }
                        }
                    },
                    labels: [currentMonthProfiles + ' of ' + profileTarget + ' profiles']
                }).render();
            }
        });
    </script>
@endsection
