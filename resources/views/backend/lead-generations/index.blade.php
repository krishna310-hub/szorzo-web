@extends('backend.layouts.master')
@section('title', 'Lead Generations')
@section('content')
<div class="main-content"><div class="page-content"><div class="container-fluid"><div class="row"><div class="col-lg-12"><div class="card">
    <div class="card-header d-flex align-items-center">
        <h5 class="card-title mb-0 flex-grow-1">Lead Generations</h5>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.lead-generations.export') }}" class="btn btn-sm btn-info">Export</a>
            <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#importModal">Import</button>
            <a href="{{ route('admin.sales-follow-ups.index') }}" class="btn btn-sm btn-warning">Today's Follow-ups</a>
            <a href="{{ route('admin.lead-generations.create') }}" class="btn btn-sm btn-primary">Add New</a>
        </div>
    </div>
    <div class="card-body">
        @include('backend.partials.import-feedback')
        <div class="table-responsive">
        <table id="datatable" class="table table-bordered nowrap w-100"><thead><tr>
            <th>S.No</th><th>Company</th><th>Contact</th><th>Mobile</th><th>Assigned To</th><th>Priority</th><th>Stage</th><th>Next Follow-up</th><th>Status</th><th>Action</th>
        </tr></thead><tbody></tbody></table>
    </div></div>
</div></div></div></div></div></div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Import Lead Generations</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.lead-generations.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Excel File</label>
                        <input type="file" name="import_file" class="form-control" required>
                    </div>
                    <a href="{{ route('admin.lead-generations.import-template') }}">Download Template</a>
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
        ajax: { url: '{{ route('admin.lead-generations.index') }}', type: 'GET' },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'account_name', name: 'account_name' },
            { data: 'contact_person', name: 'contact_person', defaultContent: '-' },
            { data: 'mobile', name: 'mobile', defaultContent: '-' },
            { data: 'assignee_name', name: 'assignee_name', orderable: false },
            { data: 'priority', name: 'priority' },
            { data: 'pipeline_stage', name: 'pipeline_stage' },
            { data: 'next_follow_up_at', name: 'next_follow_up_at', defaultContent: '-' },
            { data: 'status', name: 'status', orderable: false, searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ]
    });
    $(document).on('click', '.convert-lead', function () {
        if (!confirm('Create a Client Profile from this signed or won lead?')) return;
        $.ajax({ url: $(this).data('route'), type: 'POST', data: { _token: '{{ csrf_token() }}' }, success: function (res) {
            res.status ? toastr.success(res.message) : toastr.error(res.message); table.ajax.reload(null, false);
        }, error: function (xhr) { toastr.error(xhr.responseJSON?.message || 'Unable to convert lead.'); } });
    });
    $(document).on('click', '.delete-record', function () {
        if (!confirm('Are you sure you want to delete this record?')) return;
        $.ajax({ url: $(this).data('route'), type: 'DELETE', data: { _token: '{{ csrf_token() }}' }, success: function (res) {
            res.status ? toastr.success(res.message) : toastr.error(res.message); table.ajax.reload(null, false);
        }});
    });
});
</script>
@endsection