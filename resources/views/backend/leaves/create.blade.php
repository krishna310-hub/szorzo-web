@extends('backend.layouts.master')
@section('title', 'Apply for Leave')

@section('content')
@include('backend.attendance._styles')

<div class="main-content atl-shell">
    <div class="page-content">
        <div class="container-fluid">

            <div class="row justify-content-center">
                <div class="col-12 col-lg-9 col-xl-8">

                    <!-- Top Bar -->
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                        <div>
                            <h3 class="fw-bold text-dark mb-1">
                                <i class="ri-calendar-event-line text-primary me-2"></i>Apply for Leave
                            </h3>
                            <p class="text-muted mb-0 small">Internal employees can submit full-day or half-day leave requests for admin approval.</p>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-outline-info rounded-pill px-3 fw-semibold" data-bs-toggle="modal" data-bs-target="#employeeHolidaysModal">
                                <i class="ri-calendar-2-line me-1"></i> View Holidays
                            </button>
                            <a href="{{ route('admin.leaves.index') }}" class="btn btn-outline-dark rounded-pill px-3 fw-semibold">
                                <i class="ri-arrow-left-line me-1"></i> Back to Leave History
                            </a>
                        </div>
                    </div>

                    @if(session('error'))
                        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
                            <i class="ri-error-warning-line me-2"></i>{{ session('error') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
                            <div class="fw-bold mb-1"><i class="ri-error-warning-line me-2"></i>Please resolve the following issues:</div>
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Application Form Card -->
                    <div class="card atl-card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                        <div class="card-header bg-white border-bottom p-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-3 d-flex align-items-center justify-content-center text-primary bg-primary-subtle" style="width: 48px; height: 48px; font-size: 24px;">
                                    <i class="ri-file-user-line"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold text-dark mb-0">Leave Application Form</h5>
                                    <span class="text-muted small">Applicant: <strong>{{ auth()->user()->name }}</strong> ({{ auth()->user()->email }})</span>
                                </div>
                            </div>
                        </div>

                        <div class="card-body p-4">
                            <form action="{{ route('admin.leaves.store') }}" method="POST" enctype="multipart/form-data" id="leaveApplicationForm">
                                @csrf

                                <div class="row g-4">
                                    <!-- Leave Type -->
                                    <div class="col-12 col-md-6">
                                        <label class="form-label fw-semibold text-dark">
                                            Leave Type <span class="text-danger">*</span>
                                        </label>
                                        <select name="leave_type" id="leave_type" class="form-select form-select-lg @error('leave_type') is-invalid @enderror" required>
                                            <option value="">Select leave type...</option>
                                            @foreach(\App\Models\LeaveRequest::TYPES as $type)
                                                <option value="{{ $type }}" @selected(old('leave_type') === $type)>{{ $type }}</option>
                                            @endforeach
                                        </select>
                                        @error('leave_type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Duration Indicator Badge -->
                                    <div class="col-12 col-md-6 d-flex align-items-end">
                                        <div id="durationCard" class="p-3 rounded-3 bg-light border w-100 d-flex align-items-center justify-content-between">
                                            <span class="text-muted fw-semibold small">Calculated Duration:</span>
                                            <span class="badge bg-primary fs-14 px-3 py-2 rounded-pill" id="calculatedDaysBadge">1 Day</span>
                                        </div>
                                    </div>

                                    <!-- Day Type Selection: Full Day vs Half Day -->
                                    <div class="col-12">
                                        <label class="form-label fw-semibold text-dark mb-2">
                                            Leave Duration Option <span class="text-danger">*</span>
                                        </label>
                                        <div class="d-flex flex-wrap gap-3">
                                            <div class="form-check p-0 m-0">
                                                <input class="btn-check" type="radio" name="day_type" id="day_type_full" value="full_day" @checked(old('day_type', 'full_day') === 'full_day') autocomplete="off">
                                                <label class="btn btn-outline-primary px-4 py-2 rounded-pill fw-semibold" for="day_type_full">
                                                    <i class="ri-sun-fill me-1"></i> Full Day
                                                </label>
                                            </div>
                                            <div class="form-check p-0 m-0">
                                                <input class="btn-check" type="radio" name="day_type" id="day_type_half" value="half_day" @checked(old('day_type') === 'half_day') autocomplete="off">
                                                <label class="btn btn-outline-primary px-4 py-2 rounded-pill fw-semibold" for="day_type_half">
                                                    <i class="ri-contrast-2-line me-1"></i> Half Day (Half Leave)
                                                </label>
                                            </div>
                                        </div>
                                        @error('day_type')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Half Day Session Container (Morning vs Afternoon) -->
                                    <div class="col-12" id="halfDaySessionContainer" style="{{ old('day_type') === 'half_day' ? '' : 'display: none;' }}">
                                        <div class="p-3 rounded-3 border bg-light">
                                            <label class="form-label fw-semibold text-dark mb-2">
                                                Choose Half Day Session <span class="text-danger">*</span>
                                            </label>
                                            <div class="d-flex flex-wrap gap-3">
                                                <div class="form-check p-0 m-0">
                                                    <input class="btn-check" type="radio" name="half_day_type" id="session_morning" value="morning" @checked(old('half_day_type', 'morning') === 'morning') autocomplete="off">
                                                    <label class="btn btn-outline-info px-4 py-2 rounded-pill fw-semibold" for="session_morning">
                                                        <i class="ri-sun-line me-1"></i> Morning (1st Half)
                                                    </label>
                                                </div>
                                                <div class="form-check p-0 m-0">
                                                    <input class="btn-check" type="radio" name="half_day_type" id="session_afternoon" value="afternoon" @checked(old('half_day_type') === 'afternoon') autocomplete="off">
                                                    <label class="btn btn-outline-warning px-4 py-2 rounded-pill fw-semibold text-dark" for="session_afternoon">
                                                        <i class="ri-moon-clear-line me-1"></i> Afternoon (2nd Half)
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="form-text text-muted mt-2">
                                                <i class="ri-information-line me-1"></i> When approved by the admin, attendance for this date will be automatically marked as <strong>"Half Day" (0.5 day)</strong>.
                                            </div>
                                            @error('half_day_type')
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- From Date / Leave Date -->
                                    <div class="col-12 col-md-6" id="fromDateCol">
                                        <label class="form-label fw-semibold text-dark" id="fromDateLabel">
                                            From Date <span class="text-danger">*</span>
                                        </label>
                                        <input type="date" name="from_date" id="from_date" class="form-control form-control-lg @error('from_date') is-invalid @enderror" value="{{ old('from_date', now()->toDateString()) }}" required>
                                        @error('from_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- To Date (Only for Full Day) -->
                                    <div class="col-12 col-md-6" id="toDateCol" style="{{ old('day_type') === 'half_day' ? 'display: none;' : '' }}">
                                        <label class="form-label fw-semibold text-dark" id="toDateLabel">
                                            To Date <span class="text-danger">*</span>
                                        </label>
                                        <input type="date" name="to_date" id="to_date" class="form-control form-control-lg @error('to_date') is-invalid @enderror" value="{{ old('to_date', now()->toDateString()) }}" required>
                                        @error('to_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Reason (Mandatory) -->
                                    <div class="col-12">
                                        <label class="form-label fw-semibold text-dark d-flex justify-content-between align-items-center">
                                            <span>Reason for Leave <span class="text-danger">*</span></span>
                                            <span class="text-muted small fw-normal" id="reasonCharCounter">0 / 3000 chars</span>
                                        </label>
                                        <textarea name="reason" id="leave_reason" rows="4" maxlength="3000" class="form-control @error('reason') is-invalid @enderror" placeholder="Please explain the reason for your leave request in detail (e.g. personal commitments, family emergency, unwell / medical doctor appointment)..." required minlength="5">{{ old('reason') }}</textarea>
                                        <div class="form-text text-muted">A clear reason helps the management process your request without delay.</div>
                                        @error('reason')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Optional Attachment -->
                                    <div class="col-12">
                                        <label class="form-label fw-semibold text-dark">
                                            Supporting Document / Attachment <span class="text-muted small fw-normal">(Optional)</span>
                                        </label>
                                        <input type="file" name="attachment" class="form-control @error('attachment') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png">
                                        <div class="form-text text-muted">Upload doctor's prescription, hospital note, or travel tickets if applicable (PDF, JPG, PNG &bull; Max 5 MB).</div>
                                        @error('attachment')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Policy Callout -->
                                    <div class="col-12">
                                        <div class="p-3 rounded-3 bg-light border small text-muted" id="policyCalloutBox">
                                            <i class="ri-shield-check-line text-success me-1"></i>
                                            <strong>Note:</strong> Once submitted, your request will be delivered as an immediate notification to the admin. Upon approval, your status on the attendance module will automatically be marked as <strong id="policyAttendanceStatus">"On Leave"</strong>.
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                                    <a href="{{ route('admin.leaves.index') }}" class="btn btn-light rounded-pill px-4">Cancel</a>
                                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-semibold shadow-sm" id="submitLeaveBtn">
                                        <i class="ri-send-plane-fill me-1"></i> Submit Leave Request
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

@include('backend.leaves._holidays_modal')

@endsection

@section('script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const fromInput = document.getElementById('from_date');
    const toInput = document.getElementById('to_date');
    const fromLabel = document.getElementById('fromDateLabel');
    const toCol = document.getElementById('toDateCol');
    const badge = document.getElementById('calculatedDaysBadge');
    const reasonInput = document.getElementById('leave_reason');
    const charCounter = document.getElementById('reasonCharCounter');
    const form = document.getElementById('leaveApplicationForm');
    const submitBtn = document.getElementById('submitLeaveBtn');
    const halfDayContainer = document.getElementById('halfDaySessionContainer');
    const policyStatus = document.getElementById('policyAttendanceStatus');

    const dayTypeRadios = document.querySelectorAll('input[name="day_type"]');
    const sessionRadios = document.querySelectorAll('input[name="half_day_type"]');

    function isHalfDaySelected() {
        const checked = document.querySelector('input[name="day_type"]:checked');
        return checked && checked.value === 'half_day';
    }

    function getSelectedSession() {
        const checked = document.querySelector('input[name="half_day_type"]:checked');
        return checked ? checked.value : 'morning';
    }

    function calculateDuration() {
        if (!fromInput || !badge) return;

        if (isHalfDaySelected()) {
            const session = getSelectedSession();
            const sessionTitle = session === 'afternoon' ? 'Afternoon' : 'Morning';
            badge.textContent = '0.5 Day (' + sessionTitle + ')';
            badge.className = 'badge bg-info fs-14 px-3 py-2 rounded-pill';
            if (policyStatus) {
                policyStatus.textContent = '"Half Day"';
            }
            return;
        }

        if (policyStatus) {
            policyStatus.textContent = '"On Leave"';
        }

        if (!toInput) return;
        const fromVal = fromInput.value;
        const toVal = toInput.value;

        if (!fromVal || !toVal) {
            badge.textContent = '—';
            badge.className = 'badge bg-secondary fs-14 px-3 py-2 rounded-pill';
            return;
        }

        const d1 = new Date(fromVal);
        const d2 = new Date(toVal);

        if (d2 < d1) {
            badge.textContent = 'Invalid Range';
            badge.className = 'badge bg-danger fs-14 px-3 py-2 rounded-pill';
            return;
        }

        const diffTime = Math.abs(d2 - d1);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;

        badge.textContent = diffDays + (diffDays === 1 ? ' Day' : ' Days');
        badge.className = 'badge bg-primary fs-14 px-3 py-2 rounded-pill';
    }

    function toggleDayType() {
        const isHalf = isHalfDaySelected();
        if (isHalf) {
            if (halfDayContainer) halfDayContainer.style.display = '';
            if (toCol) toCol.style.display = 'none';
            if (fromLabel) fromLabel.innerHTML = 'Leave Date <span class="text-danger">*</span>';
            if (fromInput && toInput) {
                toInput.value = fromInput.value;
            }
        } else {
            if (halfDayContainer) halfDayContainer.style.display = 'none';
            if (toCol) toCol.style.display = '';
            if (fromLabel) fromLabel.innerHTML = 'From Date <span class="text-danger">*</span>';
            if (fromInput && toInput && (!toInput.value || toInput.value < fromInput.value)) {
                toInput.value = fromInput.value;
            }
        }
        calculateDuration();
    }

    dayTypeRadios.forEach(function (radio) {
        radio.addEventListener('change', toggleDayType);
    });

    sessionRadios.forEach(function (radio) {
        radio.addEventListener('change', calculateDuration);
    });

    if (fromInput) {
        fromInput.addEventListener('change', function () {
            if (isHalfDaySelected() && toInput) {
                toInput.value = fromInput.value;
            } else if (toInput) {
                if (toInput.value && toInput.value < fromInput.value) {
                    toInput.value = fromInput.value;
                }
                toInput.min = fromInput.value;
            }
            calculateDuration();
        });
    }

    if (toInput) {
        toInput.addEventListener('change', calculateDuration);
    }

    if (reasonInput && charCounter) {
        function updateCounter() {
            const len = reasonInput.value.length;
            charCounter.textContent = len + ' / 3000 chars';
        }
        reasonInput.addEventListener('input', updateCounter);
        updateCounter();
    }

    if (form && submitBtn) {
        form.addEventListener('submit', function () {
            if (isHalfDaySelected() && fromInput && toInput) {
                toInput.value = fromInput.value;
            }
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="ri-loader-4-line ri-spin me-1"></i> Submitting...';
        });
    }

    toggleDayType();
});
</script>
@endsection
