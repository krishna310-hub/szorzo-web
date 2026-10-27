@extends('backend.layouts.master')
@section('title', 'Invest Modules - Employment Details & Profit Sharing')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <!-- Page Title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Invest Modules & Profit Sharing</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('admin.investers.index') }}">Investers</a></li>
                                <li class="breadcrumb-item active">Invest Modules</li>
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

            <!-- Filters Card -->
            <div class="row mb-3">
                <div class="col-12">
                    <div class="card mb-0">
                        <div class="card-header bg-light py-2">
                            <h6 class="card-title mb-0 fs-13"><i class="ri-filter-3-line me-1 text-primary"></i> Filter Deployments</h6>
                        </div>
                        <div class="card-body py-3">
                            <div class="row g-2 align-items-end">
                                @if(!$isInvestorUser)
                                    <div class="col-md-4">
                                        <label class="form-label small mb-1">Choose Investor</label>
                                        <select id="filter_invester_id" class="form-select form-select-sm">
                                            <option value="">All Investors</option>
                                            @foreach($investers as $inv)
                                                <option value="{{ $inv->id }}">{{ $inv->name }} ({{ $inv->invester_id }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif
                                <div class="col-md-{{ $isInvestorUser ? '6' : '4' }}">
                                    <label class="form-label small mb-1">Client</label>
                                    <select id="filter_client_id" class="form-select form-select-sm">
                                        <option value="">{{ $isInvestorUser ? 'All Mapped Clients' : 'All Clients' }}</option>
                                        @foreach($clients as $cli)
                                            <option value="{{ $cli->id }}">{{ $cli->client }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-{{ $isInvestorUser ? '5' : '3' }}">
                                    <label class="form-label small mb-1">Status</label>
                                    <select id="filter_status" class="form-select form-select-sm">
                                        <option value="">All Status</option>
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div>
                                <div class="col-md-1">
                                    <button type="button" id="btn-reset-filters" class="btn btn-sm btn-outline-secondary w-100" title="Reset Filters">
                                        <i class="ri-refresh-line"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- DataTable Card -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                            <div>
                                <h5 class="card-title mb-0">Employment Details & Profit Sharing Records</h5>
                                <small class="text-muted">Rate Card, CTC, Margin % and @if($isInvestorUser) Investor Profit Share (30%) @else Profit Share split (SZ 70% / Investor 30%) @endif</small>
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                @if(!$isInvestorUser)
                                    @can('read', \App\Models\InvesterDeployment::class)
                                        <a href="{{ route('admin.invester-deployments.export') }}" class="btn btn-sm btn-info">
                                            <i class="ri-file-excel-2-line align-middle me-1"></i> Export Excel
                                        </a>
                                    @endcan
                                @endif
                                @can('create', \App\Models\InvesterDeployment::class)
                                    <a href="{{ route('admin.invester-deployments.create') }}" class="btn btn-sm btn-primary">
                                        <i class="ri-add-line align-middle me-1"></i> Add Deployment / Invest Module
                                    </a>
                                @endcan
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="deployments-datatable" class="table table-bordered table-striped dt-responsive nowrap align-middle w-100">
                                    <thead>
                                        <tr>
                                            <th>S.No</th>
                                            <th>Invester</th>
                                            <th>Employee</th>
                                            <th>Deployed Client</th>
                                            <th>Employee CTC (₹)</th>
                                            <th>Margin In Rupees</th>
                                            <th>@if($isInvestorUser) Investor Share (30%) @else Profit Sharing (SZ / Inv) @endif</th>
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

            <style>
            /* High z-index & backdrop stacking fix for Deployment Employees Modal */
            #deploymentEmployeesModal {
                z-index: 1080 !important;
            }
            #deploymentEmployeesModal .modal-dialog {
                z-index: 1085 !important;
                position: relative !important;
            }
            #deploymentEmployeesModal .modal-content {
                background-color: #ffffff !important;
                position: relative !important;
                z-index: 1090 !important;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4) !important;
            }
            </style>

            <!-- Deployment Employees Modal -->
            <div class="modal fade" id="deploymentEmployeesModal" tabindex="-1" aria-labelledby="deploymentEmployeesModalLabel" aria-hidden="true" style="z-index: 1080 !important;">
                <div class="modal-dialog modal-dialog-centered modal-lg" style="z-index: 1085 !important;">
                    <div class="modal-content border-0 shadow" style="background-color: #ffffff !important; position: relative !important; z-index: 1090 !important;">
                        <div class="modal-header bg-primary text-white py-3">
                            <div class="d-flex align-items-center gap-2">
                                <i class="ri-team-line fs-20"></i>
                                <div>
                                    <h5 class="modal-title text-white mb-0" id="deploymentEmployeesModalLabel">Mapped Employees</h5>
                                    <small class="text-white-50" id="deploymentEmployeesModalSub">—</small>
                                </div>
                            </div>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-3">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 40px;">#</th>
                                            <th>Employee Name</th>
                                            <th>Employee ID</th>
                                            <th>Designation</th>
                                            <th>Deployed Client</th>
                                            <th class="text-end">Salary / CTC (₹)</th>
                                            <th class="text-end">Margin Generated (₹)</th>
                                        </tr>
                                    </thead>
                                    <tbody id="deploymentEmployeesTbody"></tbody>
                                    <tfoot class="table-light fw-bold" id="deploymentEmployeesTfoot"></tfoot>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer bg-light py-2">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
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
function ensureDeploymentModalInBody() {
    var modal = document.getElementById('deploymentEmployeesModal');
    if (modal && modal.parentElement !== document.body) {
        document.body.appendChild(modal);
    }
}

$(document).ready(function () {
    ensureDeploymentModalInBody();
    var table = $('#deployments-datatable').DataTable({
        processing: true,
        serverSide: true,
        scrollX: true,
        ajax: {
            url: '{{ route('admin.invester-deployments.index') }}',
            type: 'GET',
            data: function (d) {
                d.invester_id = $('#filter_invester_id').val();
                d.client_id = $('#filter_client_id').val();
                d.status = $('#filter_status').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'invester_name', name: 'invester.name' },
            { data: 'employee_name', name: 'employee.employee_name' },
            { data: 'client_name', name: 'client.client' },
            { data: 'rate_card_formatted', name: 'employee_ctc' },
            { data: 'margin_in_rupees', name: 'margin_amount' },
            { data: 'commission_split', name: 'commission_split', orderable: false, searchable: false },
            { data: 'status', name: 'status', orderable: false, searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ]
    });

    $('#filter_invester_id, #filter_client_id, #filter_status').on('change', function() {
        table.ajax.reload();
    });

    $('#btn-reset-filters').on('click', function() {
        $('#filter_invester_id').val('');
        $('#filter_client_id').val('');
        $('#filter_status').val('');
        table.ajax.reload();
    });

    $(document).on('click', '.view-deployment-emps-btn', function () {
        var title = $(this).data('title') || 'Mapped Employees';
        var emps = $(this).data('employees');
        if (typeof emps === 'string') {
            try { emps = JSON.parse(emps); } catch(e) { emps = []; }
        }
        if (!Array.isArray(emps)) emps = [];

        $('#deploymentEmployeesModalSub').text(title + ' (' + emps.length + ' Employees)');
        var tbody = '';
        var totalSal = 0;
        var totalRev = 0;

        emps.forEach(function(emp, idx) {
            var sal = parseFloat(emp.salary || emp.ctc) || 0;
            var bPct = parseFloat(emp.billing_percentage) || 15.0;
            var rev = parseFloat(emp.revenue) || (Math.round(sal * (bPct / 100) * 100) / 100);
            totalSal += sal;
            totalRev += rev;

            tbody += '<tr>' +
                '<td class="text-center text-muted">' + (idx + 1) + '</td>' +
                '<td class="fw-bold text-dark">' + (emp.name || '—') + '</td>' +
                '<td><span class="badge bg-light text-dark border">' + (emp.employee_no || ('SZ' + String(emp.id).padStart(3, '0'))) + '</span></td>' +
                '<td>' + (emp.designation || 'Staff') + '</td>' +
                '<td>' + (emp.client || '—') + '</td>' +
                '<td class="text-end fw-semibold text-dark">₹ ' + sal.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</td>' +
                '<td class="text-end fw-bold text-success">₹ ' + rev.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</td>' +
            '</tr>';
        });

        if (emps.length === 0) {
            tbody = '<tr><td colspan="7" class="text-center text-muted py-3">No employees found.</td></tr>';
            $('#deploymentEmployeesTfoot').html('');
        } else {
            var tfoot = '<tr>' +
                '<td colspan="5" class="text-end">Total (' + emps.length + ' Employees):</td>' +
                '<td class="text-end text-dark">₹ ' + totalSal.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</td>' +
                '<td class="text-end text-success">₹ ' + totalRev.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</td>' +
            '</tr>';
            $('#deploymentEmployeesTfoot').html(tfoot);
        }

        $('#deploymentEmployeesTbody').html(tbody);
        ensureDeploymentModalInBody();
        var modalEl = document.getElementById('deploymentEmployeesModal');
        if (modalEl && window.bootstrap) {
            var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        }
    });

    $('#deploymentEmployeesModal').on('hidden.bs.modal', function () {
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open').css({ overflow: '', paddingRight: '' });
    });

    $(document).on('click', '.delete-btn', function () {
        var id = $(this).data('id');
        if (!confirm('Are you sure you want to delete this deployment record?')) {
            return;
        }

        $.ajax({
            url: '{{ url('admin/investers/deployments') }}/' + id,
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
                    alert(res.message || 'Error deleting record');
                }
            },
            error: function () {
                alert('An error occurred while deleting the deployment record.');
            }
        });
    });
});
</script>
@endsection

