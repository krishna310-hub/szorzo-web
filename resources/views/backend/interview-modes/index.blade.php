@extends('backend.layouts.master')

@section('title', 'Interview Modes')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <h5 class="card-title mb-0 flex-grow-1">Interview Modes</h5>
                                @can('create', \App\Models\InterviewMode::class)
                                    <a href="{{ route('admin.interview-modes.create') }}" class="btn btn-sm btn-primary">Add New Interview Mode</a>
                                @endcan
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="interview-modes-table" class="table table-bordered dt-responsive nowrap w-100">
                                    <thead>
                                        <tr>
                                            <th>S.No</th>
                                            <th>Interview Mode</th>
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
        var table = $('#interview-modes-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: { url: '{{ route('admin.interview-modes.index') }}', type: 'GET' },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'interview_mode', name: 'interview_mode' },
                { data: 'status', name: 'status', orderable: false, searchable: false },
                { data: 'created_at', name: 'created_at' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });

        $(document).on('click', '.delete-record', function () {
            if (!confirm('Are you sure you want to delete this interview mode?')) return;
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
