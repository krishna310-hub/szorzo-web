@extends('backend.layouts.master')

@section('title', 'Edit Employee')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">Edit Employee</h4>
                            <div class="d-flex align-items-center gap-2">
                                <a href="{{ route('admin.payslip.index', ['employee_id' => $employee->id]) }}" class="btn btn-sm btn-outline-success">
                                    <i class="bx bx-receipt me-1"></i>Payslip
                                </a>
                                <a href="{{ route('admin.employees.index') }}" class="btn btn-sm btn-light">Back</a>
                            </div>
                        </div>
                        <div class="card-body">
                            @php
                                $completion = $employee->profile_completion;
                                $percent = $completion['percentage'];
                                $color = $employee->progress_color;
                                $badgeClass = $percent >= 80 ? 'bg-success text-white' : ($percent >= 40 ? 'bg-warning text-dark' : 'bg-danger text-white');
                            @endphp

                            <!-- Profile Completion Highlight Banner -->
                            <div class="card border mb-4 bg-light-subtle shadow-none">
                                <div class="card-body p-3">
                                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                                        <div class="d-flex align-items-center gap-3">
                                            <!-- Profile Image with Circular Progress Ring -->
                                            <div class="avatar-progress-container avatar-lg-ring" title="Profile Completion: {{ $percent }}%">
                                                <div class="avatar-circle-ring" style="background: conic-gradient({{ $color }} 0% {{ $percent }}%, #e9ebec {{ $percent }}% 100%); width: 76px; height: 76px; padding: 4px;">
                                                    <img src="{{ $employee->avatar_url }}" alt="{{ $employee->employee_name }}" class="avatar-circle-img" style="width: 100%; height: 100%; object-fit: cover;">
                                                </div>
                                                <span class="avatar-percent-pill {{ $badgeClass }}" style="bottom: -2px; right: -2px; font-size: 11px;">
                                                    {{ $percent }}%
                                                </span>
                                            </div>

                                            <div>
                                                <h5 class="mb-1 text-dark fw-bold">{{ $employee->employee_name }}</h5>
                                                <p class="text-muted mb-1 small">
                                                    <span class="me-2"><i class="bx bx-id-card me-1"></i>ID: <strong>{{ $employee->employee_no ?? 'N/A' }}</strong></span>
                                                    @if($employee->designation)
                                                        <span class="me-2"><i class="bx bx-briefcase me-1"></i>{{ $employee->designation }}</span>
                                                    @endif
                                                    <span class="badge bg-primary-subtle text-primary">{{ $employee->employment_mode }}</span>
                                                </p>
                                                <div class="d-flex flex-wrap align-items-center gap-2 mt-1">
                                                    <span class="badge {{ $badgeClass }} px-2 py-1 fs-6">
                                                        <i class="bx bx-check-double me-1"></i>Complete your profile: {{ $completion['ratio_text'] }} ({{ $percent }}%)
                                                    </span>
                                                    <span class="badge bg-secondary-subtle text-secondary">
                                                        {{ $completion['verified_count'] }} of {{ $completion['total_count'] }} Verified
                                                    </span>
                                                    <span class="badge bg-info-subtle text-info">
                                                        {{ $completion['uploaded_count'] }} Documents Uploaded
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="text-end">
                                            <button type="button" class="btn btn-sm btn-outline-success verify-all-form-btn mb-1" title="Check all uploaded document verification switches">
                                                <i class="bx bx-check-double me-1"></i>Verify All Uploaded
                                            </button>
                                            <div class="small text-muted">Use the switches in the Document section below.</div>
                                        </div>
                                    </div>

                                    <!-- Progress Bar -->
                                    <div class="progress mt-3" style="height: 8px;">
                                        <div class="progress-bar" role="progressbar" style="width: {{ $percent }}%; background-color: {{ $color }};" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>

                            <form action="{{ route('admin.employees.update', $employee->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                @include('backend.employees.form')
                            </form>
                        </div>
                    </div>
                </div>
            </div>
    </div>
</div>
@endsection

@push('style')
<style>
.avatar-progress-container {
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.avatar-circle-ring {
    position: relative;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 1px 4px rgba(0,0,0,0.1);
    transition: transform 0.2s ease;
}
.avatar-circle-ring:hover {
    transform: scale(1.05);
}
.avatar-circle-img {
    border-radius: 50%;
    object-fit: cover;
    background: #ffffff;
    border: 2px solid #ffffff;
}
.avatar-percent-pill {
    position: absolute;
    font-weight: 700;
    line-height: 1;
    padding: 2px 5px;
    border-radius: 10px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.25);
    border: 1.5px solid #ffffff;
}
</style>
@endpush

@section('script')
<script>
    $(document).ready(function() {
        $('.verify-all-form-btn').on('click', function() {
            $('.repeatable-document').each(function() {
                var block = $(this);
                if (block.find('.existing-document').length > 0) {
                    block.find('.form-check-input').prop('checked', true);
                }
            });
            if ($('input[name="tenth_marksheet"]').closest('div').find('a').length > 0 ||
                $('input[name="twelfth_marksheet"]').closest('div').find('a').length > 0 ||
                $('input[name="degree_certificate"]').closest('div').find('a').length > 0) {
                $('#form_chk_edu').prop('checked', true);
            }
            if ($('input[name="pan_card_file"]').closest('div').find('a').length > 0) {
                $('#form_chk_pan').prop('checked', true);
            }
            if ($('input[name="aadhaar_file"]').closest('div').find('a').length > 0) {
                $('#form_chk_aadhaar').prop('checked', true);
            }
            if ($('#form_chk_photo').length > 0) {
                $('#form_chk_photo').prop('checked', true);
            }
            if ($('#form_chk_personal').length > 0) {
                $('#form_chk_personal').prop('checked', true);
            }
            toastr.info('Uploaded checklist items marked as verified. Click "Update" to save.');
        });
    });
</script>
@endsection
