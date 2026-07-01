@extends('backend.layouts.master')

@section('title', 'Recruiters')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <h5 class="card-title mb-0 flex-grow-1">Recruiters</h5>
                            @can('create', \App\Models\Recruiter::class)
                                <a href="{{ route('admin.recruiters.create') }}" class="btn btn-sm btn-primary">Add New Recruiter</a>
                            @endcan
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="recruiters-table" class="table table-bordered dt-responsive nowrap w-100">
                                    <thead>
                                        <tr>
                                            <th>S.No</th>
                                            <th>Recruiter Name</th>
                                            <th>Location</th>
                                            <th>Email</th>
                                            <th>Mobile Number</th>
                                            {{-- <th>Performance Rating</th> --}}
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
        var table = $('#recruiters-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: { url: '{{ route('admin.recruiters.index') }}', type: 'GET' },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'recruiter_name', name: 'recruiter_name' },
                { data: 'location', name: 'location' },
                { data: 'email', name: 'email' },
                { data: 'mobile_number', name: 'mobile_number' },
                // { data: 'performance_rating', name: 'performance_rating' },
                { data: 'status', name: 'status', orderable: false, searchable: false },
                { data: 'created_at', name: 'created_at' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });

        $(document).on('click', '.delete-record', function () {
            if (!confirm('Are you sure you want to delete this recruiter?')) return;
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
