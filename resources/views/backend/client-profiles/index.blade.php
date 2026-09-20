@extends('backend.layouts.master')
@section('title', 'Client Profiles')
@section('content')
<div class="main-content"><div class="page-content"><div class="container-fluid"><div class="row"><div class="col-lg-12"><div class="card">
    <div class="card-header d-flex align-items-center">
        <h5 class="card-title mb-0 flex-grow-1">Client Profiles</h5>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.client-profiles.export') }}" class="btn btn-sm btn-info">Export</a>
            <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#importModal">Import</button>
            <a href="{{ route('admin.client-profiles.create') }}" class="btn btn-sm btn-primary">Add New</a>
        </div>
    </div>
    <div class="card-body">
        @include('backend.partials.import-feedback')
        <div class="table-responsive">
        <table id="datatable" class="table table-bordered nowrap w-100"><thead><tr>
            <th>S.No</th><th>Account Name</th><th>Account Owner</th><th>Industry</th><th>Status</th><th>Action</th>
        </tr></thead><tbody></tbody></table>
    </div></div>
</div></div></div></div></div></div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Import Client Profiles</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.client-profiles.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Excel File</label>
                        <input type="file" name="import_file" class="form-control" required>
                    </div>
                    <a href="{{ route('admin.client-profiles.import-template') }}">Download Template</a>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Import</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@section('script')
<script>
$(document).ready(function () {
    var table = $('#datatable').DataTable({
        processing: true, serverSide: true, scrollX: true,
        ajax: { url: '{{ route('admin.client-profiles.index') }}', type: 'GET' },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'account_name', name: 'account_name' },
            { data: 'account_owner', name: 'account_owner' },
            { data: 'industry', name: 'industry' },
            { data: 'status', name: 'status', orderable: false, searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ]
    });
    $(document).on('click', '.delete-record', function () {
        if (!confirm('Are you sure you want to delete this record?')) return;
        $.ajax({ url: $(this).data('route'), type: 'DELETE', data: { _token: '{{ csrf_token() }}' }, success: function (res) {
            res.status ? toastr.success(res.message) : toastr.error(res.message); table.ajax.reload(null, false);
        }});
    });
    $(document).on('click', '.convert-client', function () {
        if (!confirm('Are you sure you want to convert this profile to a Main Client?')) return;
        $.ajax({ url: $(this).data('route'), type: 'POST', data: { _token: '{{ csrf_token() }}' }, success: function (res) {
            res.status ? toastr.success(res.message) : toastr.error(res.message); table.ajax.reload(null, false);
        }});
    });
});
</script>
@endsection