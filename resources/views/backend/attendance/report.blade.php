@extends('backend.layouts.master')
@section('title', 'Detailed Attendance Reports')
@section('content')
@include('backend.attendance._styles')
<div class="main-content atl-shell">
    <div class="page-content">
        <div class="container-fluid">
            <!-- Header -->
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                <div>
                    <h3 class="mb-1">Detailed Attendance Reports</h3>
                    <p class="text-muted small mb-0">Comprehensive tracking of employee working hours, tea breaks, and lunch duration.</p>
                </div>
                <div class="d-flex gap-2">
                    <a class="btn btn-outline-secondary" href="{{ route('admin.attendance.monthly') }}">Monthly View</a>
                    <a class="btn btn-outline-dark" href="{{ route('admin.attendance.dashboard') }}">Dashboard</a>
                </div>
            </div>

            <!-- Filter Card -->
            <div class="card atl-card mb-3">
                <div class="card-body">
                    <form id="report-filter" class="row g-2 align-items-center">
                        <div class="col-md-2 col-sm-6">
                            <label class="form-label small text-muted mb-1">From Date</label>
                            <input type="date" name="from_date" value="{{ $filters['from_date'] ?? '' }}" class="form-control" title="From date">
                        </div>
                        <div class="col-md-2 col-sm-6">
                            <label class="form-label small text-muted mb-1">To Date</label>
                            <input type="date" name="to_date" value="{{ $filters['to_date'] ?? '' }}" class="form-control" title="To date">
                        </div>
                        <div class="col-md-2 col-sm-6">
                            <label class="form-label small text-muted mb-1">Month</label>
                            <select name="month" class="form-select">
                                <option value="">Any month</option>
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" @selected(($filters['month'] ?? '') == $m)>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-1 col-sm-6">
                            <label class="form-label small text-muted mb-1">Year</label>
                            <input name="year" type="number" value="{{ $filters['year'] ?? '' }}" min="2000" max="2100" placeholder="Year" class="form-control">
                        </div>
                        <div class="col-md-2 col-sm-6">
                            <label class="form-label small text-muted mb-1">Employee / User</label>
                            <select name="user_id" class="form-select">
                                <option value="">All employees</option>
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}" @selected(($filters['user_id'] ?? '') == $u->id)>{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 col-sm-6">
                            <label class="form-label small text-muted mb-1">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All statuses</option>
                                @foreach(\App\Models\Attendance::STATUSES as $s)
                                    <option value="{{ $s }}" @selected(($filters['status'] ?? '') === $s)>{{ ucwords(str_replace('_', ' ', $s)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-1 col-sm-12 d-flex align-items-end">
                            <button type="submit" class="btn btn-dark w-100 mt-auto" style="height: 38px;">Filter</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Export Buttons -->
            @can('export', \App\Models\Attendance::class)
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                <div class="d-flex flex-wrap gap-2">
                    <span class="text-muted small align-self-center me-1">Export:</span>
                    @foreach(['print' => 'Print', 'pdf' => 'PDF Report', 'xlsx' => 'Excel (.xlsx)', 'csv' => 'CSV Export'] as $format => $label)
                        <a target="{{ $format === 'print' ? '_blank' : '_self' }}" class="btn btn-outline-secondary btn-sm export-link" href="{{ route('admin.attendance.export', $format) }}">
                            <i class="ri-download-2-line me-1"></i>{{ $label }}
                        </a>
                    @endforeach
                </div>
                <div class="text-muted small">
                    Showing <strong>{{ $records->total() }}</strong> records
                </div>
            </div>
            @endcan

            <!-- Status Summary Cards -->
            <div class="row g-2 mb-3">
                @foreach(\App\Models\Attendance::STATUSES as $s)
                    <div class="col-6 col-md-4 col-xl-2">
                        <div class="card atl-stat">
                            <div class="card-body py-2 px-3">
                                <small class="text-muted">{{ ucwords(str_replace('_', ' ', $s)) }}</small>
                                <div class="fs-4 fw-bold">{{ (int)($summary[$s] ?? 0) }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Executive Break & Work Time Summary Cards -->
            <div class="row g-2 mb-4">
                <div class="col-6 col-md-3">
                    <div class="card atl-stat border-start border-4 border-success">
                        <div class="card-body py-3">
                            <small class="text-muted text-uppercase fw-semibold d-block mb-1">Total Work Time</small>
                            <div class="fs-3 fw-bold text-success">{{ $timeMetrics['total_work_formatted'] ?? '0m' }}</div>
                            <span class="badge bg-success-subtle text-success font-monospace mt-1">{{ \App\Models\Attendance::formatSecondsToTime($timeMetrics['total_work_seconds'] ?? 0) }} (hh:mm)</span>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card atl-stat border-start border-4 border-warning">
                        <div class="card-body py-3">
                            <small class="text-muted text-uppercase fw-semibold d-block mb-1">Total Break (Tea)</small>
                            <div class="fs-3 fw-bold text-warning">{{ $timeMetrics['total_break_formatted'] ?? '0m' }}</div>
                            <span class="badge bg-warning-subtle text-dark font-monospace mt-1">{{ \App\Models\Attendance::formatSecondsToTime($timeMetrics['total_break_seconds'] ?? 0) }} (hh:mm)</span>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card atl-stat border-start border-4 border-info">
                        <div class="card-body py-3">
                            <small class="text-muted text-uppercase fw-semibold d-block mb-1">Total Lunch Time</small>
                            <div class="fs-3 fw-bold text-info">{{ $timeMetrics['total_lunch_formatted'] ?? '0m' }}</div>
                            <span class="badge bg-info-subtle text-info font-monospace mt-1">{{ \App\Models\Attendance::formatSecondsToTime($timeMetrics['total_lunch_seconds'] ?? 0) }} (hh:mm)</span>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card atl-stat border-start border-4 border-primary">
                        <div class="card-body py-3">
                            <small class="text-muted text-uppercase fw-semibold d-block mb-1">Combined Total Break</small>
                            <div class="fs-3 fw-bold text-primary">{{ $timeMetrics['total_combined_break_formatted'] ?? '0m' }}</div>
                            <span class="badge bg-primary-subtle text-primary font-monospace mt-1">{{ \App\Models\Attendance::formatSecondsToTime($timeMetrics['total_combined_seconds'] ?? 0) }} (Tea + Lunch)</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Attendance Table -->
            <div class="card atl-card">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h5 class="mb-0">Detailed Attendance & Break Time Log</h5>
                </div>
                <div class="table-responsive">
                    <table class="table attendance-table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Employee / User</th>
                                <th>Status</th>
                                <th>Timer</th>
                                <th>Check In</th>
                                <th>Check Out</th>
                                <th>Work Hours</th>
                                <th>Break Time</th>
                                <th>Lunch Time</th>
                                <th>Total Break</th>
                                <th>Break Details</th>
                                <th>Leave / Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($records as $a)
                                <tr>
                                    <!-- Date -->
                                    <td>
                                        <div class="fw-semibold">{{ $a->attendance_date?->format('d M Y') }}</div>
                                        <small class="text-muted">{{ $a->attendance_date?->format('l') }}</small>
                                    </td>

                                    <!-- Employee / User -->
                                    <td>
                                        <div class="fw-bold">{{ $a->display_name }}</div>
                                        <small class="text-muted">
                                            <span class="badge bg-light text-dark border me-1">ID: {{ $a->employee_no }}</span>
                                            {{ $a->user?->email ?? $a->employee?->official_mail ?? '' }}
                                        </small>
                                    </td>

                                    <!-- Status -->
                                    <td>
                                        <span class="status-badge s-{{ $a->status }}">
                                            {{ ucwords(str_replace('_', ' ', $a->status)) }}
                                        </span>
                                    </td>

                                    <!-- Timer Status -->
                                    <td>
                                        <span class="timer-badge t-{{ $a->timer_status ?? 'not_started' }}">
                                            {{ ucwords(str_replace('_', ' ', $a->timer_status ?? 'not_started')) }}
                                        </span>
                                    </td>

                                    <!-- Check In -->
                                    <td>
                                        @if($a->check_in)
                                            <span class="font-monospace text-dark">{{ substr((string)$a->check_in, 0, 5) }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>

                                    <!-- Check Out -->
                                    <td>
                                        @if($a->check_out)
                                            <span class="font-monospace text-dark">{{ substr((string)$a->check_out, 0, 5) }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>

                                    <!-- Work Hours -->
                                    <td>
                                        <div class="font-monospace fw-semibold text-dark">{{ $a->formatted_work_time }}</div>
                                        <small class="text-muted">{{ \App\Models\Attendance::formatSecondsHuman($a->current_work_seconds ?: ($a->working_minutes * 60)) }}</small>
                                    </td>

                                    <!-- Break Time (Tea) -->
                                    <td>
                                        <div class="font-monospace text-warning fw-semibold">{{ $a->formatted_break_time }}</div>
                                        @if($a->break_count > 0)
                                            <small class="badge bg-warning-subtle text-dark border border-warning-subtle">{{ $a->break_count }} {{ Str::plural('break', $a->break_count) }}</small>
                                        @endif
                                    </td>

                                    <!-- Lunch Time -->
                                    <td>
                                        <div class="font-monospace text-info fw-semibold">{{ $a->formatted_lunch_time }}</div>
                                        @if($a->lunch_count > 0)
                                            <small class="badge bg-info-subtle text-info border border-info-subtle">{{ $a->lunch_count }} lunch</small>
                                        @endif
                                    </td>

                                    <!-- Total Break -->
                                    <td>
                                        <div class="font-monospace fw-bold text-dark">{{ $a->formatted_total_break_time }}</div>
                                        <small class="text-muted">{{ \App\Models\Attendance::formatSecondsHuman($a->combined_break_seconds) }}</small>
                                    </td>

                                    <!-- Break Details Logs Dropdown -->
                                    <td>
                                        @php
                                            $breakList = is_array($a->breaks) ? $a->breaks : [];
                                            $hasActiveBreak = $a->break_started_at && in_array($a->timer_status, [\App\Models\Attendance::TIMER_ON_BREAK, \App\Models\Attendance::TIMER_ON_LUNCH]);
                                            $totalEntries = count($breakList) + ($hasActiveBreak ? 1 : 0);
                                        @endphp

                                        @if($totalEntries > 0)
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle py-0 px-2 font-monospace" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    Logs ({{ $totalEntries }})
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end shadow-sm p-2" style="min-width: 250px; font-size: 0.82rem;">
                                                    <li class="dropdown-header fw-bold text-uppercase pb-1 px-1">Break Time History</li>
                                                    @foreach($breakList as $b)
                                                        @php
                                                            $bType = strtolower((string)($b['type'] ?? 'break'));
                                                            $bSecs = (int)($b['seconds'] ?? round(($b['minutes'] ?? 0) * 60));
                                                        @endphp
                                                        <li class="px-1 py-1 border-bottom">
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <div>
                                                                    <span class="badge {{ $bType === 'lunch' ? 'bg-info text-white' : 'bg-warning text-dark' }} me-1">
                                                                        {{ ucfirst($bType) }}
                                                                    </span>
                                                                    <span class="text-muted">{{ substr((string)($b['start'] ?? ''), 0, 5) }} - {{ substr((string)($b['end'] ?? ''), 0, 5) }}</span>
                                                                </div>
                                                                <strong class="font-monospace text-dark">{{ \App\Models\Attendance::formatSecondsHuman($bSecs) }}</strong>
                                                            </div>
                                                        </li>
                                                    @endforeach
                                                    @if($hasActiveBreak)
                                                        <li class="px-1 py-1 text-danger">
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <div>
                                                                    <span class="badge bg-danger me-1">Live {{ $a->timer_status === \App\Models\Attendance::TIMER_ON_LUNCH ? 'Lunch' : 'Break' }}</span>
                                                                    <span class="text-muted">Started {{ $a->break_started_at->format('H:i') }}</span>
                                                                </div>
                                                                <span class="badge bg-danger-subtle text-danger">Active</span>
                                                            </div>
                                                        </li>
                                                    @endif
                                                </ul>
                                            </div>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>

                                    <!-- Leave / Remarks -->
                                    <td>
                                        @if($a->leaveRequest?->leave_type)
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle d-inline-block mb-1">{{ $a->leaveRequest->leave_type }}</span>
                                        @endif
                                        @if($a->remarks)
                                            <div class="small text-muted">{{ $a->remarks }}</div>
                                        @endif
                                        @if(!$a->leaveRequest?->leave_type && !$a->remarks)
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="12" class="text-center py-5 text-muted">
                                        <i class="ri-file-search-line fs-2 d-block mb-2 text-secondary"></i>
                                        No attendance records match the selected filters.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($records->hasPages())
                    <div class="card-footer bg-transparent border-top py-2">
                        {{ $records->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
@section('script')
<script>
document.querySelectorAll('.export-link').forEach(a => {
    a.addEventListener('click', function(e) {
        const form = document.getElementById('report-filter');
        if (form) {
            const params = new URLSearchParams(new FormData(form)).toString();
            this.href = this.href.split('?')[0] + (params ? '?' + params : '');
        }
    });
});
</script>
@endsection
