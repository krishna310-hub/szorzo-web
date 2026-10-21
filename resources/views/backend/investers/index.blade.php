@extends('backend.layouts.master')
@section('title', 'Investers')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <!-- Page Title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Investers</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                                <li class="breadcrumb-item active">Investers</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            @include('backend.investers.partials.nav-buttons')

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="ri-check-double-line me-1"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="ri-error-warning-line me-1"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <h5 class="card-title mb-0 flex-grow-1">Investers Directory</h5>
                            <div class="d-flex flex-wrap gap-2">
                                @can('read', \App\Models\Invester::class)
                                    <a href="{{ route('admin.investers.export') }}" class="btn btn-sm btn-info">
                                        <i class="ri-file-excel-2-line align-middle me-1"></i> Export Excel
                                    </a>
                                @endcan
                                @can('create', \App\Models\Invester::class)
                                    <a href="{{ route('admin.investers.create') }}" class="btn btn-sm btn-primary">
                                        <i class="ri-add-line align-middle me-1"></i> Add New Investor
                                    </a>
                                @endcan
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="investers-datatable" class="table table-bordered table-striped dt-responsive nowrap align-middle w-100">
                                    <thead>
                                        <tr>
                                            <th>S.No</th>
                                            <th>Photo</th>
                                            <th>Invester ID</th>
                                            <th>Invester Name</th>
                                            <th>Investor Type</th>
                                            <th>Contact</th>
                                            <th>Primary Email</th>
                                            <th>Account Manager</th>
                                            <th>Status</th>
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
    var table = $('#investers-datatable').DataTable({
        processing: true,
        serverSide: true,
        scrollX: true,
        ajax: {
            url: '{{ route('admin.investers.index') }}',
            type: 'GET'
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'profile_picture_thumb', name: 'profile_picture', orderable: false, searchable: false },
            { data: 'invester_id', name: 'invester_id' },
            { data: 'name', name: 'name' },
            { data: 'investor_type', name: 'investor_type' },
            { data: 'contact', name: 'contact' },
            { data: 'primary_email', name: 'primary_email' },
            { data: 'account_manager_name', name: 'accountManager.employee_name' },
            { data: 'status', name: 'status', orderable: false, searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ]
    });

    $(document).on('click', '.delete-record', function () {
        var routeUrl = $(this).data('route');
        if (!confirm('Are you sure you want to delete this investor?')) {
            return;
        }

        $.ajax({
            url: routeUrl,
            type: 'DELETE',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function (res) {
                if (res.status) {
                    if (typeof toastr !== 'undefined') {
                        toastr.success(res.message);
                    } else {
                        alert(res.message);
                    }
                    table.ajax.reload(null, false);
                } else {
                    if (typeof toastr !== 'undefined') {
                        toastr.error(res.message || 'Error deleting record');
                    } else {
                        alert(res.message || 'Error deleting record');
                    }
                }
            },
            error: function () {
                alert('An error occurred while deleting the record.');
            }
        });
    });
});
</script>
@endsection

