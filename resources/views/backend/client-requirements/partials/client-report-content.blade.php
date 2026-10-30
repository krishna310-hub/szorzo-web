<div class="client-report-container">
    {{-- 1. Client Overview Profile Header --}}
    <div class="card border shadow-sm mb-4">
        <div class="card-body p-3">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3 border-bottom pb-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar-md bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center fs-2 fw-bold">
                        {{ strtoupper(substr($client->client ?? 'C', 0, 2)) }}
                    </div>
                    <div>
                        <h4 class="mb-1 text-primary fw-bold">{{ $client->client }}</h4>
                        <div class="d-flex flex-wrap align-items-center gap-2 text-muted fs-7">
                            @if ($client->division)
                                <span><i class="ri-building-line me-1"></i>{{ $client->division->name }}</span>
                                <span>&bull;</span>
                            @endif
                            @if ($client->location)
                                <span><i class="ri-map-pin-line me-1"></i>{{ $client->location->location }}</span>
                                <span>&bull;</span>
                            @endif
                            <span>Status: {!! $client->status ? '<span class="badge bg-success-subtle text-success">Active</span>' : '<span class="badge bg-danger-subtle text-danger">Inactive</span>' !!}</span>
                        </div>
                    </div>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    @if ($client->upload_agreement)
                        <a href="{{ asset($client->upload_agreement) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="ri-file-text-line me-1"></i>Agreement
                        </a>
                    @endif
                    @if (isset($metrics['total_revenue']))
                        <div class="text-end ps-3 border-start">
                            <div class="text-muted fs-7 text-uppercase fw-semibold">Total Revenue</div>
                            <div class="fs-4 fw-bold text-success">₹{{ number_format((float) $metrics['total_revenue'], 2) }}</div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Client Metadata Grid --}}
            <div class="row g-3 text-sm">
                <div class="col-6 col-md-3">
                    <div class="text-muted fs-7">Point of Contact (POC)</div>
                    <div class="fw-semibold text-dark">{{ $client->poc_name ?: '-' }}</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-muted fs-7">Contact / Phone</div>
                    <div class="fw-semibold text-dark">{{ $client->contact_number ?: ($client->mobile_number ?: '-') }}</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-muted fs-7">Email</div>
                    <div class="fw-semibold text-dark">{{ $client->email ?: '-' }}</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-muted fs-7">Billing % & Cycle</div>
                    <div class="fw-semibold text-dark">
                        {{ $client->billing ? $client->billing->value.'%' : '-' }}
                        @if ($client->payment_cycle)
                            <span class="text-muted fw-normal">({{ $client->payment_cycle }} Days)</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. Brief Report - KPI Stat Cards --}}
    <div class="row g-3 mb-4">
        {{-- Card 1: Total Requirements --}}
        <div class="col-12 col-sm-6 col-xl">
            <div class="card border border-primary border-opacity-25 shadow-none h-100 mb-0">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted fs-7 text-uppercase fw-semibold">Requirements</span>
                        <div class="avatar-xs bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center">
                            <i class="ri-file-list-3-line fs-5"></i>
                        </div>
                    </div>
                    <h3 class="mb-1 fw-bold text-primary">{{ $metrics['total_requirements'] }}</h3>
                    <div class="d-flex align-items-center gap-1 fs-7">
                        <span class="badge bg-success-subtle text-success">{{ $metrics['active_requirements'] }} Active</span>
                        <span class="badge bg-secondary-subtle text-secondary">{{ $metrics['inactive_requirements'] }} Closed</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 2: Total Positions --}}
        <div class="col-12 col-sm-6 col-xl">
            <div class="card border border-info border-opacity-25 shadow-none h-100 mb-0">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted fs-7 text-uppercase fw-semibold">Positions</span>
                        <div class="avatar-xs bg-info-subtle text-info rounded-circle d-flex align-items-center justify-content-center">
                            <i class="ri-team-line fs-5"></i>
                        </div>
                    </div>
                    <h3 class="mb-1 fw-bold text-info">{{ $metrics['total_positions'] }}</h3>
                    <div class="d-flex align-items-center gap-1 fs-7">
                        <span class="badge bg-success-subtle text-success">{{ $metrics['onboarded_positions'] }} Filled</span>
                        <span class="badge bg-warning-subtle text-warning">{{ $metrics['remaining_positions'] }} Remaining</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 3: Total Onboarded --}}
        <div class="col-12 col-sm-6 col-xl">
            <div class="card border border-success border-opacity-25 shadow-none h-100 mb-0">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted fs-7 text-uppercase fw-semibold">Onboarded</span>
                        <div class="avatar-xs bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center">
                            <i class="ri-checkbox-circle-line fs-5"></i>
                        </div>
                    </div>
                    <h3 class="mb-1 fw-bold text-success">{{ $metrics['total_onboarded'] }}</h3>
                    <div class="fs-7 text-muted">
                        <span class="fw-semibold text-success">{{ $metrics['fill_rate'] }}%</span> Fill Rate
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 4: Total Rejected --}}
        <div class="col-12 col-sm-6 col-xl">
            <div class="card border border-danger border-opacity-25 shadow-none h-100 mb-0">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted fs-7 text-uppercase fw-semibold">Rejected Details</span>
                        <div class="avatar-xs bg-danger-subtle text-danger rounded-circle d-flex align-items-center justify-content-center">
                            <i class="ri-close-circle-line fs-5"></i>
                        </div>
                    </div>
                    <h3 class="mb-1 fw-bold text-danger">{{ $metrics['total_rejected'] }}</h3>
                    <div class="fs-7 text-muted">
                        Total Candidates Dropped
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 5: Revenue Generated --}}
        <div class="col-12 col-sm-6 col-xl">
            <div class="card border border-success bg-success-subtle bg-opacity-10 shadow-none h-100 mb-0">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted fs-7 text-uppercase fw-semibold">Revenue Generated</span>
                        <div class="avatar-xs bg-success text-white rounded-circle d-flex align-items-center justify-content-center">
                            <i class="ri-money-rupee-circle-line fs-5"></i>
                        </div>
                    </div>
                    <h3 class="mb-1 fw-bold text-success">₹{{ number_format((float) $metrics['total_revenue'], 2) }}</h3>
                    <div class="fs-7 text-muted text-truncate" title="Invoiced: ₹{{ number_format((float) $metrics['invoiced_revenue'], 2) }} | Placement: ₹{{ number_format((float) $metrics['placement_revenue'], 2) }}">
                        Inv: ₹{{ number_format((float) $metrics['invoiced_revenue'], 0) }} | Placed: ₹{{ number_format((float) $metrics['placement_revenue'], 0) }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. Rejection Stage Breakdown Summary Pills --}}
    <div class="card border bg-light shadow-none mb-4">
        <div class="card-body p-3">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                <h6 class="mb-0 fw-bold text-dark d-flex align-items-center">
                    <i class="ri-shield-cross-line text-danger me-2"></i> Rejection & Dropout Stages Summary
                </h6>
                <span class="text-muted fs-7">Total {{ $metrics['total_rejected'] }} rejected / dropped candidates</span>
            </div>
            <div class="d-flex flex-wrap gap-2 pt-1">
                <div class="badge bg-danger-subtle text-danger p-2 fs-7 d-flex align-items-center gap-1 border border-danger border-opacity-25">
                    <span>Screening Rejects:</span>
                    <strong class="fs-6">{{ $rejectionStats['screening'] }}</strong>
                </div>
                <div class="badge bg-danger-subtle text-danger p-2 fs-7 d-flex align-items-center gap-1 border border-danger border-opacity-25">
                    <span>Technical (L1-L4) Rejects:</span>
                    <strong class="fs-6">{{ $rejectionStats['technical'] }}</strong>
                </div>
                <div class="badge bg-warning-subtle text-warning p-2 fs-7 d-flex align-items-center gap-1 border border-warning border-opacity-25">
                    <span>HR Round Rejects:</span>
                    <strong class="fs-6">{{ $rejectionStats['hr'] }}</strong>
                </div>
                <div class="badge bg-warning-subtle text-warning p-2 fs-7 d-flex align-items-center gap-1 border border-warning border-opacity-25">
                    <span>Offer / Joiner Declined:</span>
                    <strong class="fs-6">{{ $rejectionStats['declined'] }}</strong>
                </div>
                <div class="badge bg-secondary-subtle text-secondary p-2 fs-7 d-flex align-items-center gap-1 border border-secondary border-opacity-25">
                    <span>Candidate Dropped / Other:</span>
                    <strong class="fs-6">{{ $rejectionStats['dropped'] }}</strong>
                </div>
            </div>
        </div>
    </div>

    {{-- 4. Detailed Report Tabs --}}
    <div class="card border shadow-sm">
        <div class="card-header bg-white border-bottom-0 pb-0">
            <ul class="nav nav-tabs nav-tabs-custom card-header-tabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active fw-semibold" data-bs-toggle="tab" data-bs-target="#client-tab-requirements" type="button" role="tab">
                        <i class="ri-file-list-3-line me-1"></i> Available Requirements
                        <span class="badge bg-primary-subtle text-primary ms-1">{{ count($requirements) }}</span>
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#client-tab-onboarded" type="button" role="tab">
                        <i class="ri-user-follow-line me-1"></i> Onboarded Candidates
                        <span class="badge bg-success-subtle text-success ms-1">{{ count($onboardedCandidates) }}</span>
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#client-tab-rejected" type="button" role="tab">
                        <i class="ri-user-unfollow-line me-1"></i> Rejected Details
                        <span class="badge bg-danger-subtle text-danger ms-1">{{ count($rejectedCandidates) }}</span>
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#client-tab-revenues" type="button" role="tab">
                        <i class="ri-money-dollar-circle-line me-1"></i> Revenue & Invoices
                        <span class="badge bg-info-subtle text-info ms-1">{{ count($revenues) }}</span>
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#client-tab-pipeline" type="button" role="tab">
                        <i class="ri-git-merge-line me-1"></i> Candidate Pipeline
                        <span class="badge bg-secondary-subtle text-secondary ms-1">{{ count($allCandidates) }}</span>
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body p-3">
            <div class="tab-content">
                {{-- Tab 1: Available Requirements --}}
                <div class="tab-pane fade show active" id="client-tab-requirements" role="tabpanel">
                    <div class="table-responsive" style="max-height: 520px; overflow-y: auto;">
                        <table class="table table-bordered table-hover align-middle mb-0 nowrap">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>S.No</th>
                                    <th>Job Role</th>
                                    <th>Position Level</th>
                                    <th>Mode</th>
                                    <th>Positions (Filled / Total)</th>
                                    <th>CVs (Uploaded / Req)</th>
                                    <th>Open Date</th>
                                    <th>Closure Target Date</th>
                                    <th>CTC</th>
                                    <th>Revenue Amount</th>
                                    <th>Project Owner</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>JD</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($requirements as $idx => $req)
                                    <tr>
                                        <td>{{ $idx + 1 }}</td>
                                        <td class="fw-semibold text-primary">{{ $req->jobRole?->job_role ?? '-' }}</td>
                                        <td>{{ $req->position_level ?: '-' }}</td>
                                        <td>{{ $req->resolved_modes }}</td>
                                        <td>
                                            @php
                                                $onboarded = (int) ($req->onboarded_candidates_count ?? 0);
                                                $positions = (int) ($req->number_of_position ?? 0);
                                                $badgeClass = ($positions > 0 && $onboarded >= $positions) ? 'bg-success text-white' : 'bg-info-subtle text-info';
                                            @endphp
                                            <span class="badge {{ $badgeClass }} fw-semibold fs-7">{{ $onboarded }} / {{ $positions }}</span>
                                        </td>
                                        <td>
                                            <span class="fw-semibold">{{ (int) $req->cv_uploaded }}</span> / {{ (int) $req->cv_required }}
                                        </td>
                                        <td>{{ $req->requirement_open_date?->format('d-m-Y') ?? '-' }}</td>
                                        <td>{{ $req->closure_target_date?->format('d-m-Y') ?? '-' }}</td>
                                        <td>{{ $req->ctc !== null ? '₹'.number_format((float) $req->ctc, 2) : '-' }}</td>
                                        <td>{{ $req->revenue_amount !== null ? '₹'.number_format((float) $req->revenue_amount, 2) : '-' }}</td>
                                        <td>{{ $req->resolved_project_owners }}</td>
                                        <td>
                                            {!! $req->is_priority ? '<span class="badge bg-warning-subtle text-warning">Priority</span>' : '-' !!}
                                        </td>
                                        <td>
                                            {!! $req->status ? '<span class="badge bg-success-subtle text-success">Active</span>' : '<span class="badge bg-danger-subtle text-danger">Inactive</span>' !!}
                                        </td>
                                        <td>
                                            @if ($req->jobDescription?->job_description)
                                                <button type="button" class="btn btn-sm btn-link text-primary p-0 view-job-description" data-id="{{ $req->id }}" data-title="{{ e(($req->jobRole?->job_role ?? 'JD').' - '.($client->client ?? '')) }}" title="View Job Description">
                                                    <i class="ri-eye-line fs-5"></i>
                                                </button>
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="14" class="text-center py-4 text-muted">
                                            <i class="ri-inbox-line fs-2 d-block mb-1"></i> No requirements recorded for this client.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Tab 2: Onboarded Candidates --}}
                <div class="tab-pane fade" id="client-tab-onboarded" role="tabpanel">
                    <div class="table-responsive" style="max-height: 520px; overflow-y: auto;">
                        <table class="table table-bordered table-hover align-middle mb-0 nowrap">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>S.No</th>
                                    <th>Candidate Name</th>
                                    <th>Job Role / Requirement</th>
                                    <th>Recruiter</th>
                                    <th>Onboarding Date</th>
                                    <th>Onboarding CTC</th>
                                    <th>Billing %</th>
                                    <th>Revenue Generated</th>
                                    <th>Contact Info</th>
                                    <th>Mode</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($onboardedCandidates as $idx => $cand)
                                    <tr>
                                        <td>{{ $idx + 1 }}</td>
                                        <td>
                                            <span class="fw-semibold text-dark">{{ $cand->candidate_name }}</span>
                                            @if ($cand->qualification)
                                                <br><small class="text-muted">{{ $cand->qualification }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $cand->jobRole?->job_role ?: ($cand->clientRequirement?->jobRole?->job_role ?: '-') }}</td>
                                        <td>{{ $cand->recruiter?->recruiter_name ?: '-' }}</td>
                                        <td>
                                            @if ($cand->onboarding_date)
                                                <span class="badge bg-success-subtle text-success fs-7">{{ $cand->onboarding_date->format('d-m-Y') }}</span>
                                            @else
                                                <span class="badge bg-info-subtle text-info fs-7">Onboarded</span>
                                            @endif
                                        </td>
                                        <td>{{ $cand->onboarding_ctc ? '₹'.number_format((float) $cand->onboarding_ctc, 2) : ($cand->expected_ctc ? '₹'.number_format((float) $cand->expected_ctc, 2) : '-') }}</td>
                                        <td>{{ $cand->billing_percentage ? $cand->billing_percentage.'%' : '-' }}</td>
                                        <td class="fw-bold text-success">
                                            ₹{{ number_format((float) ($cand->calculated_revenue ?? 0), 2) }}
                                        </td>
                                        <td>
                                            <small>
                                                {{ $cand->mobile_no ?: '-' }}<br>
                                                {{ $cand->email ?: '' }}
                                            </small>
                                        </td>
                                        <td>{{ $cand->mode?->mode ?: '-' }}</td>
                                        <td>
                                            <span class="badge bg-success text-white">Onboarded</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="11" class="text-center py-4 text-muted">
                                            <i class="ri-user-unfollow-line fs-2 d-block mb-1"></i> No candidates onboarded yet for this client.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Tab 3: Rejected Details --}}
                <div class="tab-pane fade" id="client-tab-rejected" role="tabpanel">
                    <div class="table-responsive" style="max-height: 520px; overflow-y: auto;">
                        <table class="table table-bordered table-hover align-middle mb-0 nowrap">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>S.No</th>
                                    <th>Candidate Name</th>
                                    <th>Job Role / Requirement</th>
                                    <th>Recruiter</th>
                                    <th>Rejection Stage</th>
                                    <th>Details / Reason</th>
                                    <th>Current CTC</th>
                                    <th>Expected CTC</th>
                                    <th>Contact</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($rejectedCandidates as $idx => $cand)
                                    <tr>
                                        <td>{{ $idx + 1 }}</td>
                                        <td>
                                            <span class="fw-semibold text-dark">{{ $cand->candidate_name }}</span>
                                            @if ($cand->qualification)
                                                <br><small class="text-muted">{{ $cand->qualification }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $cand->jobRole?->job_role ?: ($cand->clientRequirement?->jobRole?->job_role ?: '-') }}</td>
                                        <td>{{ $cand->recruiter?->recruiter_name ?: '-' }}</td>
                                        <td>
                                            <span class="badge {{ $cand->status_badge }} fw-semibold fs-7">{{ $cand->status_stage }}</span>
                                            <br><small class="text-muted">{{ $cand->status_category }}</small>
                                        </td>
                                        <td>
                                            <span class="text-danger fw-semibold">{{ $cand->status_reason }}</span>
                                            @php
                                                $schedRemarks = $cand->interviewSchedules?->where('status', 'rejected')->pluck('remarks')->filter()->first();
                                            @endphp
                                            @if ($schedRemarks)
                                                <br><small class="text-muted">Remarks: {{ $schedRemarks }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $cand->current_ctc ? '₹'.number_format((float) $cand->current_ctc, 2) : '-' }}</td>
                                        <td>{{ $cand->expected_ctc ? '₹'.number_format((float) $cand->expected_ctc, 2) : '-' }}</td>
                                        <td>
                                            <small>{{ $cand->mobile_no ?: '-' }}</small>
                                        </td>
                                        <td>{{ $cand->updated_at?->format('d-m-Y') ?? ($cand->created_at?->format('d-m-Y') ?? '-') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center py-4 text-muted">
                                            <i class="ri-checkbox-circle-line fs-2 d-block mb-1 text-success"></i> No rejected candidates found for this client.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Tab 4: Revenue & Invoices --}}
                <div class="tab-pane fade" id="client-tab-revenues" role="tabpanel">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3 p-3 bg-light rounded border">
                        <div>
                            <span class="text-muted fs-7 text-uppercase fw-semibold">Invoiced Revenue</span>
                            <div class="fs-4 fw-bold text-primary">₹{{ number_format((float) $metrics['invoiced_revenue'], 2) }}</div>
                        </div>
                        <div>
                            <span class="text-muted fs-7 text-uppercase fw-semibold">Placement Revenue</span>
                            <div class="fs-4 fw-bold text-info">₹{{ number_format((float) $metrics['placement_revenue'], 2) }}</div>
                        </div>
                        <div>
                            <span class="text-muted fs-7 text-uppercase fw-semibold">Total Revenue Generated</span>
                            <div class="fs-4 fw-bold text-success">₹{{ number_format((float) $metrics['total_revenue'], 2) }}</div>
                        </div>
                    </div>

                    <div class="table-responsive" style="max-height: 480px; overflow-y: auto;">
                        <table class="table table-bordered table-hover align-middle mb-0 nowrap">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>S.No</th>
                                    <th>Invoice Number</th>
                                    <th>Invoice Date</th>
                                    <th>Candidate Name</th>
                                    <th>Onboarding CTC</th>
                                    <th>Billing %</th>
                                    <th>Service Amount</th>
                                    <th>GST (18%)</th>
                                    <th>Total Amount</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($revenues as $idx => $rev)
                                    <tr>
                                        <td>{{ $idx + 1 }}</td>
                                        <td class="fw-semibold text-primary">{{ $rev->invoice_number ?: '-' }}</td>
                                        <td>{{ $rev->invoice_date?->format('d-m-Y') ?? '-' }}</td>
                                        <td>
                                            {{ $rev->candidates->isNotEmpty() ? $rev->candidates->pluck('candidate_name')->join(', ') : ($rev->candidate?->candidate_name ?? '-') }}
                                        </td>
                                        <td>{{ $rev->onboarding_ctc ? '₹'.number_format((float) $rev->onboarding_ctc, 2) : '-' }}</td>
                                        <td>{{ $rev->billing_percentage ? $rev->billing_percentage.'%' : '-' }}</td>
                                        <td class="fw-bold text-dark">₹{{ number_format((float) $rev->service_amount, 2) }}</td>
                                        <td>₹{{ number_format((float) $rev->gst_amount, 2) }}</td>
                                        <td class="fw-bold text-success">₹{{ number_format((float) $rev->total_amount, 2) }}</td>
                                        <td><small class="text-muted">{{ $rev->notes ?: '-' }}</small></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center py-4 text-muted">
                                            <i class="ri-bill-line fs-2 d-block mb-1"></i> No revenue / invoice records directly generated yet for this client.
                                            @if ($onboardedCandidates->isNotEmpty())
                                                <div class="mt-2 text-info fs-7">
                                                    Candidate placements worth ₹{{ number_format((float) $metrics['placement_revenue'], 2) }} are calculated from onboarded candidates above.
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Tab 5: Candidate Pipeline --}}
                <div class="tab-pane fade" id="client-tab-pipeline" role="tabpanel">
                    <div class="table-responsive" style="max-height: 520px; overflow-y: auto;">
                        <table class="table table-bordered table-hover align-middle mb-0 nowrap">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>S.No</th>
                                    <th>Candidate Name</th>
                                    <th>Job Role / Requirement</th>
                                    <th>Recruiter</th>
                                    <th>Current Pipeline Stage</th>
                                    <th>Status Outcome</th>
                                    <th>Expected CTC</th>
                                    <th>Contact</th>
                                    <th>Sourced Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($allCandidates as $idx => $cand)
                                    <tr>
                                        <td>{{ $idx + 1 }}</td>
                                        <td class="fw-semibold text-dark">{{ $cand->candidate_name }}</td>
                                        <td>{{ $cand->jobRole?->job_role ?: ($cand->clientRequirement?->jobRole?->job_role ?: '-') }}</td>
                                        <td>{{ $cand->recruiter?->recruiter_name ?: '-' }}</td>
                                        <td>
                                            <span class="badge {{ $cand->status_badge }} fs-7">{{ $cand->interviewLevel?->level ?: 'Sourced' }}</span>
                                        </td>
                                        <td>
                                            @if ($cand->status_type === 'onboarded')
                                                <span class="badge bg-success text-white"><i class="ri-checkbox-circle-line me-1"></i>Onboarded</span>
                                            @elseif ($cand->status_type === 'rejected')
                                                <span class="badge bg-danger text-white"><i class="ri-close-circle-line me-1"></i>Rejected</span>
                                            @else
                                                <span class="badge bg-info text-white"><i class="ri-loader-4-line me-1"></i>In Pipeline</span>
                                            @endif
                                        </td>
                                        <td>{{ $cand->expected_ctc ? '₹'.number_format((float) $cand->expected_ctc, 2) : '-' }}</td>
                                        <td><small>{{ $cand->mobile_no ?: '-' }}</small></td>
                                        <td>{{ $cand->created_at?->format('d-m-Y') ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-4 text-muted">
                                            <i class="ri-team-line fs-2 d-block mb-1"></i> No candidates found for this client.
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
</div>

