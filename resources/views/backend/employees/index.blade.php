@extends('backend.layouts.master')
@section('title', 'Employees')
@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header d-flex align-items-center">
                                <h5 class="card-title mb-0 flex-grow-1">Employees</h5>
                                @php($employeeAccess = str_replace('_', '-', strtolower((string) auth()->user()->role?->access_level)))
                                @can('create', \App\Models\Employee::class)
                                <div class="d-flex flex-wrap gap-2">
                                    @if(in_array($employeeAccess, ['super-admin', 'delivery-lead', 'recruiter-dl'], true))
                                    <form method="POST" action="{{ route('admin.employees.generate-link') }}">@csrf
                                        <button class="btn btn-sm btn-outline-primary"><i class="ri-link me-1"></i>Generate Link</button>
                                    </form>
                                    @endif
                                    <a href="{{ route('admin.employees.create') }}" class="btn btn-sm btn-primary">Add New
                                        Employee</a>
                                </div>
                                @endcan
                            </div>
                            <div class="card-body">
                                @if(session('onboarding_link'))
                                    <div class="alert alert-success">
                                        <label class="form-label fw-semibold">Unique employee onboarding link</label>
                                        <div class="input-group"><input id="generated-onboarding-link" class="form-control" readonly value="{{ session('onboarding_link') }}"><button type="button" id="copy-onboarding-link" class="btn btn-success">Copy</button></div>
                                        <small>This one-time link works without login.</small>
                                    </div>
                                @endif
                                <div class="table-responsive">
                                    <table id="employees-table" class="table table-bordered dt-responsive nowrap w-100">
                                        <thead>
                                            <tr>
                                                <th>S.No</th>
                                                <th>Employee ID</th>
                                                <th>Employee</th>
                                                <th>Designation</th>
                                                <th>Date of Joining</th>
                                                <th>Status</th>
                                                <th>Created At</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Document Checklist & Verification Modal -->
    <div class="modal fade" id="employeeChecklistModal" tabindex="-1" aria-labelledby="employeeChecklistModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down">
            <form id="checklist-verify-form" class="modal-content shadow-lg border-0 position-relative" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header bg-light py-3 border-bottom sticky-top z-3">
                    <h5 class="modal-title d-flex align-items-center gap-2" id="employeeChecklistModalLabel">
                        <i class="bx bx-check-shield text-primary fs-4"></i>
                        <span class="fw-semibold">Employee Document Checklist &amp; Verification</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3 p-md-4" id="checklist-modal-content">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted">Loading checklist &amp; documents...</p>
                    </div>
                </div>
                <!-- Floating Scroll-Up Button -->
                <button type="button" id="checklistScrollTopBtn" class="btn btn-primary rounded-circle shadow-lg"
                        title="Scroll to top" aria-label="Scroll to top">
                    <i class="bx bx-up-arrow-alt fs-4"></i>
                </button>
                <div class="modal-footer bg-light py-2 border-top sticky-bottom z-3">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="save-checklist-btn">
                        <i class="bx bx-save me-1"></i>Save Verification
                    </button>
                </div>
            </form>
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
    width: 46px;
    height: 46px;
    flex-shrink: 0;
}
.avatar-circle-ring {
    position: relative;
    width: 100%;
    height: 100%;
    border-radius: 50%;
    padding: 3px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.avatar-circle-ring:hover {
    transform: scale(1.06);
    box-shadow: 0 2px 6px rgba(0,0,0,0.15);
}
.avatar-circle-img {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
    background: #ffffff;
    border: 1.5px solid #ffffff;
}
.avatar-percent-pill {
    position: absolute;
    bottom: -3px;
    right: -3px;
    font-size: 9px;
    font-weight: 700;
    line-height: 1;
    padding: 2px 4px;
    border-radius: 8px;
    background: #212529;
    color: #ffffff;
    border: 1px solid #ffffff;
    box-shadow: 0 1px 2px rgba(0,0,0,0.2);
}
.avatar-lg-ring {
    width: 72px;
    height: 72px;
}
.avatar-lg-ring .avatar-circle-ring {
    padding: 4px;
}
.table-success-subtle {
    background-color: rgba(10, 179, 156, 0.08) !important;
}
.table-warning-subtle {
    background-color: rgba(247, 184, 75, 0.08) !important;
}

/* Modal Scroll & Responsive Card Enhancements */
#employeeChecklistModal .modal-dialog-scrollable {
    max-height: calc(100% - 2.5rem);
}
#employeeChecklistModal .modal-dialog-scrollable .modal-content {
    max-height: 88vh;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    border-radius: 12px;
}
#checklist-modal-content {
    overflow-y: auto !important;
    max-height: calc(88vh - 130px);
    -webkit-overflow-scrolling: touch;
    scroll-behavior: smooth;
    position: relative;
}
/* Sleek custom scrollbar */
#checklist-modal-content::-webkit-scrollbar {
    width: 6px;
}
#checklist-modal-content::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 4px;
}
#checklist-modal-content::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}
#checklist-modal-content::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Floating Scroll to Top button */
#checklistScrollTopBtn {
    position: absolute;
    bottom: 68px;
    right: 24px;
    width: 42px;
    height: 42px;
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 1060;
    border-radius: 50%;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.22);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    border: 2px solid #ffffff;
    cursor: pointer;
}
#checklistScrollTopBtn:hover {
    transform: translateY(-3px) scale(1.05);
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.3);
}

/* Mobile Responsive Cards for Checklist */
@media (max-width: 767.98px) {
    #employeeChecklistModal .modal-dialog-scrollable .modal-content {
        max-height: 100vh;
        border-radius: 0;
    }
    #checklist-modal-content {
        max-height: calc(100vh - 125px) !important;
        padding: 0.75rem !important;
    }
    .checklist-table,
    .checklist-table tbody,
    .checklist-table tr,
    .checklist-table td {
        display: block !important;
        width: 100% !important;
    }
    .checklist-table thead {
        display: none !important;
    }
    .checklist-row {
        background: #ffffff;
        border: 1px solid #e2e8f0 !important;
        border-radius: 10px !important;
        margin-bottom: 14px !important;
        padding: 12px 14px !important;
        box-shadow: 0 2px 5px rgba(0,0,0,0.04) !important;
        transition: all 0.2s ease;
    }
    .checklist-row.table-success-subtle {
        background-color: #f0fdf4 !important;
        border-color: #bbf7d0 !important;
    }
    .checklist-row.table-warning-subtle {
        background-color: #fffbeb !important;
        border-color: #fde68a !important;
    }
    .checklist-row td {
        padding: 6px 0 !important;
        border: none !important;
    }
    .checklist-mobile-label {
        display: flex !important;
        align-items: center;
        gap: 4px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748b;
        margin-bottom: 5px;
    }
    .checklist-card-section {
        padding-top: 8px;
        margin-top: 8px;
        border-top: 1px dashed #e2e8f0;
    }
    #checklistScrollTopBtn {
        bottom: 75px;
        right: 18px;
        width: 38px;
        height: 38px;
    }
}
@media (min-width: 768px) {
    .checklist-mobile-label {
        display: none !important;
    }
}
</style>
@endpush

@section('script')
    <script>
        $(document).ready(function() {
            var table = $('#employees-table').DataTable({
                processing: true,
                serverSide: true,
                scrollX: true,
                ajax: {
                    url: '{{ route('admin.employees.index') }}',
                    type: 'GET'
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'employee_no',
                        name: 'employee_no'
                    },
                    {
                        data: 'employee_name',
                        name: 'employee_name'
                    },
                    {
                        data: 'designation',
                        name: 'designation'
                    },
                    {
                        data: 'date_of_joining',
                        name: 'date_of_joining'
                    },
                    {
                        data: 'status',
                        name: 'status',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    }, {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            // Checklist modal trigger
            $(document).on('click', '.open-checklist-modal', function() {
                var empId = $(this).data('id');
                var empName = $(this).data('name') || 'Employee';
                var modal = $('#employeeChecklistModal');
                var form = $('#checklist-verify-form');
                var content = $('#checklist-modal-content');
                var scrollBtn = $('#checklistScrollTopBtn');

                scrollBtn.hide();
                form.attr('action', '{{ url('admin/masters/employees') }}/' + empId + '/verify-checklist');
                content.scrollTop(0);
                content.html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2 text-muted">Loading checklist &amp; documents for ' + empName + '...</p></div>');
                modal.modal('show');

                $.ajax({
                    url: '{{ url('admin/masters/employees') }}/' + empId + '/checklist',
                    type: 'GET',
                    success: function(res) {
                        if (res.status && res.html) {
                            content.html(res.html);
                            content.scrollTop(0);
                        } else {
                            content.html('<div class="alert alert-danger">Could not load checklist data.</div>');
                        }
                    },
                    error: function(xhr) {
                        content.html('<div class="alert alert-danger">Failed to load checklist: ' + (xhr.responseJSON?.message || 'Server error') + '</div>');
                    }
                });
            });

            // Modal scroll listener for floating scroll-up button
            $('#checklist-modal-content').on('scroll', function() {
                if ($(this).scrollTop() > 100) {
                    $('#checklistScrollTopBtn').fadeIn(200).css('display', 'flex');
                } else {
                    $('#checklistScrollTopBtn').fadeOut(200);
                }
            });

            // Scroll to top click
            $(document).on('click', '#checklistScrollTopBtn', function(e) {
                e.preventDefault();
                $('#checklist-modal-content').animate({ scrollTop: 0 }, 300);
            });

            $('#employeeChecklistModal').on('hidden.bs.modal', function() {
                $('#checklistScrollTopBtn').hide();
            });

            // Checklist checkbox toggle in modal
            $(document).on('change', '.checklist-checkbox', function() {
                var isChecked = $(this).is(':checked');
                var label = $(this).siblings('.checklist-label');
                var row = $(this).closest('.checklist-row');
                var mobileStatusBadge = row.find('.checklist-mobile-status');

                if (isChecked) {
                    label.text('Verified').removeClass('text-muted').addClass('text-success');
                    row.removeClass('table-warning-subtle').addClass('table-success-subtle');
                    if (mobileStatusBadge.length) {
                        mobileStatusBadge.html('<span class="badge bg-success-subtle text-success"><i class="bx bx-check me-1"></i>Verified</span>');
                    }
                } else {
                    label.text('Pending').removeClass('text-success').addClass('text-muted');
                    row.removeClass('table-success-subtle');
                    if (row.data('uploaded') === 1 || row.data('uploaded') === '1') {
                        row.addClass('table-warning-subtle');
                        if (mobileStatusBadge.length) {
                            mobileStatusBadge.html('<span class="badge bg-warning-subtle text-warning"><i class="bx bx-time-five me-1"></i>Uploaded</span>');
                        }
                    } else {
                        if (mobileStatusBadge.length) {
                            mobileStatusBadge.html('<span class="badge bg-light text-muted border">Pending</span>');
                        }
                    }
                }
            });

            // Verify all uploaded button
            $(document).on('click', '.verify-all-uploaded-btn', function() {
                $('#checklist-modal-content .checklist-row').each(function() {
                    var row = $(this);
                    if (row.data('uploaded') === 1 || row.data('uploaded') === '1') {
                        var checkbox = row.find('.checklist-checkbox');
                        if (!checkbox.is(':checked')) {
                            checkbox.prop('checked', true).trigger('change');
                        }
                    }
                });
            });

            // Auto-check verify checkbox when admin selects a file to upload
            $(document).on('change', '.checklist-file-upload', function() {
                if (this.files && this.files.length > 0) {
                    var row = $(this).closest('.checklist-row');
                    var checkbox = row.find('.checklist-checkbox');
                    if (!checkbox.is(':checked')) {
                        checkbox.prop('checked', true).trigger('change');
                    }
                }
            });

            // Submit checklist form
            $('#checklist-verify-form').on('submit', function(e) {
                e.preventDefault();
                var form = $(this);
                var saveBtn = $('#save-checklist-btn');
                saveBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving & Uploading...');

                var formData = new FormData(form[0]);

                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        if (res.status) {
                            toastr.success(res.message);
                            $('#employeeChecklistModal').modal('hide');
                            table.ajax.reload(null, false);
                        } else {
                            toastr.error(res.message || 'Failed to save verification.');
                        }
                    },
                    error: function(xhr) {
                        toastr.error(xhr.responseJSON?.message || 'Error occurred while saving verification.');
                    },
                    complete: function() {
                        saveBtn.prop('disabled', false).html('<i class="bx bx-save me-1"></i>Save Verification');
                    }
                });
            });

            $(document).on('click', '.delete-record', function() {
                if (!confirm('Are you sure you want to delete this employee?')) return;
                $.ajax({
                    url: $(this).data('route'),
                    type: 'DELETE',
                    success: function(res) {
                        res.status ? toastr.success(res.message) : toastr.error(res.message);
                        table.ajax.reload(null, false);
                    }
                });
            });
            $(document).on('click', '.activate-record', function() {
                var button = $(this); button.prop('disabled', true);
                $.post(button.data('route')).done(function(res) { toastr.success(res.message); table.ajax.reload(null, false); })
                    .fail(function(xhr) { toastr.error(xhr.responseJSON?.message || 'Employee could not be activated.'); })
                    .always(function() { button.prop('disabled', false); });
            });
            $('#copy-onboarding-link').on('click', function() {
                navigator.clipboard.writeText($('#generated-onboarding-link').val()).then(function() { toastr.success('Link copied.'); });
            });
        });
    </script>
@endsection
