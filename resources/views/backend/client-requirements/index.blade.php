@extends('backend.layouts.master')

@section('title', 'Client Requirements')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <h5 class="card-title mb-0 flex-grow-1">Client Requirements</h5>
                            @can('create', \App\Models\ClientRequirement::class)
                                <a href="{{ route('admin.client-requirements.create') }}" class="btn btn-sm btn-primary">Add Client Requirement</a>
                            @endcan
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="client-requirements-table" class="table table-bordered dt-responsive nowrap w-100">
                                    <thead>
                                        <tr>
                                            <th>S.No</th>
                                            <th>Client</th>
                                            <th>Billing %</th>
                                            <th>Job Description ID</th>
                                            <th>Mode</th>
                                            <th>Open Date</th>
                                            <th>Job Role</th>
                                            <th>CTC</th>
                                            <th>Location</th>
                                            <th>No.Of.Position</th>
                                            <th>Closure Target Date</th>
                                            <th>CV's Required</th>
                                            <th>CV's Uploaded</th>
                                            <th>Project Owner</th>
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
        var table = $('#client-requirements-table').DataTable({
            processing: true,
            serverSide: true,
            scrollX: true,
            ajax: { url: '{{ route('admin.client-requirements.index') }}', type: 'GET' },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'client_name', name: 'client_name', orderable: false, searchable: false },
                { data: 'billing_percentage', name: 'billing_percentage' },
                { data: 'job_description_id', name: 'job_description_id' },
                { data: 'mode_name', name: 'mode_name', orderable: false, searchable: false },
                { data: 'open_date', name: 'open_date' },
                { data: 'job_role_name', name: 'job_role_name', orderable: false, searchable: false },
                { data: 'ctc', name: 'ctc' },
                { data: 'location_name', name: 'location_name', orderable: false, searchable: false },
                { data: 'no_of_positions', name: 'no_of_positions' },
                { data: 'closure_target_date', name: 'closure_target_date' },
                { data: 'cvs_required', name: 'cvs_required' },
                { data: 'cvs_uploaded', name: 'cvs_uploaded' },
                { data: 'project_owner_name', name: 'project_owner_name', orderable: false, searchable: false },
                { data: 'status', name: 'status', orderable: false, searchable: false },
                { data: 'created_at', name: 'created_at' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });

        $(document).on('click', '.delete-record', function () {
            if (!confirm('Are you sure you want to delete this client requirement?')) return;
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
