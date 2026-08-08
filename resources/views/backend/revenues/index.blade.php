@extends('backend.layouts.master')
@section('title', 'Revenue')
@section('content')
<div class="main-content"><div class="page-content"><div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex align-items-center">
            <h5 class="card-title mb-0 flex-grow-1">Revenue Invoices</h5>
            @can('create', \App\Models\Revenue::class)
                <a href="{{ route('admin.revenues.create') }}" class="btn btn-sm btn-primary">Generate Invoice</a>
            @endcan
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="revenues" class="table table-bordered nowrap w-100">
                    <thead><tr><th>S.No</th><th>Invoice No.</th><th>Date</th><th>Candidate</th><th>Client</th><th>Revenue</th><th>Total</th><th>Action</th></tr></thead>
                </table>
            </div>
        </div>
    </div>
</div></div></div>
@endsection
@section('script')
<script>
$(function () {
    $('#revenues').DataTable({
        processing: true, serverSide: true, scrollX: true,
        ajax: '{{ route('admin.revenues.index') }}',
        columns: [
            {data:'DT_RowIndex', orderable:false, searchable:false},
            {data:'invoice_number'}, {data:'invoice_date'}, {data:'candidate_name', name:'candidate.candidate_name', orderable:false},
            {data:'client_name'}, {data:'service_amount'}, {data:'total_amount'},
            {data:'action', orderable:false, searchable:false}
        ]
    });
});
</script>
@endsection
