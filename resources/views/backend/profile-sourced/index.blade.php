@extends('backend.layouts.master')

@section('title', 'Profile Sourced')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5 class="card-title mb-0 flex-grow-1">Profile Sourced</h5>
                    @can('create', \App\Models\ProfileSourced::class)
                        <a href="{{ route('admin.profile-sourced.create') }}" class="btn btn-sm btn-primary">Add Profile</a>
                    @endcan
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="profile-sourced-table" class="table table-bordered nowrap w-100">
                            <thead><tr>
                                <th>S.No</th><th>Candidate Name</th><th>CV</th><th>Recruiter Name</th>
                                <th>Mobile Number</th><th>Email</th><th>Created At</th><th>Action</th>
                            </tr></thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
$(function () {
    var table = $('#profile-sourced-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('admin.profile-sourced.index') }}',
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'candidate_name', name: 'candidate_name' },
            { data: 'cv', orderable: false, searchable: false },
            { data: 'recruiter_name', name: 'recruiter.recruiter_name' },
            { data: 'mobile_number', name: 'mobile_number' },
            { data: 'email', name: 'email' },
            { data: 'created_at', name: 'created_at' },
            { data: 'action', orderable: false, searchable: false }
        ]
    });

    function requestAction(button, method, confirmation) {
        if (!window.confirm(confirmation)) return;
        button.prop('disabled', true);
        $.ajax({
            url: button.data('route'),
            type: method,
            success: function (response) {
                toastr.success(response.message);
                table.ajax.reload(null, false);
            },
            error: function (xhr) {
                var errors = xhr.responseJSON?.errors;
                var message = errors ? Object.values(errors).flat()[0] : (xhr.responseJSON?.message || 'The action could not be completed.');
                toastr.error(message);
            },
            complete: function () { button.prop('disabled', false); }
        });
    }

    $(document).on('click', '.move-to-candidate', function () {
        requestAction($(this), 'POST', 'Move this profile to Candidates?');
    });
    $(document).on('click', '.delete-record', function () {
        requestAction($(this), 'DELETE', 'Delete this sourced profile and its CV?');
    });
});
</script>
@endsection
