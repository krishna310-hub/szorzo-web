@extends('backend.layouts.master')
@section('title', 'Billing')
@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header d-flex align-items-center">
                                <h5 class="card-title mb-0 flex-grow-1">Billing & Invoice Master</h5>
                                @can('create', \App\Models\Billing::class)
                                    <a href="{{ route('admin.billings.create') }}" class="btn btn-sm btn-primary">Add
                                        Billing %</a>
                                @endcan
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="billing" class="table table-bordered nowrap w-100">
                                        <thead>
                                            <tr>
                                                <th>S.No</th>
                                                <th>Invoice No.</th><th>Title</th><th>Billing %</th><th>Invoice Date</th><th>Amount</th>
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
        $(document).ready(function() {
            var table = $('#billing').DataTable({
                processing: true,
                serverSide: true,
                scrollX: true,
                ajax: {
                    url: '{{ route('admin.billings.index') }}',
                    type: 'GET'
                },
                columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                }, {
                    data: 'invoice_number', name: 'invoice_number'
                }, {
                    data: 'title', name: 'title'
                }, {
                    data: 'value',
                    name: 'value'
                }, {
                    data: 'invoice_date', name: 'invoice_date'
                }, {
                    data: 'amount', name: 'amount'
                }, {
                    data: 'status',
                    name: 'status',
                    orderable: false,
                    searchable: false
                }, {
                    data: 'created_at',
                    name: 'created_at'
                }, {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                }]
            });
            $(document).on('click', '.delete-record', function() {
                if (!confirm('Are you sure you want to delete this billing?')) return;
                $.ajax({
                    url: $(this).data('route'),
                    type: 'DELETE',
                    success: function(res) {
                        res.status ? toastr.success(res.message) : toastr.error(res.message);
                        table.ajax.reload(null, false);
                    }
                });
            });
        });
    </script>
@endsection
