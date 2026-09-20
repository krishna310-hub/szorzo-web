@extends('backend.layouts.master')
@section('title', 'Business Intelligences')
@section('content')
<div class="main-content"><div class="page-content"><div class="container-fluid"><div class="row"><div class="col-lg-12"><div class="card">
    <div class="card-header d-flex align-items-center">
        <h5 class="card-title mb-0 flex-grow-1">Business Intelligences</h5>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.business-intelligences.export') }}" class="btn btn-sm btn-info">Export</a>
            <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#importModal">Import</button>
            <a href="{{ route('admin.business-intelligences.create') }}" class="btn btn-sm btn-primary">Add New</a>
        </div>
    </div>
    <div class="card-body">
        @include('backend.partials.import-feedback')
        <div class="table-responsive">
        <table id="datatable" class="table table-bordered nowrap w-100"><thead><tr>
            <th>S.No</th><th>Metric Name</th><th>Category</th><th>Status</th><th>Action</th>
        </tr></thead><tbody></tbody></table>
    </div></div>
</div></div></div></div></div></div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Import Business Intelligences</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.business-intelligences.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Excel File</label>
                        <input type="file" name="import_file" class="form-control" required>
                    </div>
                    <a href="{{ route('admin.business-intelligences.import-template') }}">Download Template</a>
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
        ajax: { url: '{{ route('admin.business-intelligences.index') }}', type: 'GET' },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'metric_name', name: 'metric_name' },
            { data: 'category', name: 'category' },
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
});
</script>
@endsection