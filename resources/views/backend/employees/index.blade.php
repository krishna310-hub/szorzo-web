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
@endsection
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
