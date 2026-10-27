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

            /* Salary Breakup Modal High z-index */
            #deploymentSalaryBreakupModal {
                z-index: 1095 !important;
            }
            #deploymentSalaryBreakupModal .modal-dialog {
                z-index: 1100 !important;
                position: relative !important;
            }
            #deploymentSalaryBreakupModal .modal-content {
                background-color: #ffffff !important;
                position: relative !important;
                z-index: 1105 !important;
                box-shadow: 0 12px 35px rgba(0, 0, 0, 0.45) !important;
            }
            </style>

            <!-- Deployment Employees Modal -->
            <div class="modal fade" id="deploymentEmployeesModal" tabindex="-1" aria-labelledby="deploymentEmployeesModalLabel" aria-hidden="true" style="z-index: 1080 !important;">
                <div class="modal-dialog modal-dialog-centered modal-xl" style="z-index: 1085 !important; max-width: 1250px;">
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
                                            <th style="width: 40px;" class="text-center">#</th>
                                            <th>Employee</th>
                                            <th>Client & Job Role</th>
                                            <th>Job Location</th>
                                            <th class="text-center">Tenure (DOJ / Relieving)</th>
                                            <th class="text-end">Salary & Breakup</th>
                                            <th class="text-center">Offer Letter</th>
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

            <!-- Dedicated Salary Breakup Modal for Deployments -->
            <div class="modal fade" id="deploymentSalaryBreakupModal" tabindex="-1" aria-labelledby="deploymentSalaryBreakupModalLabel" aria-hidden="true" style="z-index: 1095 !important;">
                <div class="modal-dialog modal-dialog-centered modal-md" style="z-index: 1100 !important;">
                    <div class="modal-content border-0 shadow-lg" style="background-color: #ffffff !important; position: relative !important; z-index: 1105 !important;">
                        <div class="modal-header bg-dark text-white py-3">
                            <div class="d-flex align-items-center gap-2">
                                <i class="ri-file-list-3-line fs-20 text-info"></i>
                                <div>
                                    <h5 class="modal-title text-white mb-0" id="deploymentSalaryBreakupModalLabel">Salary Breakup & Statutory Details</h5>
                                    <small class="text-white-50" id="deploymentSalaryBreakupEmpInfo">—</small>
                                </div>
                            </div>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-3" id="deploymentSalaryBreakupModalBody">
                            <!-- Populated dynamically via JS -->
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

function ensureDeploymentSalaryModalInBody() {
    var modal = document.getElementById('deploymentSalaryBreakupModal');
    if (modal && modal.parentElement !== document.body) {
        document.body.appendChild(modal);
    }
}

var currentDeploymentEmployees = [];

function showDeploymentSalaryBreakupModal(emp) {
    if (!emp) return;
    var b = emp.salary_breakup || {};
    var sal = parseFloat(emp.salary || emp.ctc) || 0;
    var basic = parseFloat(b.basic) || (sal * 0.50);
    var hra = parseFloat(b.hra) || (sal * 0.30);
    var conveyance = parseFloat(b.conveyance) || 0;
    var medical = parseFloat(b.medical) || 0;
    var special = parseFloat(b.special) || (sal * 0.20);
    var otherEarn = parseFloat(b.overtime || 0) + parseFloat(b.lta || 0) + parseFloat(b.arrears || 0);
    var gross = parseFloat(b.gross) || sal;

    var pf = parseFloat(b.pf) || 0;
    var esi = parseFloat(b.esi) || 0;
    var pt = parseFloat(b.pt) || 0;
    var tds = parseFloat(b.tds) || (sal * 0.10);
    var otherDed = parseFloat(b.salary_advance || 0) + parseFloat(b.fines || 0) + parseFloat(b.labour_welfare || 0) + parseFloat(b.other_deductions || 0);
    var totalDed = pf + esi + pt + tds + otherDed;
    var net = Math.max(0, gross - totalDed);

    $('#deploymentSalaryBreakupEmpInfo').html('<strong>' + (emp.name || 'Employee') + '</strong> &bull; ' + (emp.employee_no || '') + ' (' + (emp.designation || 'Staff') + ')');

    var html = `
        <div class="card border-0 bg-light mb-3">
            <div class="card-body p-2 d-flex align-items-center gap-3">
                <img src="${emp.avatar_url || '/admin/images/users/user-dummy-img.jpg'}" class="rounded-circle border" style="width: 44px; height: 44px; object-fit: cover;" onerror="this.onerror=null;this.src='/admin/images/users/user-dummy-img.jpg';">
                <div class="flex-grow-1">
                    <h6 class="mb-0 fw-bold fs-14 text-dark">${emp.name || '—'}</h6>
                    <small class="text-muted d-block">${emp.client || '—'} &bull; <i class="ri-map-pin-line text-danger"></i> ${emp.job_location || 'Bengaluru, India'}</small>
                </div>
                ${emp.offer_letter_url ? `
                    <a href="${emp.offer_letter_url}" target="_blank" rel="noopener" class="btn btn-sm btn-soft-primary">
                        <i class="ri-file-text-line me-1"></i> Offer Letter
                    </a>
                ` : ''}
            </div>
        </div>

        <div class="row g-2">
            <!-- Earnings -->
            <div class="col-6">
                <div class="border rounded-2 p-2 h-100 bg-white shadow-sm">
                    <h6 class="fs-11 text-uppercase fw-bold text-primary mb-2 pb-1 border-bottom">Earnings (₹)</h6>
                    <div class="d-flex justify-content-between fs-12 mb-1"><span class="text-muted">Basic Salary:</span><span class="fw-semibold">₹ ${basic.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span></div>
                    <div class="d-flex justify-content-between fs-12 mb-1"><span class="text-muted">HRA:</span><span class="fw-semibold">₹ ${hra.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span></div>
                    <div class="d-flex justify-content-between fs-12 mb-1"><span class="text-muted">Conveyance:</span><span class="fw-semibold">₹ ${conveyance.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span></div>
                    <div class="d-flex justify-content-between fs-12 mb-1"><span class="text-muted">Medical Allow:</span><span class="fw-semibold">₹ ${medical.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span></div>
                    <div class="d-flex justify-content-between fs-12 mb-1"><span class="text-muted">Special Allow:</span><span class="fw-semibold">₹ ${special.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span></div>
                    ${otherEarn > 0 ? `<div class="d-flex justify-content-between fs-12 mb-1"><span class="text-muted">Other Earnings:</span><span class="fw-semibold">₹ ${otherEarn.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span></div>` : ''}
                    <div class="d-flex justify-content-between fs-12 mt-2 pt-1 border-top fw-bold text-dark"><span>Total Gross:</span><span>₹ ${gross.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span></div>
                </div>
            </div>

            <!-- Deductions -->
            <div class="col-6">
                <div class="border rounded-2 p-2 h-100 bg-white shadow-sm">
                    <h6 class="fs-11 text-uppercase fw-bold text-danger mb-2 pb-1 border-bottom">Deductions (₹)</h6>
                    <div class="d-flex justify-content-between fs-12 mb-1"><span class="text-muted">PF Deduction:</span><span class="text-danger fw-semibold">- ₹ ${pf.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span></div>
                    <div class="d-flex justify-content-between fs-12 mb-1"><span class="text-muted">ESI Deduction:</span><span class="text-danger fw-semibold">- ₹ ${esi.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span></div>
                    <div class="d-flex justify-content-between fs-12 mb-1"><span class="text-muted">Prof. Tax (PT):</span><span class="text-danger fw-semibold">- ₹ ${pt.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span></div>
                    <div class="d-flex justify-content-between fs-12 mb-1"><span class="text-muted">TDS / Income Tax:</span><span class="text-danger fw-semibold">- ₹ ${tds.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span></div>
                    ${otherDed > 0 ? `<div class="d-flex justify-content-between fs-12 mb-1"><span class="text-muted">Other Deductions:</span><span class="text-danger fw-semibold">- ₹ ${otherDed.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span></div>` : ''}
                    <div class="d-flex justify-content-between fs-12 mt-2 pt-1 border-top fw-bold text-danger"><span>Total Deductions:</span><span>- ₹ ${totalDed.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span></div>
                </div>
            </div>
        </div>

        <div class="mt-3 p-3 rounded-3 text-center border" style="background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%); border-color: #a7f3d0 !important;">
            <span class="text-muted text-uppercase fw-semibold fs-11 d-block mb-1">Net Monthly Take-Home (After PF & TDS)</span>
            <h3 class="fw-bold text-success mb-0">₹ ${net.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</h3>
        </div>
    `;

    $('#deploymentSalaryBreakupModalBody').html(html);
    ensureDeploymentSalaryModalInBody();
    var modalEl = document.getElementById('deploymentSalaryBreakupModal');
    if (modalEl && window.bootstrap) {
        var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    }
}

$(document).ready(function () {
    ensureDeploymentModalInBody();
    ensureDeploymentSalaryModalInBody();

    $(document).on('click', '.view-deployment-salary-breakup-btn', function() {
        var idx = $(this).data('emp-idx');
        var emp = currentDeploymentEmployees[idx];
        if (emp) {
            showDeploymentSalaryBreakupModal(emp);
        }
    });

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

        currentDeploymentEmployees = emps;

        $('#deploymentEmployeesModalSub').text(title + ' (' + emps.length + ' Employees)');
        var tbody = '';
        var totalSal = 0;
        var totalRev = 0;

        emps.forEach(function(emp, idx) {
            var sal = parseFloat(emp.salary || emp.ctc) || 0;
            var rev = parseFloat(emp.revenue) || 0;
            totalSal += sal;
            totalRev += rev;

            var avatarImg = emp.avatar_url || '/admin/images/users/user-dummy-img.jpg';
            var empName = emp.name || '—';
            var empNo = emp.employee_no || ('SZ' + String(emp.id).padStart(3, '0'));
            var clientName = emp.client || '—';
            var roleName = emp.designation || 'Staff';
            var locationName = emp.job_location || 'Bengaluru, India';
            var dojStr = emp.date_of_joining || '—';
            var relievingStr = emp.relieving_date || 'Active';
            var isRelievingActive = !emp.relieving_date || emp.relieving_date === 'Active' || emp.relieving_date === 'Active / Ongoing';

            var offerLetterHtml = '';
            if (emp.offer_letter_url) {
                offerLetterHtml = `
                    <div class="btn-group btn-group-sm">
                        <a href="${emp.offer_letter_url}" target="_blank" rel="noopener" class="btn btn-sm btn-soft-primary py-1 px-2 d-inline-flex align-items-center" title="View Offer Letter">
                            <i class="ri-file-text-line me-1"></i> View
                        </a>
                        <a href="${emp.offer_letter_url}" download class="btn btn-sm btn-soft-success py-1 px-2" title="Download Offer Letter">
                            <i class="ri-download-2-line"></i>
                        </a>
                    </div>
                `;
            } else {
                offerLetterHtml = `<span class="badge bg-light text-muted border py-1 px-2 fs-11"><i class="ri-file-forbid-line me-1"></i> Not Uploaded</span>`;
            }

            tbody += `<tr>
                <td class="text-center text-muted fw-semibold">${idx + 1}</td>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <img src="${avatarImg}" alt="${empName}" class="rounded-circle border shadow-sm flex-shrink-0" style="width: 40px; height: 40px; object-fit: cover;" onerror="this.onerror=null;this.src='/admin/images/users/user-dummy-img.jpg';">
                        <div>
                            <span class="fw-bold text-dark d-block fs-13">${empName}</span>
                            <span class="badge bg-light text-dark border fs-11">${empNo}</span>
                        </div>
                    </div>
                </td>
                <td>
                    <span class="fw-semibold text-primary d-block">
                        <i class="ri-building-line me-1 text-muted"></i>${clientName}
                    </span>
                    <span class="badge bg-primary-subtle text-primary mt-1 fs-11">
                        <i class="ri-briefcase-line me-1"></i>${roleName}
                    </span>
                </td>
                <td>
                    <span class="text-dark fs-12 text-nowrap">
                        <i class="ri-map-pin-2-line text-danger me-1"></i>${locationName}
                    </span>
                </td>
                <td class="text-center text-nowrap fs-12">
                    <div><span class="text-muted">DOJ:</span> <strong class="text-dark">${dojStr}</strong></div>
                    <div class="mt-1"><span class="text-muted">Relieving:</span> <span class="badge ${isRelievingActive ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary'} fs-11">${relievingStr}</span></div>
                </td>
                <td class="text-end">
                    <span class="fw-bold text-dark fs-14 d-block">₹ ${sal.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
                    <div class="mt-1">
                        <button type="button" class="btn btn-sm btn-soft-info py-0 px-2 fs-11 view-deployment-salary-breakup-btn" data-emp-idx="${idx}" title="View detailed salary breakup including PF, TDS, etc.">
                            <i class="ri-file-list-3-line me-1"></i> Salary Breakup
                        </button>
                    </div>
                </td>
                <td class="text-center">
                    ${offerLetterHtml}
                </td>
                <td class="text-end fw-bold text-success fs-14">
                    ₹ ${rev.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}
                </td>
            </tr>`;
        });

        if (emps.length === 0) {
            tbody = '<tr><td colspan="8" class="text-center text-muted py-4">No employees found.</td></tr>';
            $('#deploymentEmployeesTfoot').html('');
        } else {
            var tfoot = `<tr>
                <td colspan="5" class="text-end">Total (${emps.length} Employees):</td>
                <td class="text-end text-dark fs-14">₹ ${totalSal.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                <td></td>
                <td class="text-end text-success fs-14">₹ ${totalRev.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
            </tr>`;
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

    $('#deploymentSalaryBreakupModal').on('hidden.bs.modal', function () {
        if ($('#deploymentEmployeesModal').hasClass('show')) {
            $('body').addClass('modal-open');
        }
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

