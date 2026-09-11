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
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-light py-3">
                    <h5 class="modal-title d-flex align-items-center gap-2" id="employeeChecklistModalLabel">
                        <i class="bx bx-check-shield text-primary fs-4"></i>
                        <span>Employee Document Checklist &amp; Verification</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="checklist-verify-form" method="POST">
                    @csrf
                    <div class="modal-body p-4" id="checklist-modal-content">
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2 text-muted">Loading checklist &amp; documents...</p>
                        </div>
                    </div>
                    <div class="modal-footer bg-light py-2">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="save-checklist-btn">
                            <i class="bx bx-save me-1"></i>Save Verification
                        </button>
                    </div>
                </form>
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
    background-color: rgba(10, 179, 156, 0.06) !important;
}
.table-warning-subtle {
    background-color: rgba(247, 184, 75, 0.06) !important;
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

                form.attr('action', '{{ url('admin/masters/employees') }}/' + empId + '/verify-checklist');
                content.html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2 text-muted">Loading checklist &amp; documents for ' + empName + '...</p></div>');
                modal.modal('show');

                $.ajax({
                    url: '{{ url('admin/masters/employees') }}/' + empId + '/checklist',
                    type: 'GET',
                    success: function(res) {
                        if (res.status && res.html) {
                            content.html(res.html);
                        } else {
                            content.html('<div class="alert alert-danger">Could not load checklist data.</div>');
                        }
                    },
                    error: function(xhr) {
                        content.html('<div class="alert alert-danger">Failed to load checklist: ' + (xhr.responseJSON?.message || 'Server error') + '</div>');
                    }
                });
            });

            // Checklist checkbox toggle in modal
            $(document).on('change', '.checklist-checkbox', function() {
                var isChecked = $(this).is(':checked');
                var label = $(this).siblings('.checklist-label');
                var row = $(this).closest('tr');

                if (isChecked) {
                    label.text('Verified').removeClass('text-muted').addClass('text-success');
                    row.removeClass('table-warning-subtle').addClass('table-success-subtle');
                } else {
                    label.text('Pending').removeClass('text-success').addClass('text-muted');
                    row.removeClass('table-success-subtle');
                    if (row.data('uploaded') === 1 || row.data('uploaded') === '1') {
                        row.addClass('table-warning-subtle');
                    }
                }
            });

            // Verify all uploaded button
            $(document).on('click', '.verify-all-uploaded-btn', function() {
                $('#checklist-modal-content tbody tr').each(function() {
                    var row = $(this);
                    if (row.data('uploaded') === 1 || row.data('uploaded') === '1') {
                        var checkbox = row.find('.checklist-checkbox');
                        if (!checkbox.is(':checked')) {
                            checkbox.prop('checked', true).trigger('change');
                        }
                    }
                });
            });

            // Submit checklist form
            $('#checklist-verify-form').on('submit', function(e) {
                e.preventDefault();
                var form = $(this);
                var saveBtn = $('#save-checklist-btn');
                saveBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');

                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: form.serialize(),
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
                    always: function() {
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
