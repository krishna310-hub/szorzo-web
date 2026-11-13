@extends('backend.layouts.master')
@section('title', 'Holiday Management')

@section('content')
@include('backend.attendance._styles')

<div class="main-content atl-shell">
    <div class="page-content">
        <div class="container-fluid">

            <!-- Header -->
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                <div>
                    <h3 class="fw-bold text-dark mb-1">
                        <i class="ri-calendar-2-line text-primary me-2"></i>
                        Holiday Management
                    </h3>
                    <p class="text-muted mb-0 small">
                        Manage company and public holidays for the year. Dates saved here will automatically mark Attendance records as <strong>Holiday</strong>.
                    </p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    @can('create', \App\Models\Holiday::class)
                        <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#addHolidayModal">
                            <i class="ri-add-line me-1"></i> Add Holiday
                        </button>
                    @endcan
                    <a class="btn btn-outline-secondary rounded-pill px-3 shadow-sm fw-semibold" href="{{ route('admin.attendance.monthly') }}">
                        <i class="ri-calendar-check-line me-1"></i> Monthly Attendance
                    </a>
                </div>
            </div>

            <!-- Session Alerts -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
                    <i class="ri-checkbox-circle-fill me-1 align-middle fs-16"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                    <i class="ri-error-warning-fill me-1 align-middle fs-16"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Summary KPI Cards -->
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm rounded-4 h-100 bg-white p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted text-uppercase fw-semibold small">Holidays in {{ $year }}</span>
                            <span class="badge bg-primary-subtle text-primary rounded-circle p-2">
                                <i class="ri-calendar-fill fs-18"></i>
                            </span>
                        </div>
                        <h3 class="fw-bold text-dark mb-0">{{ $totalHolidays }}</h3>
                        <span class="text-muted small mt-1 d-block">Scheduled for year {{ $year }}</span>
                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm rounded-4 h-100 bg-white p-3 border-start border-success border-4">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted text-uppercase fw-semibold small">Upcoming</span>
                            <span class="badge bg-success-subtle text-success rounded-circle p-2">
                                <i class="ri-time-line fs-18"></i>
                            </span>
                        </div>
                        <h3 class="fw-bold text-success mb-0">{{ $upcomingCount }}</h3>
                        <span class="text-muted small mt-1 d-block">Remaining in {{ $year }}</span>
                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm rounded-4 h-100 bg-white p-3 border-start border-secondary border-4">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted text-uppercase fw-semibold small">Past Holidays</span>
                            <span class="badge bg-secondary-subtle text-secondary rounded-circle p-2">
                                <i class="ri-history-line fs-18"></i>
                            </span>
                        </div>
                        <h3 class="fw-bold text-muted mb-0">{{ $pastCount }}</h3>
                        <span class="text-muted small mt-1 d-block">Completed in {{ $year }}</span>
                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm rounded-4 h-100 bg-white p-3 border-start border-info border-4">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted text-uppercase fw-semibold small">Next Holiday</span>
                            <span class="badge bg-info-subtle text-info rounded-circle p-2">
                                <i class="ri-flag-2-line fs-18"></i>
                            </span>
                        </div>
                        @if($nextHoliday)
                            <h6 class="fw-bold text-dark mb-1 text-truncate" title="{{ $nextHoliday->title }}">{{ $nextHoliday->title }}</h6>
                            <span class="text-info fw-semibold small">{{ $nextHoliday->formatted_date }} ({{ $nextHoliday->day_name }})</span>
                        @else
                            <h6 class="fw-bold text-muted mb-0">None upcoming</h6>
                            <span class="text-muted small">No future dates set</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Filter Card -->
            <div class="card atl-card mb-4 border-0 shadow-sm">
                <div class="card-body p-3">
                    <form method="GET" action="{{ route('admin.holidays.index') }}" class="row g-2 align-items-center">
                        <div class="col-12 col-md-3">
                            <label class="form-label small text-muted mb-1 fw-semibold">Select Year</label>
                            <select name="year" class="form-select">
                                @foreach($allYears as $y)
                                    <option value="{{ $y }}" @selected($year === (int)$y)>Year {{ $y }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small text-muted mb-1 fw-semibold">Search</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 text-muted"><i class="ri-search-line"></i></span>
                                <input type="text" name="search" class="form-control border-start-0" placeholder="Search by holiday name, type, or notes..." value="{{ $search ?? '' }}">
                            </div>
                        </div>
                        <div class="col-12 col-md-3 d-flex gap-2 align-self-end">
                            <button type="submit" class="btn btn-dark flex-grow-1">
                                <i class="ri-filter-line me-1"></i> Filter
                            </button>
                            <a href="{{ route('admin.holidays.index') }}" class="btn btn-light" title="Reset Filters">
                                <i class="ri-refresh-line"></i>
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Holidays List Table -->
            <div class="card atl-card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="card-title mb-0 fw-bold">
                        Holidays for Year {{ $year }}
                        <span class="badge bg-primary-subtle text-primary ms-1">{{ $holidays->total() }}</span>
                    </h5>
                    <span class="small text-muted">
                        <i class="ri-shield-check-line text-success me-1"></i> Auto-synced with Attendance Module
                    </span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 60px;">#</th>
                                <th>Date & Day</th>
                                <th>Holiday Name</th>
                                <th>Category</th>
                                <th>Description / Notes</th>
                                <th>Status</th>
                                <th class="text-end" style="width: 140px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($holidays as $h)
                                <tr>
                                    <td>{{ $loop->iteration + ($holidays->currentPage() - 1) * $holidays->perPage() }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-3 text-center p-2 bg-light border" style="min-width: 54px;">
                                                <div class="fw-bold text-dark fs-14">{{ $h->date?->format('d') }}</div>
                                                <div class="text-muted text-uppercase" style="font-size: 10px;">{{ $h->date?->format('M') }}</div>
                                            </div>
                                            <div>
                                                <div class="fw-semibold text-dark">{{ $h->formatted_date }}</div>
                                                <small class="text-muted">{{ $h->day_name }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark fs-14">{{ $h->title }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1">
                                            {{ $h->type ?: 'Company' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-muted small">{{ $h->description ?: '—' }}</span>
                                    </td>
                                    <td>
                                        @if($h->is_today)
                                            <span class="badge bg-danger px-2 py-1"><i class="ri-fire-fill me-1"></i>Today</span>
                                        @elseif($h->is_upcoming)
                                            @php
                                                $daysLeft = now()->startOfDay()->diffInDays($h->date->startOfDay(), false);
                                            @endphp
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                                In {{ $daysLeft }} day{{ $daysLeft == 1 ? '' : 's' }}
                                            </span>
                                        @else
                                            <span class="badge bg-light text-muted border px-2 py-1">
                                                Past
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-1">
                                            @can('edit', \App\Models\Holiday::class)
                                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill edit-holiday-btn"
                                                    data-id="{{ $h->id }}"
                                                    data-title="{{ $h->title }}"
                                                    data-date="{{ $h->date?->toDateString() }}"
                                                    data-type="{{ $h->type }}"
                                                    data-description="{{ $h->description }}"
                                                    title="Edit Holiday">
                                                    <i class="ri-edit-line"></i>
                                                </button>
                                            @endcan
                                            @can('delete', \App\Models\Holiday::class)
                                                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill delete-holiday-btn"
                                                    data-id="{{ $h->id }}"
                                                    data-title="{{ $h->title }}"
                                                    data-date="{{ $h->formatted_date }}"
                                                    title="Delete Holiday">
                                                    <i class="ri-delete-bin-line"></i>
                                                </button>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="py-4">
                                            <i class="ri-calendar-2-line text-muted" style="font-size: 48px;"></i>
                                            <h6 class="fw-semibold text-dark mt-3">No Holidays Found for Year {{ $year }}</h6>
                                            <p class="text-muted small mb-3">Add current year leaves and company holidays to automatically reflect them in daily and monthly attendance.</p>
                                            @can('create', \App\Models\Holiday::class)
                                                <button type="button" class="btn btn-primary btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#addHolidayModal">
                                                    <i class="ri-add-line me-1"></i> Add First Holiday
                                                </button>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($holidays->hasPages())
                    <div class="card-footer bg-white border-top py-3">
                        {{ $holidays->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>

<!-- Add Holiday Modal -->
@can('create', \App\Models\Holiday::class)
<div class="modal fade atl-modal" id="addHolidayModal" tabindex="-1" aria-labelledby="addHolidayModalLabel" aria-hidden="true" style="z-index: 1080 !important;">
    <div class="modal-dialog modal-dialog-centered" style="z-index: 1085 !important; position: relative !important;">
        <div class="modal-content border-0 shadow-lg" style="background-color: #ffffff !important; position: relative !important; z-index: 1090 !important; box-shadow: 0 16px 48px rgba(0,0,0,0.4) !important;">
            <form method="POST" action="{{ route('admin.holidays.store') }}">
                @csrf
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold" id="addHolidayModalLabel">
                        <i class="ri-calendar-event-line text-primary me-2"></i> Add Holiday
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Holiday Title / Name <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Republic Day, Independence Day, Christmas" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-7">
                            <label class="form-label fw-semibold">Holiday Date <span class="text-danger">*</span></label>
                            <input type="date" name="date" class="form-control" value="{{ now()->toDateString() }}" required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-semibold">Category</label>
                            <select name="type" class="form-select">
                                @foreach(\App\Models\Holiday::TYPES as $type)
                                    <option value="{{ $type }}">{{ $type }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Description / Notes <small class="text-muted">(Optional)</small></label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Optional notes or festive details..."></textarea>
                    </div>

                    <div class="alert alert-info py-2 px-3 mb-0 small border-0 bg-info-subtle text-info d-flex align-items-center">
                        <i class="ri-information-line fs-18 me-2"></i>
                        <span>This date will automatically mark attendance as <strong>Holiday</strong> for all active employees.</span>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">
                        <i class="ri-save-line me-1"></i> Save Holiday
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

<!-- Edit Holiday Modal -->
@can('edit', \App\Models\Holiday::class)
<div class="modal fade atl-modal" id="editHolidayModal" tabindex="-1" aria-labelledby="editHolidayModalLabel" aria-hidden="true" style="z-index: 1080 !important;">
    <div class="modal-dialog modal-dialog-centered" style="z-index: 1085 !important; position: relative !important;">
        <div class="modal-content border-0 shadow-lg" style="background-color: #ffffff !important; position: relative !important; z-index: 1090 !important; box-shadow: 0 16px 48px rgba(0,0,0,0.4) !important;">
            <form method="POST" action="" id="editHolidayForm">
                @csrf
                @method('PUT')
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold" id="editHolidayModalLabel">
                        <i class="ri-edit-line text-primary me-2"></i> Edit Holiday
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Holiday Title / Name <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="editHolidayTitle" class="form-control" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-7">
                            <label class="form-label fw-semibold">Holiday Date <span class="text-danger">*</span></label>
                            <input type="date" name="date" id="editHolidayDate" class="form-control" required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-semibold">Category</label>
                            <select name="type" id="editHolidayType" class="form-select">
                                @foreach(\App\Models\Holiday::TYPES as $type)
                                    <option value="{{ $type }}">{{ $type }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Description / Notes <small class="text-muted">(Optional)</small></label>
                        <textarea name="description" id="editHolidayDescription" class="form-control" rows="2"></textarea>
                    </div>

                    <div class="alert alert-warning py-2 px-3 mb-0 small border-0 bg-warning-subtle text-dark d-flex align-items-center">
                        <i class="ri-alert-line fs-18 me-2"></i>
                        <span>Modifying the date will adjust auto-marked attendance records accordingly.</span>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">
                        <i class="ri-save-line me-1"></i> Update Holiday
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

<!-- Delete Holiday Modal -->
@can('delete', \App\Models\Holiday::class)
<div class="modal fade atl-modal" id="deleteHolidayModal" tabindex="-1" aria-hidden="true" style="z-index: 1080 !important;">
    <div class="modal-dialog modal-dialog-centered modal-sm" style="z-index: 1085 !important; position: relative !important;">
        <div class="modal-content border-0 shadow-lg" style="background-color: #ffffff !important; position: relative !important; z-index: 1090 !important; box-shadow: 0 16px 48px rgba(0,0,0,0.4) !important;">
            <form method="POST" action="" id="deleteHolidayForm">
                @csrf
                @method('DELETE')
                <div class="modal-body p-4 text-center">
                    <div class="text-danger mb-3">
                        <i class="ri-delete-bin-line" style="font-size: 48px;"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Delete Holiday?</h5>
                    <p class="text-muted small mb-3">
                        Are you sure you want to delete <strong id="deleteHolidayName" class="text-dark"></strong> on <span id="deleteHolidayDateStr"></span>?
                        Auto-marked attendance records for this date will be cleaned up.
                    </p>
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger rounded-pill px-3 fw-semibold">Delete</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

@endsection

@section('script')
<script>
function ensureHolidayModalsInBody() {
    ['addHolidayModal', 'editHolidayModal', 'deleteHolidayModal'].forEach(function(id) {
        var el = document.getElementById(id);
        if (el && el.parentElement !== document.body) {
            document.body.appendChild(el);
        }
    });
}
ensureHolidayModalsInBody();

document.addEventListener('DOMContentLoaded', function () {
    ensureHolidayModalsInBody();

    ['addHolidayModal', 'editHolidayModal', 'deleteHolidayModal'].forEach(function(id) {
        var el = document.getElementById(id);
        if (el) {
            el.addEventListener('show.bs.modal', ensureHolidayModalsInBody);
            el.addEventListener('hidden.bs.modal', function () {
                document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
                document.body.classList.remove('modal-open');
                document.body.style.overflow = '';
                document.body.style.paddingRight = '';
            });
        }
    });

    const editModalEl = document.getElementById('editHolidayModal');
    const editModal = editModalEl ? new bootstrap.Modal(editModalEl) : null;
    const editForm = document.getElementById('editHolidayForm');

    document.querySelectorAll('.edit-holiday-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.getAttribute('data-id');
            const title = this.getAttribute('data-title');
            const date = this.getAttribute('data-date');
            const type = this.getAttribute('data-type');
            const desc = this.getAttribute('data-description');

            if (editForm) {
                editForm.action = '{{ url("admin/holidays") }}/' + id;
                document.getElementById('editHolidayTitle').value = title || '';
                document.getElementById('editHolidayDate').value = date || '';
                document.getElementById('editHolidayType').value = type || 'Company';
                document.getElementById('editHolidayDescription').value = desc || '';
                editModal.show();
            }
        });
    });

    const deleteModalEl = document.getElementById('deleteHolidayModal');
    const deleteModal = deleteModalEl ? new bootstrap.Modal(deleteModalEl) : null;
    const deleteForm = document.getElementById('deleteHolidayForm');

    document.querySelectorAll('.delete-holiday-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.getAttribute('data-id');
            const title = this.getAttribute('data-title');
            const date = this.getAttribute('data-date');

            if (deleteForm) {
                deleteForm.action = '{{ url("admin/holidays") }}/' + id;
                document.getElementById('deleteHolidayName').textContent = title || 'this holiday';
                document.getElementById('deleteHolidayDateStr').textContent = date || '';
                deleteModal.show();
            }
        });
    });
});
</script>
@endsection
