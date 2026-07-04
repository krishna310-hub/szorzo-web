@extends('backend.layouts.master')
@section('title', 'Clients')
@section('content')
<div class="main-content"><div class="page-content"><div class="container-fluid"><div class="row"><div class="col-lg-12"><div class="card">
    <div class="card-header d-flex align-items-center">
        <h5 class="card-title mb-0 flex-grow-1">Clients</h5>
        <div class="d-flex flex-wrap gap-2">
            @include('backend.partials.master-import-export', [
                'routePrefix' => 'clients',
                'moduleName' => 'Clients',
                'model' => \App\Models\Client::class,
                'fields' => ['Record ID', 'Client', 'Billing', 'Location', 'PoC Name', 'Signed Date', 'Renewal Date', 'Division', 'Contact Number', 'Email', 'Mobile Number', 'Status'],
            ])
            @can('create', \App\Models\Client::class)<a href="{{ route('admin.clients.create') }}" class="btn btn-sm btn-primary">Add New Client</a>@endcan
        </div>
    </div>
    <div class="card-body">
        @include('backend.partials.import-feedback')
        <div class="table-responsive">
        <table id="clients-table" class="table table-bordered dt-responsive nowrap w-100"><thead><tr>
            <th>S.No</th><th>Client</th><th>Billing</th><th>Location</th><th>PoC Name</th><th>Signed Date</th><th>Renewal Date</th><th>Division</th><th>Contact Number</th><th>Email</th><th>Status</th><th>Created At</th><th>Action</th>
        </tr></thead><tbody></tbody></table>
    </div></div>
</div></div></div></div></div></div>
@endsection
@section('script')
<script>
$(document).ready(function () {
    var table = $('#clients-table').DataTable({
        processing: true, serverSide: true, scrollX: true,
        ajax: { url: '{{ route('admin.clients.index') }}', type: 'GET' },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'client', name: 'client' },
            { data: 'billing_value', name: 'billing_value', orderable: false, searchable: false  },
            { data: 'location_name', name: 'location_name', orderable: false, searchable: false },
            { data: 'poc_name', name: 'poc_name' }, { data: 'signed_date', name: 'signed_date' },
            { data: 'renewal_date', name: 'renewal_date' },
            { data: 'division_name', name: 'division_name', orderable: false, searchable: false },
            { data: 'contact_number', name: 'contact_number' }, { data: 'email', name: 'email' },
            { data: 'status', name: 'status', orderable: false, searchable: false },
            { data: 'created_at', name: 'created_at' }, { data: 'action', name: 'action', orderable: false, searchable: false }
        ]
    });
    $(document).on('click', '.delete-record', function () {
        if (!confirm('Are you sure you want to delete this client?')) return;
        $.ajax({ url: $(this).data('route'), type: 'DELETE', success: function (res) {
            res.status ? toastr.success(res.message) : toastr.error(res.message); table.ajax.reload(null, false);
        }});
    });
});
</script>
@endsection
