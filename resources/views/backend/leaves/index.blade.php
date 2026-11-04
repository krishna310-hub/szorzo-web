@extends('backend.layouts.master')
@section('title', $canApprove ? 'Leave Requests Management' : 'My Leave Requests')

@section('content')
@include('backend.attendance._styles')

<div class="main-content atl-shell">
    <div class="page-content">
        <div class="container-fluid">

            <!-- Breadcrumbs / Top Header -->
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                <div>
                    <h3 class="fw-bold text-dark mb-1">
                        <i class="ri-calendar-event-line text-primary me-2"></i>
                        {{ $canApprove ? 'Leave Requests Management' : 'My Leave Requests' }}
                    </h3>
                    <p class="text-muted mb-0 small">
                        {{ $canApprove ? 'Review, approve, or reject internal employee leave requests.' : 'Track your leave requests and review admin approval status.' }}
                    </p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    @if(auth()->user()->isSuperAdmin() || auth()->user()->isInternalEmployee() || auth()->user()->can('create', \App\Models\LeaveRequest::class))
                        <a class="btn btn-primary rounded-pill px-4 shadow-sm fw-semibold" href="{{ route('admin.leaves.create') }}">
                            <i class="ri-add-line me-1"></i> Apply for Leave
                        </a>
                    @endif
                    @if($canApprove)
                        <a class="btn btn-outline-secondary rounded-pill px-3 shadow-sm fw-semibold" href="{{ route('admin.attendance.dashboard') }}">
                            <i class="ri-dashboard-line me-1"></i> Attendance Dashboard
                        </a>
                    @endif
                </div>
            </div>

            <!-- Stats Overview Cards -->
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm rounded-4 h-100 bg-white p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted text-uppercase fw-semibold small">Total Requests</span>
                            <span class="badge bg-primary-subtle text-primary rounded-circle p-2">
                                <i class="ri-folder-user-line fs-18"></i>
                            </span>
                        </div>
                        <h3 class="fw-bold text-dark mb-0">{{ $stats['total'] ?? 0 }}</h3>
                        <span class="text-muted small mt-1 d-block">Overall submissions</span>
                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm rounded-4 h-100 bg-white p-3 border-start border-warning border-4">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted text-uppercase fw-semibold small">Pending</span>
                            <span class="badge bg-warning-subtle text-warning rounded-circle p-2">
                                <i class="ri-time-line fs-18"></i>
                            </span>
                        </div>
                        <h3 class="fw-bold text-warning mb-0">{{ $stats['pending'] ?? 0 }}</h3>
                        <span class="text-muted small mt-1 d-block">Awaiting admin review</span>
                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm rounded-4 h-100 bg-white p-3 border-start border-success border-4">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted text-uppercase fw-semibold small">Approved</span>
                            <span class="badge bg-success-subtle text-success rounded-circle p-2">
                                <i class="ri-checkbox-circle-line fs-18"></i>
                            </span>
                        </div>
                        <h3 class="fw-bold text-success mb-0">{{ $stats['approved'] ?? 0 }}</h3>
                        <span class="text-muted small mt-1 d-block">Reflected on attendance</span>
                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm rounded-4 h-100 bg-white p-3 border-start border-danger border-4">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted text-uppercase fw-semibold small">Rejected</span>
                            <span class="badge bg-danger-subtle text-danger rounded-circle p-2">
                                <i class="ri-close-circle-line fs-18"></i>
                            </span>
                        </div>
                        <h3 class="fw-bold text-danger mb-0">{{ $stats['rejected'] ?? 0 }}</h3>
                        <span class="text-muted small mt-1 d-block">Declined with reason</span>
                    </div>
                </div>
            </div>

            <!-- Filter Card -->
            <div class="card atl-card mb-4 border-0 shadow-sm">
                <div class="card-body p-3">
                    <form method="GET" action="{{ route('admin.leaves.index') }}" class="row g-2 align-items-center">
                        <div class="col-12 col-md-4">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 text-muted"><i class="ri-search-line"></i></span>
                                <input type="text" name="search" class="form-control border-start-0" placeholder="Search by reason, type, or employee..." value="{{ $filters['search'] ?? '' }}">
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <select name="status" class="form-select">
                                <option value="">All Statuses</option>
                                @foreach(\App\Models\LeaveRequest::STATUSES as $s)
                                    <option value="{{ $s }}" @selected(($filters['status'] ?? '') === $s)>{{ ucfirst($s) }} Requests</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-3 d-flex gap-2">
                            <button type="submit" class="btn btn-dark w-100 rounded-pill">
                                <i class="ri-filter-3-line me-1"></i> Filter
                            </button>
                            @if(!empty($filters['status']) || !empty($filters['search']))
                                <a href="{{ route('admin.leaves.index') }}" class="btn btn-outline-secondary rounded-pill" title="Reset Filters">
                                    <i class="ri-refresh-line"></i>
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <!-- Leaves Table Card -->
            <div class="card atl-card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table attendance-table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3" style="width: 50px;">#</th>
                                    @if($canApprove)
                                        <th>Internal Employee</th>
                                    @endif
                                    <th>Leave Type</th>
                                    <th>Dates & Duration</th>
                                    <th style="min-width: 220px;">Reason</th>
                                    <th>Attachment</th>
                                    <th>Status & Review</th>
                                    <th class="text-end pe-3" style="min-width: 140px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($leaves as $leave)
                                    @php
                                        $fromDate = \Carbon\Carbon::parse($leave->from_date);
                                        $toDate = \Carbon\Carbon::parse($leave->to_date);
                                        $typeBadgeClass = match($leave->leave_type) {
                                            'Sick Leave' => 'bg-info-subtle text-info border border-info-subtle',
                                            'Casual Leave' => 'bg-primary-subtle text-primary border border-primary-subtle',
                                            'Earned Leave' => 'bg-success-subtle text-success border border-success-subtle',
                                            'Unpaid Leave' => 'bg-warning-subtle text-warning border border-warning-subtle',
                                            default => 'bg-secondary-subtle text-secondary border border-secondary-subtle'
                                        };
                                        $statusBadgeClass = match($leave->status) {
                                            'approved' => 'bg-success text-white',
                                            'rejected' => 'bg-danger text-white',
                                            default => 'bg-warning text-dark'
                                        };
                                    @endphp
                                    <tr>
                                        <td class="ps-3 text-muted fw-semibold">
                                            {{ $loop->iteration + ($leaves->currentPage() - 1) * $leaves->perPage() }}
                                        </td>
                                        @if($canApprove)
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="avatar-xs flex-shrink-0">
                                                        <span class="avatar-title rounded-circle bg-primary-subtle text-primary fw-bold">
                                                            {{ strtoupper(substr($leave->user?->name ?? 'U', 0, 2)) }}
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold text-dark">{{ $leave->user?->name ?? 'Unknown User' }}</div>
                                                        <div class="small text-muted">
                                                            <span class="badge bg-light text-secondary border px-1 py-0">{{ $leave->user?->linkedEmployee()?->employee_no ?? 'SZ Internal' }}</span>
                                                            &bull; {{ $leave->user?->email }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        @endif
                                        <td>
                                            <span class="badge {{ $typeBadgeClass }} px-2 py-1 fs-12 fw-semibold">
                                                {{ $leave->leave_type }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-dark">
                                                <i class="ri-calendar-line text-muted me-1"></i>
                                                {{ $fromDate->format('d M Y') }}
                                                @if($fromDate->ne($toDate))
                                                    <span class="text-muted">&rarr;</span> {{ $toDate->format('d M Y') }}
                                                @endif
                                            </div>
                                            <span class="badge bg-light text-dark border px-2 py-0 fs-11 mt-1">
                                                {{ (float) $leave->number_of_days }} {{ (float) $leave->number_of_days == 1 ? 'Day' : 'Days' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="p-2 rounded bg-light border text-break small" style="max-height: 85px; overflow-y: auto;">
                                                {{ $leave->reason }}
                                            </div>
                                        </td>
                                        <td>
                                            @if($leave->attachment)
                                                <a href="{{ route('admin.leaves.attachment', $leave) }}" class="btn btn-sm btn-outline-primary rounded-pill px-2 py-0" title="Download Document">
                                                    <i class="ri-attachment-line me-1"></i> File
                                                </a>
                                            @else
                                                <span class="text-muted small">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge {{ $statusBadgeClass }} px-2 py-1 fs-12 fw-bold text-uppercase">
                                                {{ $leave->status }}
                                            </span>
                                            @if($leave->status === 'rejected' && $leave->review_remarks)
                                                <div class="mt-1 small text-danger p-1 rounded bg-danger-subtle border border-danger-subtle" style="max-width: 220px;">
                                                    <strong>Reason:</strong> {{ $leave->review_remarks }}
                                                </div>
                                            @elseif($leave->status === 'approved' && $leave->review_remarks)
                                                <div class="mt-1 small text-muted">
                                                    <i class="ri-chat-1-line me-1"></i>{{ $leave->review_remarks }}
                                                </div>
                                            @endif
                                            @if($leave->reviewer)
                                                <div class="text-muted small mt-1" style="font-size: 11px;">
                                                    By {{ $leave->reviewer->name }} &bull; {{ $leave->reviewed_at?->format('d M, h:i A') }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="text-end pe-3">
                                            @if($canApprove && $leave->status === 'pending')
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-sm btn-success rounded-start px-2"
                                                        data-bs-toggle="modal" data-bs-target="#approveLeaveModal"
                                                        data-leave-id="{{ $leave->id }}"
                                                        data-employee="{{ $leave->user?->name }}"
                                                        data-type="{{ $leave->leave_type }}"
                                                        data-days="{{ $leave->number_of_days }}"
                                                        data-action="{{ route('admin.leaves.review', $leave) }}">
                                                        <i class="ri-check-line me-1"></i> Approve
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-danger rounded-end px-2"
                                                        data-bs-toggle="modal" data-bs-target="#rejectLeaveModal"
                                                        data-leave-id="{{ $leave->id }}"
                                                        data-employee="{{ $leave->user?->name }}"
                                                        data-type="{{ $leave->leave_type }}"
                                                        data-days="{{ $leave->number_of_days }}"
                                                        data-action="{{ route('admin.leaves.review', $leave) }}">
                                                        <i class="ri-close-line me-1"></i> Reject
                                                    </button>
                                                </div>
                                            @elseif(!$canApprove && $leave->status === 'pending' && $leave->user_id === auth()->id())
                                                <form action="{{ route('admin.leaves.cancel', $leave) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to cancel this pending leave request?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2 py-0">
                                                        <i class="ri-delete-bin-line me-1"></i> Cancel
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-muted small">
                                                    <i class="ri-check-double-line text-success me-1"></i> Completed
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $canApprove ? 8 : 7 }}" class="text-center py-5 text-muted">
                                            <i class="ri-calendar-todo-line fs-36 text-muted mb-2 d-block"></i>
                                            <p class="mb-0 fw-semibold">No leave requests found.</p>
                                            <span class="small text-muted">Leave applications and history will appear here.</span>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($leaves->hasPages())
                    <div class="card-footer bg-white border-top p-3">
                        {{ $leaves->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>

@if($canApprove)
    <!-- Modal: Approve Leave Request -->
    <div class="modal fade" id="approveLeaveModal" tabindex="-1" aria-labelledby="approveLeaveModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <form id="approveLeaveForm" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="status" value="approved">
                    <div class="modal-header bg-success text-white py-3">
                        <h5 class="modal-title fw-bold" id="approveLeaveModalLabel">
                            <i class="ri-checkbox-circle-line me-1"></i> Approve Leave Request
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="alert alert-success-subtle border border-success-subtle text-success small mb-3">
                            <i class="ri-information-line me-1"></i>
                            Approving will automatically mark this internal employee as <strong>"On Leave"</strong> in the Attendance Module for the requested dates.
                        </div>

                        <div class="mb-3 p-3 bg-light rounded-3 border">
                            <div class="small text-muted text-uppercase fw-semibold">Request Details</div>
                            <div class="fw-bold text-dark fs-15 mt-1" id="approveModalDetails">—</div>
                        </div>

                        <div class="mb-0">
                            <label class="form-label fw-semibold">Approval Remarks (Optional)</label>
                            <input type="text" name="review_remarks" class="form-control" placeholder="e.g. Approved. Enjoy your time off!">
                        </div>
                    </div>
                    <div class="modal-footer bg-light py-2">
                        <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success rounded-pill px-4 fw-semibold">
                            <i class="ri-check-line me-1"></i> Confirm Approval
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal: Reject Leave Request (Reason REQUIRED) -->
    <div class="modal fade" id="rejectLeaveModal" tabindex="-1" aria-labelledby="rejectLeaveModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <form id="rejectLeaveForm" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="status" value="rejected">
                    <div class="modal-header bg-danger text-white py-3">
                        <h5 class="modal-title fw-bold" id="rejectLeaveModalLabel">
                            <i class="ri-close-circle-line me-1"></i> Reject Leave Request
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="alert alert-danger-subtle border border-danger-subtle text-danger small mb-3">
                            <i class="ri-alert-line me-1"></i>
                            <strong>Reason is mandatory.</strong> The rejection reason will be recorded and notified to the employee.
                        </div>

                        <div class="mb-3 p-3 bg-light rounded-3 border">
                            <div class="small text-muted text-uppercase fw-semibold">Request Details</div>
                            <div class="fw-bold text-dark fs-15 mt-1" id="rejectModalDetails">—</div>
                        </div>

                        <div class="mb-0">
                            <label class="form-label fw-semibold">Reason for Rejection <span class="text-danger">*</span></label>
                            <textarea name="review_remarks" rows="3" class="form-control" placeholder="Please state clearly why this leave request is being rejected..." required minlength="3" maxlength="2000"></textarea>
                            <div class="form-text text-muted">A clear reason helps the employee understand why the request was declined.</div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light py-2">
                        <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger rounded-pill px-4 fw-semibold">
                            <i class="ri-close-line me-1"></i> Confirm Rejection
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

@endsection

@section('script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const approveModal = document.getElementById('approveLeaveModal');
    if (approveModal) {
        approveModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const action = button.getAttribute('data-action');
            const employee = button.getAttribute('data-employee') || 'Employee';
            const type = button.getAttribute('data-type') || 'Leave';
            const days = button.getAttribute('data-days') || '1';

            const form = document.getElementById('approveLeaveForm');
            form.setAttribute('action', action);

            const details = document.getElementById('approveModalDetails');
            details.textContent = employee + ' • ' + type + ' (' + days + ' days)';
        });
    }

    const rejectModal = document.getElementById('rejectLeaveModal');
    if (rejectModal) {
        rejectModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const action = button.getAttribute('data-action');
            const employee = button.getAttribute('data-employee') || 'Employee';
            const type = button.getAttribute('data-type') || 'Leave';
            const days = button.getAttribute('data-days') || '1';

            const form = document.getElementById('rejectLeaveForm');
            form.setAttribute('action', action);

            const details = document.getElementById('rejectModalDetails');
            details.textContent = employee + ' • ' + type + ' (' + days + ' days)';
        });
    }
});
</script>
@endsection
