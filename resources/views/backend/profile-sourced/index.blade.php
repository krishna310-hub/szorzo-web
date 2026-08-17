@extends('backend.layouts.master')

@section('title', 'Profile Sourced')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5 class="card-title mb-0 flex-grow-1">Profile Sourced</h5>
                    <a href="{{ route('admin.profile-sourced.export') }}" id="profile-sourced-export" class="btn btn-sm btn-success me-2"><i class="ri-file-excel-2-line me-1"></i>Export</a>
                    <button class="btn btn-sm btn-outline-primary me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#profileSourcedFilters"><i class="ri-filter-3-line me-1"></i>Filter</button>
                    @can('create', \App\Models\ProfileSourced::class)
                        <a href="{{ route('admin.profile-sourced.create') }}" class="btn btn-sm btn-primary">Add Profile</a>
                    @endcan
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="profile-sourced-table" class="table table-bordered nowrap w-100">
                            <thead><tr>
                                <th>S.No</th><th>Candidate Name</th><th>Job Role</th><th>Need</th><th>CV</th><th>Recruiter Name</th>
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
<div class="offcanvas offcanvas-end" tabindex="-1" id="profileSourcedFilters">
    <div class="offcanvas-header border-bottom"><h5 class="offcanvas-title">Profile Sourced Filters</h5><button class="btn-close" data-bs-dismiss="offcanvas"></button></div>
    <form id="profile-sourced-filter-form" class="offcanvas-body d-flex flex-column gap-3">
        <div><label class="form-label">From Date</label><input type="date" class="form-control" name="from_date"></div>
        <div><label class="form-label">To Date</label><input type="date" class="form-control" name="to_date"></div>
        <div><label class="form-label">Recruiter</label><select class="form-select" name="recruiter_id"><option value="">All recruiters</option>@foreach($recruiters as $item)<option value="{{ $item->id }}">{{ $item->recruiter_name }}</option>@endforeach</select></div>
        <div class="mt-auto d-flex gap-2 border-top pt-3"><button type="button" id="profile-filter-reset" class="btn btn-light w-50">Reset</button><button class="btn btn-primary w-50">Apply</button></div>
    </form>
</div>
@endsection

@section('script')
<script>
$(function () {
    var filters = {};
    var exportBaseUrl = @json(route('admin.profile-sourced.export'));
    var table = $('#profile-sourced-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: { url: '{{ route('admin.profile-sourced.index') }}', data: function (data) { Object.assign(data, filters); } },
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'candidate_name', name: 'candidate_name' },
            { data: 'job_role_name', name: 'jobRole.job_role', orderable: false },
            { data: 'need', name: 'need' },
            { data: 'cv', orderable: false, searchable: false },
            { data: 'recruiter_name', name: 'recruiter.recruiter_name' },
            { data: 'mobile_number', name: 'mobile_number' },
            { data: 'email', name: 'email' },
            { data: 'created_at', name: 'created_at' },
            { data: 'action', orderable: false, searchable: false }
        ]
    });

    function updateExport() {
        var url = new URL(exportBaseUrl, window.location.origin);
        Object.keys(filters).forEach(function (key) { url.searchParams.set(key, filters[key]); });
        $('#profile-sourced-export').attr('href', url.toString());
    }
    $('#profile-sourced-filter-form').on('submit', function (event) {
        event.preventDefault(); filters = {};
        $(this).serializeArray().forEach(function (item) { if (item.value) filters[item.name] = item.value; });
        updateExport(); table.ajax.reload(); bootstrap.Offcanvas.getInstance(document.getElementById('profileSourcedFilters'))?.hide();
    });
    $('#profile-filter-reset').on('click', function () { $('#profile-sourced-filter-form')[0].reset(); filters = {}; updateExport(); table.ajax.reload(); });

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
