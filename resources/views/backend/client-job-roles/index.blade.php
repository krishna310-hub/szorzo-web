@extends('backend.layouts.master')

@section('title', 'Client Job Roles')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <h5 class="card-title mb-0 flex-grow-1">Client Job Roles</h5>
                            @can('create', \App\Models\ClientJobRole::class)
                                <a href="{{ route('admin.client-job-roles.create') }}" class="btn btn-sm btn-primary">Add Client Job Role</a>
                            @endcan
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="client-job-roles-table" class="table table-bordered dt-responsive nowrap w-100">
                                    <thead>
                                        <tr>
                                            <th>S.No</th>
                                            <th>Client</th>
                                            <th>Job Role</th>
                                            <th>PoC Name</th>
                                            <th>Contact Number</th>
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
    $(document).ready(function () {
        var table = $('#client-job-roles-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: { url: '{{ route('admin.client-job-roles.index') }}', type: 'GET' },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'client_name', name: 'client_name', orderable: false, searchable: false },
                { data: 'job_role_name', name: 'job_role_name', orderable: false, searchable: false },
                // { data: 'job_description', name: 'job_description' },
                { data: 'poc_name', name: 'poc_name', orderable: false, searchable: false },
                { data: 'contact_number', name: 'contact_number', orderable: false, searchable: false },
                { data: 'status', name: 'status', orderable: false, searchable: false },
                { data: 'created_at', name: 'created_at' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });

        $(document).on('click', '.delete-record', function () {
            if (!confirm('Are you sure you want to delete this client job role?')) return;
            $.ajax({
                url: $(this).data('route'),
                type: 'DELETE',
                success: function (res) {
                    res.status ? toastr.success(res.message) : toastr.error(res.message);
                    table.ajax.reload(null, false);
                }
            });
        });
    });
</script>
@endsection
