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
            width: 48px;
            height: 48px;
            border-radius: 15px;
            display: grid;
            place-items: center;
            font-size: 1.35rem;
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

        .section-title {
            font-weight: 800;
            color: #172033;
        }

        .pipeline-row {
            display: grid;
            grid-template-columns: minmax(90px, 1fr) 4fr 45px;
            align-items: center;
            gap: 12px;
            margin-bottom: 17px;
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

        @media (max-width: 576px) {
            .dashboard-hero {
                border-radius: 16px;
            }

            .pipeline-row {
                grid-template-columns: 80px 1fr 35px;
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

                @unless ($isRecruiterDashboard)
                    <div class="card panel-card mb-4">
                        <div class="card-body p-3 p-lg-4">
                            <form method="GET" action="{{ route('admin.dashboard') }}" class="row g-3 align-items-end">
                                <div class="col-md-{{ $showClientFilter ? '5' : '9' }}">
                                    <label for="dashboard_recruiter_id" class="form-label fw-semibold">Recruiter</label>
                                    <select id="dashboard_recruiter_id" name="recruiter_id" class="form-select">
                                        <option value="">All recruiters</option>
                                        @foreach ($recruiters as $recruiter)
                                            <option value="{{ $recruiter->id }}" @selected((int) $selectedRecruiterId === $recruiter->id)>{{ $recruiter->recruiter_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @if ($showClientFilter)
                                    <div class="col-md-4">
                                        <label for="dashboard_client_id" class="form-label fw-semibold">Client</label>
                                        <select id="dashboard_client_id" name="client_id" class="form-select">
                                            <option value="">All clients</option>
                                            @foreach ($clients as $client)
                                                <option value="{{ $client->id }}" @selected((int) $selectedClientId === $client->id)>{{ $client->client }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif
                                <div class="col-md-3 d-flex gap-2">
                                    <button type="submit" class="btn btn-danger flex-grow-1"><i class="ri-filter-3-line me-1"></i>Apply</button>
                                    <a href="{{ route('admin.dashboard') }}" class="btn btn-light" title="Clear filters"><i class="ri-refresh-line"></i></a>
                                </div>
                            </form>
                        </div>
                    </div>
                @endunless

                @unless ($recruiterLinked)
                    <div class="alert alert-warning border-0 shadow-sm"><i class="ri-alert-line me-2"></i>Your login email is
                        not linked to a recruiter record, so your personal dashboard currently has no records.</div>
                @endunless

                <div class="row g-3 mb-4">
                    @can('read', \App\Models\ClientRequirement::class)
                        <div class="col-6 col-xl-3">
                            <div class="card metric-card">
                                <div class="card-body p-4 d-flex justify-content-between">
                                    <div>
                                        <div class="metric-label mb-2">Active Positions</div>
                                        <div class="metric-value">{{ number_format($activeRequirements) }}</div>
                                    </div>
                                    <div class="metric-icon bg-danger-subtle text-danger"><i class="ri-briefcase-4-line"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endcan
                    @can('read', \App\Models\Candidate::class)
                        <div class="col-6 col-xl-3">
                            <div class="card metric-card">
                                <div class="card-body p-4 d-flex justify-content-between">
                                    <div>
                                        <div class="metric-label mb-2">Applicants</div>
                                        <div class="metric-value">{{ number_format($myApplicants) }}</div>
                                    </div>
                                    <div class="metric-icon bg-primary-subtle text-primary"><i class="ri-team-line"></i></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-xl-3">
                            <div class="card metric-card">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div>
                                            <div class="metric-label">Interviews</div>
                                            <div class="metric-value">
                                                {{ number_format($scheduledInterviews->count()) }}
                                            </div>
                                        </div>

                                        <div class="metric-icon bg-warning-subtle text-warning">
                                            <i class="ri-calendar-event-line"></i>
                                        </div>
                                    </div>

                                    <div class="row g-2 text-center">
                                        <div class="col-3">
                                            <div class="border rounded p-2 bg-light">
                                                <small class="text-muted d-block">L1</small>
                                                <strong>{{ $scheduledInterviews->where('level_of_interview_id', [7,8])->count() ?? 0 }}</strong>
                                            </div>
                                        </div>

                                        <div class="col-3">
                                            <div class="border rounded p-2 bg-light">
                                                <small class="text-muted d-block">L2</small>
                                                <strong>{{ $scheduledInterviews->where('level_of_interview_id', [11,12])->count() ?? 0 }}</strong>
                                            </div>
                                        </div>

                                        <div class="col-3">
                                            <div class="border rounded p-2 bg-light">
                                                <small class="text-muted d-block">L3</small>
                                                <strong>{{ $scheduledInterviews->where('level_of_interview_id', [23,25])->count() ?? 0 }}</strong>
                                            </div>
                                        </div>

                                        <div class="col-3">
                                            <div class="border rounded p-2 bg-light">
                                                <small class="text-muted d-block">L4</small>
                                                <strong>{{ $scheduledInterviews->where('level_of_interview_id', [27,28])->count() ?? 0 }}</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-xl-3">
                            <div class="card metric-card">
                                <div class="card-body p-4 d-flex justify-content-between">
                                    <div>
                                        <div class="metric-label mb-2">Offered</div>
                                        <div class="metric-value">{{ number_format($offered) }}</div>
                                    </div>
                                    <div class="metric-icon bg-success-subtle text-success"><i class="ri-user-follow-line"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endcan
                </div>

                <div class="row g-4 mb-4">
                    @can('read', \App\Models\Candidate::class)
                        <div class="col-xl-8">
                            <div class="card panel-card">
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
                        <div class="col-xl-4">
                            <div class="card panel-card">
                                <div class="card-body p-4">
                                    <h5 class="section-title mb-1">Interview Pipeline</h5>
                                    <p class="text-muted small mb-4">Level-wise interview activity</p>
                                    @php($maxLevel = max(1, (int) $interviewLevels->max('total')))
                                    @forelse($interviewLevels as $level)
                                        <div class="pipeline-row"><span class="small fw-semibold text-truncate"
                                                title="{{ $level->level }}">{{ $level->level }}</span>
                                            <div class="pipeline-track">
                                                <div class="pipeline-fill"
                                                    style="width:{{ round(($level->total / $maxLevel) * 100) }}%"></div>
                                            </div><strong>{{ $level->total }}</strong>
                                        </div>
                                    @empty
                                        <div class="text-center text-muted py-5"><i
                                                class="ri-bar-chart-grouped-line fs-1 d-block mb-2"></i>No interview data yet
                                        </div>
                                    @endforelse
                                    <div class="row g-2 mt-3 pt-3 border-top text-center">
                                        <div class="col-6">
                                            <div class="fw-bold fs-4">{{ $yetToOffer }}</div><small class="text-muted">Yet to
                                                offer</small>
                                        </div>
                                        <div class="col-6">
                                            <div class="fw-bold fs-4 text-success">{{ $offered }}</div><small
                                                class="text-muted">Offered</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endcan
                </div>

                <div class="row g-4">
                    @can('read', \App\Models\ClientRequirement::class)
                        <div class="col-xl-4">
                            <div class="card panel-card">
                                <div class="card-body p-4">
                                    <h5 class="section-title">Revenue Overview</h5>
                                    <p class="text-muted small">Revenue recorded on visible requirements</p>
                                    <div class="py-4">
                                        <div class="metric-label mb-2">Total Pipeline Revenue</div>
                                        <div class="display-6 fw-bold text-dark">₹{{ number_format($revenue, 2) }}</div>
                                    </div>
                                    <div class="rounded-4 bg-danger-subtle text-danger p-3 small"><i
                                            class="ri-information-line me-1"></i> Values follow your role-based requirement
                                        access.</div>
                                </div>
                            </div>
                        </div>
                    @endcan
                    @can('read', \App\Models\Candidate::class)
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
                                                    class="text-muted">{{ $interview->client?->client ?? 'No client' }} ·
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
    </script>
@endsection
