@php
    $selectedClientIds = old('client_ids', isset($deployment) ? ($deployment->client_ids ?: ($deployment->client_id ? [$deployment->client_id] : [])) : []);
    if (!is_array($selectedClientIds)) {
        $selectedClientIds = json_decode($selectedClientIds, true) ?: [];
    }
    $selectedClientIds = array_values(array_filter(array_map('intval', (array) $selectedClientIds)));

    $selectedEmployeeIds = old('employee_ids', isset($deployment) ? ($deployment->employee_ids ?: ($deployment->employee_id ? [$deployment->employee_id] : [])) : []);
    if (!is_array($selectedEmployeeIds)) {
        $selectedEmployeeIds = json_decode($selectedEmployeeIds, true) ?: [];
    }
    $selectedEmployeeIds = array_values(array_filter(array_map('intval', (array) $selectedEmployeeIds)));

    // Client lookup map for fast zero-latency client-side interaction
    $clientsJson = $clients->map(function ($c) {
        return [
            'id' => $c->id,
            'name' => $c->client,
            'billing' => $c->billing ? (float) $c->billing->value : null,
        ];
    })->values();

    // External employees lookup map (External contract employees only)
    $employeesJson = $employees->map(function ($emp) {
        $no = $emp->employee_no ?: ('SZ' . str_pad($emp->id, 3, '0', STR_PAD_LEFT));
        $salaryData = $emp->getContractReportSalary();
        $salary = (float) $salaryData['salary'];
        $durInfo = $emp->getContractDurationInfo();
        $durLabel = !empty($durInfo['duration']) ? ' [' . $durInfo['duration'] . ']' : '';
        $billingPct = $emp->getClientRequirementBillingPercentage();
        $billingLabel = $billingPct !== null ? ' [' . $billingPct . '% Billing]' : '';

        return [
            'id' => (int) $emp->id,
            'client_id' => (int) $emp->client_id,
            'client_name' => $emp->client?->client ?? '',
            'name' => $emp->employee_name,
            'employee_name' => $emp->employee_name,
            'employee_no' => $no,
            'designation' => $emp->designation ?? '',
            'monthly_gross' => (float) ($emp->monthly_gross ?? 0),
            'salary' => $salary,
            'ctc' => $salary,
            'present_days' => $salaryData['present_days'],
            'absent_days' => $salaryData['absent_days'],
            'days_in_month' => $salaryData['days_in_month'],
            'source' => $salaryData['source'],
            'contract_duration' => $durInfo['duration'] ?? '',
            'contract_duration_detail' => $durInfo['detail'] ?? '',
            'contract_from' => $durInfo['from_date'] ?? null,
            'contract_to' => $durInfo['to_date'] ?? null,
            'billing_percentage' => $billingPct,
            'salary_formatted' => number_format($salary, 2),
            'label' => $emp->employee_name . ' (' . $no . ($emp->designation ? ' - ' . $emp->designation : '') . ')' . ($salary > 0 ? ' - ₹' . number_format($salary, 2) . '/mo' : '') . $billingLabel . $durLabel . ($emp->client ? ' [' . $emp->client->client . ']' : ''),
        ];
    })->values();
@endphp

<div class="row">
    <!-- Section: Invester & Deployment Association -->
    <div class="col-12 mb-3">
        <div class="card border shadow-none">
            <div class="card-header bg-light">
                <h6 class="card-title mb-0">
                    <i class="ri-user-star-line me-1 text-primary"></i> 1. Investor & Stakeholder Allocation
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Invester <span class="text-danger">*</span></label>
                        @php
                            $selectedInvesterId = old('invester_id', $deployment->invester_id ?? $preselectedInvesterId ?? '');
                        @endphp
                        <select class="form-select @error('invester_id') is-invalid @enderror" name="invester_id" id="invester_id" required>
                            <option value="">Select Invester</option>
                            @foreach($investers as $inv)
                                <option value="{{ $inv->id }}" {{ $selectedInvesterId == $inv->id ? 'selected' : '' }}>
                                    {{ $inv->name }} ({{ $inv->invester_id }})
                                </option>
                            @endforeach
                        </select>
                        @error('invester_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                        <div class="d-flex align-items-center gap-4 mt-2">
                            <div class="form-check form-radio-success">
                                <input class="form-check-input" type="radio" name="status" id="depStatusActive" value="1" 
                                    {{ old('status', $deployment->status ?? 1) == 1 ? 'checked' : '' }}>
                                <label class="form-check-label" for="depStatusActive">Active</label>
                            </div>
                            <div class="form-check form-radio-danger">
                                <input class="form-check-input" type="radio" name="status" id="depStatusInactive" value="0" 
                                    {{ old('status', $deployment->status ?? 1) == 0 ? 'checked' : '' }}>
                                <label class="form-check-label" for="depStatusInactive">Inactive</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section: Employment Details (Matching Photo Layout + Client/Employee Multi-Select & Calculations) -->
    <div class="col-12 mb-3">
        <div class="card border shadow-none">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0">
                    <i class="ri-briefcase-4-line me-1 text-primary"></i> 2. Employment Details & Profit Sharing Deal
                </h6>
                <span class="badge bg-warning-subtle text-warning fw-semibold fs-12">
                    <i class="ri-calculator-line me-1"></i> Live Margin & Commission Calculation
                </span>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Client Selection (Multi-select) -->
                    <div class="col-md-6 mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fw-semibold mb-0" for="client_ids">
                                Deployed To (Client Name) <span class="text-danger">*</span>
                            </label>
                            <span class="badge bg-info-subtle text-info fs-11" id="client_count_badge">
                                0 Clients Selected
                            </span>
                        </div>
                        <select class="form-select @error('client_ids') is-invalid @enderror @error('client_id') is-invalid @enderror" 
                            name="client_ids[]" id="client_ids" multiple required>
                            @foreach($clients as $cli)
                                @php
                                    $isSelected = in_array((int)$cli->id, $selectedClientIds, true);
                                @endphp
                                <option value="{{ $cli->id }}" {{ $isSelected ? 'selected' : '' }}>
                                    {{ $cli->client }}
                                </option>
                            @endforeach
                        </select>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <small class="text-muted"><i class="ri-information-line me-1"></i> Multi-select client(s). External staff will auto-filter.</small>
                        </div>
                        @error('client_ids')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        @error('client_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- External Employees Selection (Multi-select, Filtered to External ONLY for selected clients) -->
                    <div class="col-md-6 mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fw-semibold mb-0" for="employee_ids">
                                Employee Name (External Staff Only) <span class="text-danger">*</span>
                            </label>
                            <div>
                                <span class="badge bg-success-subtle text-success fs-11" id="employee_selected_badge">
                                    0 External Employees Selected
                                </span>
                                <span class="badge bg-primary-subtle text-primary fs-11 ms-1" id="employee_billing_sum_badge" style="display:none;"></span>
                            </div>
                        </div>
                        <select class="form-select @error('employee_ids') is-invalid @enderror @error('employee_id') is-invalid @enderror" 
                            name="employee_ids[]" id="employee_ids" multiple required>
                            <!-- Populated dynamically via JS / AJAX based on selected clients and investor -->
                        </select>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <small class="text-muted"><i class="ri-user-shared-line me-1"></i> Strictly external contract employees only (excludes employees on other investors).</small>
                            <div>
                                <span id="employee_billing_detail_badge" class="badge bg-light text-primary fs-11 me-1"></span>
                                <span id="employee_ctc_sum_badge" class="badge bg-light text-primary fs-11"></span>
                            </div>
                        </div>
                        @error('employee_ids')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        @error('employee_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-12 mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fw-semibold mb-0" for="contract_duration">Contract Duration</label>
                            <div>
                                <span class="badge bg-secondary-subtle text-secondary fs-11" id="contract_duration_badge">
                                    <i class="ri-time-line me-1"></i> Employee Module
                                </span>
                                <a href="javascript:void(0)" id="reset_contract_duration_btn" class="fs-11 text-decoration-none text-primary ms-2" style="display:none;">
                                    <i class="ri-refresh-line"></i> Re-fetch from employees
                                </a>
                            </div>
                        </div>
                        <input type="text" class="form-control @error('contract_duration') is-invalid @enderror" 
                            name="contract_duration" id="contract_duration"
                            value="{{ old('contract_duration', $deployment->contract_duration ?? '') }}" 
                            placeholder="Auto-fetched based on selected employee(s) contract duration (e.g. 6 Months, 1 Year)">
                        <div class="small text-muted mt-1" id="contract_duration_hint">
                            <i class="ri-time-line me-1"></i> Auto-fetched from employee contract dates in Employee module
                        </div>
                        @error('contract_duration')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Financial Rates, CTC, Billing %, TDS & Margin -->
                <div class="row pt-2 border-top mt-2">
                    <div class="col-md-6 mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fw-semibold mb-0" for="employee_ctc">
                                Employee CTC (₹) <span class="text-danger">*</span>
                            </label>
                            <span class="badge bg-info-subtle text-info fs-11">Contract Report</span>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="number" step="0.01" min="0" class="form-control @error('employee_ctc') is-invalid @enderror" 
                                name="employee_ctc" id="employee_ctc"
                                value="{{ old('employee_ctc', isset($deployment) && $deployment->employee_ctc !== null ? (float)$deployment->employee_ctc : '') }}" 
                                placeholder="Total salary from Contract Report" required>
                        </div>
                        <small class="text-muted" id="employee_ctc_hint">Total payable salary from Contract Report (based on days worked this month)</small>
                        @error('employee_ctc')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fw-semibold mb-0" for="billing_percentage">Client Billing %</label>
                            <span class="badge bg-primary-subtle text-primary fs-11" id="billing_source_badge">
                                <i class="ri-file-list-3-line me-1"></i> Requirement Mapped
                            </span>
                        </div>
                        <div class="input-group">
                            <input type="number" step="0.01" min="0" max="100" class="form-control @error('billing_percentage') is-invalid @enderror" 
                                name="billing_percentage" id="billing_percentage"
                                value="{{ old('billing_percentage', isset($deployment) && $deployment->billing_percentage !== null ? (float)$deployment->billing_percentage : '') }}" 
                                placeholder="Total Client Requirement Billing %">
                            <span class="input-group-text">%</span>
                        </div>
                        <small class="text-muted" id="billing_percentage_hint">Sum of billing % from selected employee(s) mapped client requirements</small>
                        @error('billing_percentage')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold" for="margin_percentage">Target Margin %</label>
                        <div class="input-group">
                            <input type="number" step="0.01" min="0" max="100" class="form-control @error('margin_percentage') is-invalid @enderror" 
                                name="margin_percentage" id="margin_percentage"
                                value="{{ old('margin_percentage', $deployment->margin_percentage ?? '15') }}" 
                                placeholder="15">
                            <span class="input-group-text">%</span>
                        </div>
                        <small class="text-muted">Target margin percentage (editable)</small>
                        @error('margin_percentage')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fw-semibold mb-0" for="tds_percentage">TDS %</label>
                            <span class="badge bg-secondary-subtle text-secondary fs-11">Auto-fetched</span>
                        </div>
                        <div class="input-group">
                            <input type="number" step="0.01" min="0" max="100" class="form-control @error('tds_percentage') is-invalid @enderror" 
                                name="tds_percentage" id="tds_percentage"
                                value="{{ old('tds_percentage', $deployment->tds_percentage ?? '10.00') }}" 
                                placeholder="10.00">
                            <span class="input-group-text">%</span>
                        </div>
                        <small class="text-muted">194J contract staffing standard (10%)</small>
                        @error('tds_percentage')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fw-semibold mb-0" for="tds_amount">TDS Deduction (₹)</label>
                            <span class="badge bg-danger-subtle text-danger fs-11">Auto-calculated</span>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="number" step="0.01" min="0" class="form-control bg-light @error('tds_amount') is-invalid @enderror" 
                                name="tds_amount" id="tds_amount"
                                value="{{ old('tds_amount', isset($deployment) && $deployment->tds_amount !== null ? (float)$deployment->tds_amount : '') }}" 
                                placeholder="Auto-calculated" readonly>
                        </div>
                        <small class="text-muted">Gross TDS: Employee CTC × TDS %</small>
                        @error('tds_amount')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Highlighted Yellow Margin in Rupees (Directly matching Excel photo) -->
                    <div class="col-md-12 mb-3">
                        <div class="p-3 rounded-3" style="background: #fff8bd; border: 2px solid #ecd83c;">
                            <div class="row align-items-center">
                                <div class="col-md-5">
                                    <label class="form-label fw-bold text-dark fs-5 mb-0" for="margin_amount">
                                        <i class="ri-money-rupee-circle-fill text-warning me-1"></i> Margin In Rupees
                                    </label>
                                    <div class="small text-muted">Auto-calculated: Employee CTC × Margin % (editable)</div>
                                </div>
                                <div class="col-md-7">
                                    <div class="input-group input-group-lg">
                                        <span class="input-group-text bg-white fw-bold">₹</span>
                                        <input type="number" step="0.01" min="0" 
                                            class="form-control form-control-lg fw-bolder fs-4 text-dark bg-white @error('margin_amount') is-invalid @enderror" 
                                            name="margin_amount" id="margin_amount"
                                            value="{{ old('margin_amount', isset($deployment) && $deployment->margin_amount !== null ? (float)$deployment->margin_amount : '') }}" 
                                            placeholder="Margin In Rupees">
                                    </div>
                                    @error('margin_amount')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Profit Sharing Model (Matching Photo: SZ 70.00% / Investor 30.00%) -->
                <div class="row pt-3 border-top mt-2">
                    <div class="col-12 mb-2">
                        <h6 class="fw-bold text-dark mb-1">
                            <i class="ri-pie-chart-2-line me-1 text-primary"></i> Profit Sharing Model
                        </h6>
                        <small class="text-muted">Commissions split dynamically between Szorzo (SZ) and Investor</small>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="p-3 border rounded-3 bg-light">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold text-primary fs-6">SZ (Szorzo) Share</span>
                                <div class="input-group" style="width: 140px;">
                                    <input type="number" step="0.01" min="0" max="100" class="form-control text-end fw-bold" 
                                        name="szorzo_share_percentage" id="szorzo_share_percentage"
                                        value="{{ old('szorzo_share_percentage', $deployment->szorzo_share_percentage ?? '70.00') }}">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                <span class="text-muted small">SZ Commission Amount:</span>
                                <span class="fw-bold text-primary fs-5" id="sz_calc_display">₹ 0.00</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="p-3 border rounded-3 bg-light">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold text-success fs-6">Investor Share</span>
                                <div class="input-group" style="width: 140px;">
                                    <input type="number" step="0.01" min="0" max="100" class="form-control text-end fw-bold" 
                                        name="investor_share_percentage" id="investor_share_percentage"
                                        value="{{ old('investor_share_percentage', $deployment->investor_share_percentage ?? '30.00') }}">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                <span class="text-muted small">Investor Commission Amount:</span>
                                <span class="fw-bold text-success fs-5" id="inv_calc_display">₹ 0.00</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Deployment Start Date</label>
                        <input type="date" class="form-control" name="start_date" 
                            value="{{ old('start_date', isset($deployment) && $deployment->start_date ? $deployment->start_date->format('Y-m-d') : '') }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Deployment End Date</label>
                        <input type="date" class="form-control" name="end_date" 
                            value="{{ old('end_date', isset($deployment) && $deployment->end_date ? $deployment->end_date->format('Y-m-d') : '') }}">
                    </div>

                    <div class="col-12 mb-3">
                        <label class="form-label fw-semibold">Deployment Notes / Engagement Remarks</label>
                        <textarea class="form-control" name="notes" rows="2" 
                            placeholder="Add specific terms, milestone details, or notes...">{{ old('notes', $deployment->notes ?? '') }}</textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('script')
<script>
(function() {
    const clientsData = @json($clientsJson);
    let allExternalEmployees = @json($employeesJson);
    const initialSelectedClientIds = @json($selectedClientIds);
    const initialSelectedEmployeeIds = @json($selectedEmployeeIds);
    const deploymentId = {{ isset($deployment) ? (int)$deployment->id : 'null' }};

    const clientSelect = document.getElementById('client_ids');
    const employeeSelect = document.getElementById('employee_ids');
    const investerSelect = document.getElementById('invester_id');
    const employeeCtcInput = document.getElementById('employee_ctc');
    const contractDurationInput = document.getElementById('contract_duration');
    const contractDurationHint = document.getElementById('contract_duration_hint');
    const resetDurationBtn = document.getElementById('reset_contract_duration_btn');
    const billingPctInput = document.getElementById('billing_percentage');
    const billingHint = document.getElementById('billing_percentage_hint');
    const marginPctInput = document.getElementById('margin_percentage');
    const tdsPctInput = document.getElementById('tds_percentage');
    const tdsAmtInput = document.getElementById('tds_amount');
    const marginAmtInput = document.getElementById('margin_amount');
    const szPctInput = document.getElementById('szorzo_share_percentage');
    const invPctInput = document.getElementById('investor_share_percentage');
    const szDisplay = document.getElementById('sz_calc_display');
    const invDisplay = document.getElementById('inv_calc_display');
    const clientCountBadge = document.getElementById('client_count_badge');
    const employeeSelectedBadge = document.getElementById('employee_selected_badge');
    const employeeBillingSumBadge = document.getElementById('employee_billing_sum_badge');
    const employeeBillingDetailBadge = document.getElementById('employee_billing_detail_badge');
    const employeeCtcSumBadge = document.getElementById('employee_ctc_sum_badge');
    const ctcHint = document.getElementById('employee_ctc_hint');

    let clientChoices = null;
    let employeeChoices = null;

    function formatCurrency(val) {
        return '₹ ' + (Number(val) || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // Helper to get selected values from a select or Choices instance
    function getSelectedValues(selectEl, choicesInst) {
        if (choicesInst) {
            return choicesInst.getValue(true).map(v => parseInt(v, 10)).filter(v => !isNaN(v));
        }
        if (!selectEl) return [];
        return Array.from(selectEl.selectedOptions).map(opt => parseInt(opt.value, 10)).filter(v => !isNaN(v));
    }

    // Update Employee choices based on selected client IDs
    function updateEmployeeOptions(selectedClientIds, selectedEmployeeIdsToKeep = []) {
        const filteredEmployees = allExternalEmployees.filter(emp => selectedClientIds.includes(emp.client_id));
        const keepSet = new Set(selectedEmployeeIdsToKeep.map(Number));

        if (employeeChoices) {
            employeeChoices.clearStore();
            const choiceList = filteredEmployees.map(emp => ({
                value: String(emp.id),
                label: emp.label,
                selected: keepSet.has(emp.id),
                customProperties: {
                    salary: emp.salary,
                    ctc: emp.ctc,
                    monthly: emp.monthly_gross,
                    clientId: emp.client_id,
                    presentDays: emp.present_days,
                    absentDays: emp.absent_days,
                    contractDuration: emp.contract_duration,
                    contractDurationDetail: emp.contract_duration_detail,
                    billingPercentage: emp.billing_percentage
                }
            }));
            employeeChoices.setChoices(choiceList, 'value', 'label', true);
        } else if (employeeSelect) {
            employeeSelect.innerHTML = '';
            filteredEmployees.forEach(emp => {
                const opt = document.createElement('option');
                opt.value = emp.id;
                opt.textContent = emp.label;
                opt.dataset.salary = emp.salary;
                opt.dataset.ctc = emp.ctc;
                opt.dataset.monthly = emp.monthly_gross;
                opt.dataset.clientId = emp.client_id;
                opt.dataset.presentDays = emp.present_days;
                opt.dataset.absentDays = emp.absent_days;
                opt.dataset.contractDuration = emp.contract_duration || '';
                opt.dataset.contractDurationDetail = emp.contract_duration_detail || '';
                opt.dataset.billingPercentage = emp.billing_percentage !== undefined ? emp.billing_percentage : '';
                if (keepSet.has(emp.id)) {
                    opt.selected = true;
                }
                employeeSelect.appendChild(opt);
            });
        }

        updateEmployeeSelectionSummary();
    }

    // Update Employee Selection Count & Aggregated Contract Report Salary & Contract Duration & Requirement Billing %
    function updateEmployeeSelectionSummary() {
        const selectedEmpIds = getSelectedValues(employeeSelect, employeeChoices);
        const selectedEmployees = allExternalEmployees.filter(emp => selectedEmpIds.includes(emp.id));

        if (employeeSelectedBadge) {
            employeeSelectedBadge.textContent = selectedEmployees.length + ' External Employee' + (selectedEmployees.length !== 1 ? 's' : '') + ' Selected';
        }

        // Aggregate Salary from Contract Report (calculated based on working days this month)
        const totalSalary = selectedEmployees.reduce((sum, emp) => sum + (Number(emp.salary !== undefined ? emp.salary : emp.ctc) || 0), 0);
        if (employeeCtcSumBadge) {
            employeeCtcSumBadge.textContent = totalSalary > 0 
                ? ('Total Salary: ' + formatCurrency(totalSalary)) 
                : '';
        }

        // Auto-update Employee CTC input if not manually overwritten
        if (employeeCtcInput && !employeeCtcInput.dataset.manuallyEdited) {
            employeeCtcInput.value = totalSalary > 0 ? totalSalary.toFixed(2) : '';
        }

        // Aggregate Client Requirement Billing % from selected employees (Plus all selected employees' client requirement billing)
        const billingPercentages = selectedEmployees
            .map(e => parseFloat(e.billing_percentage) || 0)
            .filter(v => v > 0);
        const totalBilling = billingPercentages.length > 0
            ? Math.round(billingPercentages.reduce((a, b) => a + b, 0) * 100) / 100
            : 0;

        if (billingPctInput && !billingPctInput.dataset.manuallyEdited) {
            billingPctInput.value = totalBilling > 0 ? totalBilling.toFixed(2) : '';
        }

        if (marginPctInput && (!marginPctInput.dataset.manuallyEdited || parseFloat(marginPctInput.value) === 0)) {
            marginPctInput.value = totalBilling > 0 ? totalBilling.toFixed(2) : '';
        }

        if (employeeBillingSumBadge) {
            if (totalBilling > 0) {
                employeeBillingSumBadge.style.display = 'inline-block';
                employeeBillingSumBadge.innerHTML = '<i class="ri-percent-line me-1"></i> Billing Total: ' + totalBilling.toFixed(2) + '%';
            } else {
                employeeBillingSumBadge.style.display = 'none';
            }
        }

        if (employeeBillingDetailBadge) {
            if (totalBilling > 0) {
                employeeBillingDetailBadge.textContent = 'Req Billing: ' + totalBilling.toFixed(2) + '%';
            } else {
                employeeBillingDetailBadge.textContent = '';
            }
        }

        if (billingHint) {
            if (selectedEmployees.length > 0) {
                const details = selectedEmployees.map(e => {
                    const bp = parseFloat(e.billing_percentage) || 0;
                    return e.name + ': ' + (bp > 0 ? (bp.toFixed(2) + '%') : 'No req billing');
                }).join(', ');
                billingHint.innerHTML = '<i class="ri-calculator-line text-primary me-1"></i> Employee Req Billing: ' + details + (totalBilling > 0 ? (' = <strong>' + totalBilling.toFixed(2) + '%</strong>') : '');
            } else {
                billingHint.textContent = 'Sum of billing % from selected employee(s) mapped client requirements';
            }
        }

        // Auto-update Contract Duration from Employee module
        const contractDurationsWithNames = selectedEmployees.map(e => ({
            name: e.name || e.employee_name,
            duration: (e.contract_duration || '').trim(),
            detail: (e.contract_duration_detail || '').trim()
        }));

        const validDurations = contractDurationsWithNames.filter(d => d.duration.length > 0);
        const uniqueDurationTexts = [...new Set(validDurations.map(d => d.duration))];

        if (contractDurationInput && !contractDurationInput.dataset.manuallyEdited) {
            if (validDurations.length === 0) {
                if (!deploymentId) {
                    contractDurationInput.value = '';
                }
            } else if (validDurations.length === 1) {
                contractDurationInput.value = validDurations[0].duration;
            } else if (uniqueDurationTexts.length === 1) {
                // All selected employees share the same duration (e.g. "6 Months")
                contractDurationInput.value = uniqueDurationTexts[0];
            } else {
                // Different durations: "Karun Kumar: 6 Months, Kavya B: 3 Months"
                contractDurationInput.value = validDurations.map(d => d.name + ': ' + d.duration).join(', ');
            }
        }

        // Informative breakdown in hint
        if (contractDurationHint) {
            if (selectedEmployees.length === 0) {
                contractDurationHint.innerHTML = '<i class="ri-time-line me-1"></i> Auto-fetched from employee contract dates in Employee module';
            } else if (validDurations.length > 0) {
                const details = validDurations.map(d => d.detail || (d.name + ': ' + d.duration)).join(' &bull; ');
                contractDurationHint.innerHTML = '<i class="ri-calendar-check-line text-success me-1"></i> <strong>Employee Duration:</strong> ' + details;
            } else {
                contractDurationHint.innerHTML = '<i class="ri-information-line text-warning me-1"></i> Selected employee(s) have no contract dates set in Employee module';
            }
        }

        if (resetDurationBtn) {
            resetDurationBtn.style.display = (contractDurationInput && contractDurationInput.dataset.manuallyEdited && validDurations.length > 0) ? 'inline' : 'none';
        }

        // Auto-update hint with calculation breakdown
        if (ctcHint) {
            if (selectedEmployees.length > 0) {
                const details = selectedEmployees.map(e => {
                    const sal = Number(e.salary || 0).toLocaleString('en-IN');
                    const days = e.present_days !== undefined ? (e.present_days + ' days') : 'Contract Report';
                    return e.name + ': ₹' + sal + ' (' + days + ')';
                }).join(', ');
                ctcHint.innerHTML = '<i class="ri-calculator-line text-primary me-1"></i> Contract Report: ' + details;
            } else {
                ctcHint.textContent = 'Total payable salary from Contract Report (based on days worked this month)';
            }
        }

        recalculateFromPercentage();
    }

    // Update client choices and sync employees (Billing % is derived from employees, NOT clients)
    function onClientsChanged() {
        const selectedClientIds = getSelectedValues(clientSelect, clientChoices);
        
        if (clientCountBadge) {
            clientCountBadge.textContent = selectedClientIds.length > 0 
                ? (selectedClientIds.length + ' client' + (selectedClientIds.length > 1 ? 's' : '') + ' selected')
                : '0 Clients Selected';
        }

        // Auto-fetch TDS (Statutory 10.00% under Section 194J)
        if (tdsPctInput && (!tdsPctInput.value || parseFloat(tdsPctInput.value) <= 0)) {
            tdsPctInput.value = '10.00';
        }

        syncEmployeesAndOptions();
    }

    // Synchronize available employees from server and exclude any employee already chosen on another investor
    function syncEmployeesAndOptions() {
        const selectedClientIds = getSelectedValues(clientSelect, clientChoices);
        const selectedInvesterId = investerSelect ? investerSelect.value : '';
        const currentSelectedEmpIds = getSelectedValues(employeeSelect, employeeChoices);
        const keepIds = currentSelectedEmpIds.length > 0 ? currentSelectedEmpIds : initialSelectedEmployeeIds;

        // Immediate local render first
        updateEmployeeOptions(selectedClientIds, keepIds);

        if (selectedClientIds.length > 0) {
            const params = new URLSearchParams();
            selectedClientIds.forEach(id => params.append('client_ids[]', id));
            if (selectedInvesterId) {
                params.append('invester_id', selectedInvesterId);
            }
            if (deploymentId) {
                params.append('deployment_id', deploymentId);
            }

            fetch('{{ route('admin.invester-deployments.client-employees') }}?' + params.toString(), {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data && data.status) {
                    // Exclude any employees deployed on another investor
                    if (Array.isArray(data.employees)) {
                        const serverEmpMap = new Map(data.employees.map(e => [e.id, e]));

                        // Update or filter allExternalEmployees:
                        // Replace or update employees for selected clients with server-validated list
                        allExternalEmployees = allExternalEmployees
                            .filter(emp => !selectedClientIds.includes(emp.client_id) || serverEmpMap.has(emp.id))
                            .map(emp => serverEmpMap.has(emp.id) ? { ...emp, ...serverEmpMap.get(emp.id) } : emp);

                        data.employees.forEach(emp => {
                            if (!allExternalEmployees.some(e => e.id === emp.id)) {
                                allExternalEmployees.push(emp);
                            }
                        });

                        // Re-render employee options with server validated list
                        updateEmployeeOptions(selectedClientIds, keepIds);
                    }
                }
            })
            .catch(err => {
                console.error('Error synchronizing client employees:', err);
            });
        }
    }

    // Financial Calculation Logic:
    // Employee CTC
    // Billing % / Margin %
    // TDS %
    // TDS Amount = Employee CTC * (TDS % / 100)
    // Margin In Rupees = Employee CTC * (Margin % / 100)
    // SZ Commission = Margin In Rupees * (SZ % / 100)
    // Investor Commission = Margin In Rupees * (Investor % / 100)
    function recalculateFromPercentage() {
        const employeeCtc = parseFloat(employeeCtcInput?.value) || 0;
        const billingPct = parseFloat(billingPctInput?.value) || 0;
        const marginPct = parseFloat(marginPctInput?.value) || (billingPct > 0 ? billingPct : 0);
        const tdsPct = parseFloat(tdsPctInput?.value) || 10;

        // Auto calculate Margin In Rupees
        const marginAmt = Math.round(employeeCtc * (marginPct / 100) * 100) / 100;
        if (marginAmtInput) {
            marginAmtInput.value = marginAmt > 0 ? marginAmt.toFixed(2) : (employeeCtc > 0 ? '0.00' : '');
        }

        // Auto calculate TDS Deduction Amount
        const tdsAmt = Math.round(employeeCtc * (tdsPct / 100) * 100) / 100;
        if (tdsAmtInput) {
            tdsAmtInput.value = tdsAmt > 0 ? tdsAmt.toFixed(2) : (employeeCtc > 0 ? '0.00' : '');
        }

        recalculateSplits();
    }

    // Two-way sync when Margin In Rupees is typed manually
    function recalculateFromAmount() {
        const employeeCtc = parseFloat(employeeCtcInput?.value) || 0;
        const marginAmt = parseFloat(marginAmtInput?.value) || 0;
        const tdsPct = parseFloat(tdsPctInput?.value) || 10;

        if (employeeCtc > 0) {
            const marginPct = Math.round((marginAmt / employeeCtc) * 10000) / 100;
            if (marginPctInput) {
                marginPctInput.value = marginPct.toFixed(2);
                marginPctInput.dataset.manuallyEdited = 'true';
            }
        }

        // TDS amount
        const tdsAmt = Math.round(employeeCtc * (tdsPct / 100) * 100) / 100;
        if (tdsAmtInput) {
            tdsAmtInput.value = tdsAmt > 0 ? tdsAmt.toFixed(2) : (employeeCtc > 0 ? '0.00' : '');
        }

        recalculateSplits();
    }

    function recalculateSplits() {
        const marginAmt = parseFloat(marginAmtInput?.value) || 0;
        const szPct = parseFloat(szPctInput?.value) || 70;
        const invPct = parseFloat(invPctInput?.value) || 30;

        const szAmt = Math.round(marginAmt * (szPct / 100) * 100) / 100;
        const invAmt = Math.round(marginAmt * (invPct / 100) * 100) / 100;

        if (szDisplay) szDisplay.textContent = formatCurrency(szAmt);
        if (invDisplay) invDisplay.textContent = formatCurrency(invAmt);
    }

    // Initialize Choices.js
    function initChoices() {
        if (typeof Choices !== 'undefined') {
            if (clientSelect && !clientSelect.dataset.choicesInitialized) {
                clientChoices = new Choices(clientSelect, {
                    removeItemButton: true,
                    searchEnabled: true,
                    shouldSort: false,
                    placeholder: true,
                    placeholderValue: 'Select Clients',
                    noChoicesText: 'All clients selected',
                    noResultsText: 'No clients found'
                });
                clientSelect.dataset.choicesInitialized = 'true';

                clientSelect.addEventListener('change', onClientsChanged);
            }

            if (employeeSelect && !employeeSelect.dataset.choicesInitialized) {
                employeeChoices = new Choices(employeeSelect, {
                    removeItemButton: true,
                    searchEnabled: true,
                    shouldSort: false,
                    placeholder: true,
                    placeholderValue: 'Select External Employees',
                    noChoicesText: 'All external employees selected for this client',
                    noResultsText: 'No external employees found for selected client(s)'
                });
                employeeSelect.dataset.choicesInitialized = 'true';

                employeeSelect.addEventListener('change', updateEmployeeSelectionSummary);
            }
        } else {
            // Fallback for native selects
            if (clientSelect) clientSelect.addEventListener('change', onClientsChanged);
            if (employeeSelect) employeeSelect.addEventListener('change', updateEmployeeSelectionSummary);
        }

        // When investor selection changes, re-sync employees to exclude any already chosen on another investor
        if (investerSelect) {
            investerSelect.addEventListener('change', syncEmployeesAndOptions);
        }

        // Preserve manually entered contract duration when editing an existing deployment
        if (deploymentId && contractDurationInput && contractDurationInput.value.trim() !== '') {
            contractDurationInput.dataset.manuallyEdited = 'true';
        }

        // Initial setup
        const activeClientIds = initialSelectedClientIds.length > 0
            ? initialSelectedClientIds
            : getSelectedValues(clientSelect, clientChoices);

        updateEmployeeOptions(activeClientIds, initialSelectedEmployeeIds);
        onClientsChanged();
    }

    // Event listeners
    if (contractDurationInput) {
        contractDurationInput.addEventListener('input', function() {
            this.dataset.manuallyEdited = 'true';
            if (resetDurationBtn) resetDurationBtn.style.display = 'inline';
        });
    }
    if (resetDurationBtn) {
        resetDurationBtn.addEventListener('click', function() {
            if (contractDurationInput) {
                delete contractDurationInput.dataset.manuallyEdited;
            }
            updateEmployeeSelectionSummary();
        });
    }
    if (employeeCtcInput) {
        employeeCtcInput.addEventListener('input', function() {
            this.dataset.manuallyEdited = 'true';
            recalculateFromPercentage();
        });
    }
    if (billingPctInput) {
        billingPctInput.addEventListener('input', function() {
            this.dataset.manuallyEdited = 'true';
            if (marginPctInput && (!marginPctInput.dataset.manuallyEdited || parseFloat(marginPctInput.value) === 0)) {
                marginPctInput.value = this.value;
            }
            recalculateFromPercentage();
        });
    }
    if (marginPctInput) {
        marginPctInput.addEventListener('input', function() {
            this.dataset.manuallyEdited = 'true';
            recalculateFromPercentage();
        });
    }
    if (tdsPctInput) {
        tdsPctInput.addEventListener('input', recalculateFromPercentage);
    }
    if (marginAmtInput) {
        marginAmtInput.addEventListener('input', recalculateFromAmount);
    }

    if (szPctInput) {
        szPctInput.addEventListener('input', function() {
            const sz = parseFloat(this.value) || 0;
            if (invPctInput) invPctInput.value = Math.max(0, (100 - sz)).toFixed(2);
            recalculateSplits();
        });
    }

    if (invPctInput) {
        invPctInput.addEventListener('input', function() {
            const inv = parseFloat(this.value) || 0;
            if (szPctInput) szPctInput.value = Math.max(0, (100 - inv)).toFixed(2);
            recalculateSplits();
        });
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initChoices);
    } else {
        initChoices();
    }
})();
</script>
@endpush
