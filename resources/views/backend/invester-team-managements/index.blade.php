@extends('backend.layouts.master')
@section('title', 'Team & Management Scope')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <!-- Page Title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Team & Management Scope</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('admin.investers.index') }}">Investers</a></li>
                                <li class="breadcrumb-item active">Team & Management Scope</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="ri-check-double-line me-1"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <h5 class="card-title mb-0 flex-grow-1">Team & Management Scope Records</h5>
                            <div class="d-flex flex-wrap gap-2">
                                @can('read', \App\Models\InvesterTeamManagement::class)
                                    <a href="{{ route('admin.invester-team-managements.export') }}" class="btn btn-sm btn-info">
                                        <i class="ri-file-excel-2-line align-middle me-1"></i> Export Excel
                                    </a>
                                @endcan
                                @can('create', \App\Models\InvesterTeamManagement::class)
                                    <a href="{{ route('admin.invester-team-managements.create') }}" class="btn btn-sm btn-primary">
                                        <i class="ri-add-line align-middle me-1"></i> Add Record
                                    </a>
                                @endcan
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="team-mgmt-datatable" class="table table-bordered table-striped dt-responsive nowrap align-middle w-100">
                                    <thead>
                                        <tr>
                                            <th>S.No</th>
                                            <th>Invester</th>
                                            <th>Team / Organization Name</th>
                                            <th>Managed Employee Count</th>
                                            <th>Key Operational Contacts / Delegates</th>
                                            <th>Management / Governance Role</th>
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
    var table = $('#team-mgmt-datatable').DataTable({
        processing: true,
        serverSide: true,
        scrollX: true,
        ajax: {
            url: '{{ route('admin.invester-team-managements.index') }}',
            type: 'GET'
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'invester_name', name: 'invester.name' },
            { data: 'team_name', name: 'team_name' },
            { data: 'managed_employee_count', name: 'managed_employee_count' },
            { data: 'operational_contacts', name: 'operational_contacts' },
            { data: 'governance_role', name: 'governance_role' },
            { data: 'status', name: 'status', orderable: false, searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ]
    });

    $(document).on('click', '.delete-record', function () {
        var routeUrl = $(this).data('route');
        if (!confirm('Are you sure you want to delete this record?')) {
            return;
        }

        $.ajax({
            url: routeUrl,
            type: 'DELETE',
            data: { _token: '{{ csrf_token() }}' },
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

