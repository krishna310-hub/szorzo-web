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
        $monthly = (float) ($emp->monthly_gross ?? 0);
        $ctc = $monthly > 0 ? round($monthly * 12, 2) : 0;
        return [
            'id' => (int) $emp->id,
            'client_id' => (int) $emp->client_id,
            'client_name' => $emp->client?->client ?? '',
            'name' => $emp->employee_name,
            'employee_no' => $no,
            'designation' => $emp->designation ?? '',
            'monthly_gross' => $monthly,
            'ctc' => $ctc,
            'label' => $emp->employee_name . ' (' . $no . ($emp->designation ? ' - ' . $emp->designation : '') . ')' . ($emp->client ? ' - ' . $emp->client->client : ''),
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
                            <span class="badge bg-primary-subtle text-primary fs-11" id="client_billing_badge">
                                <i class="ri-percent-line me-1"></i> Auto-fetch Billing %
                            </span>
                        </div>
                        <select class="form-select @error('client_ids') is-invalid @enderror @error('client_id') is-invalid @enderror" 
                            name="client_ids[]" id="client_ids" multiple required>
                            @foreach($clients as $cli)
                                @php
                                    $billingVal = $cli->billing ? (float) $cli->billing->value : null;
                                    $isSelected = in_array((int)$cli->id, $selectedClientIds, true);
                                @endphp
                                <option value="{{ $cli->id }}" 
                                    data-billing="{{ $billingVal !== null ? $billingVal : '' }}" 
                                    {{ $isSelected ? 'selected' : '' }}>
                                    {{ $cli->client }} @if($billingVal !== null)({{ $billingVal }}% Billing)@endif
                                </option>
                            @endforeach
                        </select>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <small class="text-muted"><i class="ri-information-line me-1"></i> Multi-select client(s). External staff and billing rate will auto-update.</small>
                            <span id="client_count_badge" class="badge bg-light text-dark fs-11"></span>
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
                            <span class="badge bg-success-subtle text-success fs-11" id="employee_selected_badge">
                                0 External Employees Selected
                            </span>
                        </div>
                        <select class="form-select @error('employee_ids') is-invalid @enderror @error('employee_id') is-invalid @enderror" 
                            name="employee_ids[]" id="employee_ids" multiple required>
                            <!-- Populated dynamically via JS / AJAX based on selected clients -->
                        </select>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <small class="text-muted"><i class="ri-user-shared-line me-1"></i> Strictly external contract employees only.</small>
                            <span id="employee_ctc_sum_badge" class="badge bg-light text-primary fs-11"></span>
                        </div>
                        @error('employee_ids')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        @error('employee_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Mode of Engagement <span class="text-danger">*</span></label>
                        @php
                            $engagementModes = ['Contract', 'Contract to Hire', 'Permanent', 'Subcontract', 'Fixed Term'];
                            $currentMode = old('mode_of_engagement', $deployment->mode_of_engagement ?? 'Contract');
                        @endphp
                        <select class="form-select @error('mode_of_engagement') is-invalid @enderror" name="mode_of_engagement" required>
                            @foreach($engagementModes as $m)
                                <option value="{{ $m }}" {{ $currentMode == $m ? 'selected' : '' }}>{{ $m }}</option>
                            @endforeach
                        </select>
                        @error('mode_of_engagement')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Contract Duration</label>
                        <input type="text" class="form-control @error('contract_duration') is-invalid @enderror" 
                            name="contract_duration" 
                            value="{{ old('contract_duration', $deployment->contract_duration ?? '6 Months') }}" 
                            placeholder="e.g. 6 Months, 1 Year, Long-term">
                        @error('contract_duration')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Financial Rates, CTC, Billing %, TDS & Margin -->
                <div class="row pt-2 border-top mt-2">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Approved Rate Card (₹) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="number" step="0.01" min="0" class="form-control @error('approved_rate_card') is-invalid @enderror" 
                                name="approved_rate_card" id="approved_rate_card"
                                value="{{ old('approved_rate_card', $deployment->approved_rate_card ?? '200000') }}" 
                                placeholder="200000" required>
                        </div>
                        <small class="text-muted">Total billing rate agreed with client</small>
                        @error('approved_rate_card')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fw-semibold mb-0">Employee CTC (₹)</label>
                            <span class="badge bg-info-subtle text-info fs-11">Auto-calculated</span>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="number" step="0.01" min="0" class="form-control @error('employee_ctc') is-invalid @enderror" 
                                name="employee_ctc" id="employee_ctc"
                                value="{{ old('employee_ctc', $deployment->employee_ctc ?? '1790000') }}" 
                                placeholder="1790000">
                        </div>
                        <small class="text-muted" id="employee_ctc_hint">Aggregated annual cost of selected external staff</small>
                        @error('employee_ctc')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fw-semibold mb-0">Client Billing %</label>
                            <span class="badge bg-primary-subtle text-primary fs-11">Auto-fetched</span>
                        </div>
                        <div class="input-group">
                            <input type="number" step="0.01" min="0" max="100" class="form-control @error('billing_percentage') is-invalid @enderror" 
                                name="billing_percentage" id="billing_percentage"
                                value="{{ old('billing_percentage', $deployment->billing_percentage ?? '8.33') }}" 
                                placeholder="e.g. 8.33">
                            <span class="input-group-text">%</span>
                        </div>
                        <small class="text-muted">Auto-fetched from client module (e.g. 8.33%)</small>
                        @error('billing_percentage')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Target Margin %</label>
                        <div class="input-group">
                            <input type="number" step="0.01" min="0" max="100" class="form-control @error('margin_percentage') is-invalid @enderror" 
                                name="margin_percentage" id="margin_percentage"
                                value="{{ old('margin_percentage', $deployment->margin_percentage ?? '15') }}" 
                                placeholder="15">
                            <span class="input-group-text">%</span>
                        </div>
                        <small class="text-muted">Target margin percentage (editable)</small>
                        @error('margin_percentage')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fw-semibold mb-0">TDS %</label>
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
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fw-semibold mb-0">TDS Deduction (₹)</label>
                            <span class="badge bg-danger-subtle text-danger fs-11">Auto-calculated</span>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="number" step="0.01" min="0" class="form-control bg-light @error('tds_amount') is-invalid @enderror" 
                                name="tds_amount" id="tds_amount"
                                value="{{ old('tds_amount', $deployment->tds_amount ?? '20000.00') }}" 
                                placeholder="20000.00" readonly>
                        </div>
                        <small class="text-muted">Gross TDS: Rate Card × TDS %</small>
                        @error('tds_amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Highlighted Yellow Margin in Rupees (Directly matching Excel photo) -->
                    <div class="col-md-12 mb-3">
                        <div class="p-3 rounded-3" style="background: #fff8bd; border: 2px solid #ecd83c;">
                            <div class="row align-items-center">
                                <div class="col-md-5">
                                    <label class="form-label fw-bold text-dark fs-5 mb-0">
                                        <i class="ri-money-rupee-circle-fill text-warning me-1"></i> Margin In Rupees
                                    </label>
                                    <div class="small text-muted">Auto-calculated: Approved Rate Card × Margin % (editable)</div>
                                </div>
                                <div class="col-md-7">
                                    <div class="input-group input-group-lg">
                                        <span class="input-group-text bg-white fw-bold">₹</span>
                                        <input type="number" step="0.01" min="0" 
                                            class="form-control form-control-lg fw-bolder fs-4 text-dark bg-white @error('margin_amount') is-invalid @enderror" 
                                            name="margin_amount" id="margin_amount"
                                            value="{{ old('margin_amount', $deployment->margin_amount ?? '30000') }}" 
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
    const allExternalEmployees = @json($employeesJson);
    const initialSelectedClientIds = @json($selectedClientIds);
    const initialSelectedEmployeeIds = @json($selectedEmployeeIds);

    const clientSelect = document.getElementById('client_ids');
    const employeeSelect = document.getElementById('employee_ids');
    const rateCardInput = document.getElementById('approved_rate_card');
    const employeeCtcInput = document.getElementById('employee_ctc');
    const billingPctInput = document.getElementById('billing_percentage');
    const marginPctInput = document.getElementById('margin_percentage');
    const tdsPctInput = document.getElementById('tds_percentage');
    const tdsAmtInput = document.getElementById('tds_amount');
    const marginAmtInput = document.getElementById('margin_amount');
    const szPctInput = document.getElementById('szorzo_share_percentage');
    const invPctInput = document.getElementById('investor_share_percentage');
    const szDisplay = document.getElementById('sz_calc_display');
    const invDisplay = document.getElementById('inv_calc_display');
    const clientBillingBadge = document.getElementById('client_billing_badge');
    const clientCountBadge = document.getElementById('client_count_badge');
    const employeeSelectedBadge = document.getElementById('employee_selected_badge');
    const employeeCtcSumBadge = document.getElementById('employee_ctc_sum_badge');

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
                    ctc: emp.ctc,
                    monthly: emp.monthly_gross,
                    clientId: emp.client_id
                }
            }));
            employeeChoices.setChoices(choiceList, 'value', 'label', true);
        } else if (employeeSelect) {
            employeeSelect.innerHTML = '';
            filteredEmployees.forEach(emp => {
                const opt = document.createElement('option');
                opt.value = emp.id;
                opt.textContent = emp.label;
                opt.dataset.ctc = emp.ctc;
                opt.dataset.monthly = emp.monthly_gross;
                opt.dataset.clientId = emp.client_id;
                if (keepSet.has(emp.id)) {
                    opt.selected = true;
                }
                employeeSelect.appendChild(opt);
            });
        }

        updateEmployeeSelectionSummary();
    }

    // Update client billing percentage & badges
    function onClientsChanged() {
        const selectedClientIds = getSelectedValues(clientSelect, clientChoices);
        
        if (clientCountBadge) {
            clientCountBadge.textContent = selectedClientIds.length > 0 
                ? (selectedClientIds.length + ' client' + (selectedClientIds.length > 1 ? 's' : '') + ' selected')
                : 'No clients selected';
        }

        // Calculate auto-fetched billing % from selected clients
        const selectedClients = clientsData.filter(c => selectedClientIds.includes(c.id));
        const billingValues = selectedClients.map(c => c.billing).filter(v => v !== null && !isNaN(v));

        if (billingValues.length > 0) {
            const avgBilling = Math.round((billingValues.reduce((a, b) => a + b, 0) / billingValues.length) * 100) / 100;
            if (billingPctInput) {
                billingPctInput.value = avgBilling.toFixed(2);
            }
            if (marginPctInput && (!marginPctInput.dataset.manuallyEdited || parseFloat(marginPctInput.value) === 0)) {
                marginPctInput.value = avgBilling.toFixed(2);
            }
            if (clientBillingBadge) {
                clientBillingBadge.innerHTML = '<i class="ri-check-line me-1"></i> Auto-fetched: ' + avgBilling.toFixed(2) + '%';
            }
        } else {
            if (clientBillingBadge) {
                clientBillingBadge.innerHTML = '<i class="ri-percent-line me-1"></i> Default Staffing: 8.33%';
            }
        }

        // Auto-fetch TDS (Statutory 10.00% under Section 194J)
        if (tdsPctInput && (!tdsPctInput.value || parseFloat(tdsPctInput.value) <= 0)) {
            tdsPctInput.value = '10.00';
        }

        // Re-filter external employees for selected clients
        const currentSelectedEmpIds = getSelectedValues(employeeSelect, employeeChoices);
        updateEmployeeOptions(selectedClientIds, currentSelectedEmpIds.length > 0 ? currentSelectedEmpIds : initialSelectedEmployeeIds);

        // Recalculate financial figures
        recalculateFromPercentage();
    }

    // Update Employee Selection Count & Aggregated CTC
    function updateEmployeeSelectionSummary() {
        const selectedEmpIds = getSelectedValues(employeeSelect, employeeChoices);
        const selectedEmployees = allExternalEmployees.filter(emp => selectedEmpIds.includes(emp.id));

        if (employeeSelectedBadge) {
            employeeSelectedBadge.textContent = selectedEmployees.length + ' External Employee' + (selectedEmployees.length !== 1 ? 's' : '') + ' Selected';
        }

        // Aggregate CTC
        const totalCtc = selectedEmployees.reduce((sum, emp) => sum + (Number(emp.ctc) || 0), 0);
        if (employeeCtcSumBadge) {
            employeeCtcSumBadge.textContent = totalCtc > 0 ? ('Total CTC: ' + formatCurrency(totalCtc)) : '';
        }

        // Auto-update Employee CTC input if not manually overwritten
        if (selectedEmployees.length > 0 && employeeCtcInput && !employeeCtcInput.dataset.manuallyEdited) {
            employeeCtcInput.value = totalCtc > 0 ? totalCtc.toFixed(2) : '';
        }

        // If rate card is empty, prefill with total CTC or standard billing
        if (rateCardInput && (!rateCardInput.value || parseFloat(rateCardInput.value) <= 0) && totalCtc > 0) {
            // Suggest monthly billing rate (CTC / 12 * 1.15) or standard
            const monthlyBilling = Math.round((totalCtc / 12) * 100) / 100;
            if (monthlyBilling > 0) {
                rateCardInput.value = monthlyBilling.toFixed(2);
            }
        }

        recalculateFromPercentage();
    }

    // Financial Calculation Logic:
    // Rate Card
    // Billing % / Margin %
    // TDS %
    // TDS Amount = Rate Card * (TDS % / 100)
    // Margin In Rupees = Rate Card * (Margin % / 100)
    // SZ Commission = Margin In Rupees * (SZ % / 100)
    // Investor Commission = Margin In Rupees * (Investor % / 100)
    function recalculateFromPercentage() {
        const rateCard = parseFloat(rateCardInput?.value) || 0;
        const billingPct = parseFloat(billingPctInput?.value) || 0;
        const marginPct = parseFloat(marginPctInput?.value) || (billingPct > 0 ? billingPct : 15);
        const tdsPct = parseFloat(tdsPctInput?.value) || 10;

        // Auto calculate Margin In Rupees
        const marginAmt = Math.round(rateCard * (marginPct / 100) * 100) / 100;
        if (marginAmtInput) {
            marginAmtInput.value = marginAmt > 0 ? marginAmt.toFixed(2) : '0.00';
        }

        // Auto calculate TDS Deduction Amount
        const tdsAmt = Math.round(rateCard * (tdsPct / 100) * 100) / 100;
        if (tdsAmtInput) {
            tdsAmtInput.value = tdsAmt > 0 ? tdsAmt.toFixed(2) : '0.00';
        }

        recalculateSplits();
    }

    // Two-way sync when Margin In Rupees is typed manually
    function recalculateFromAmount() {
        const rateCard = parseFloat(rateCardInput?.value) || 0;
        const marginAmt = parseFloat(marginAmtInput?.value) || 0;
        const tdsPct = parseFloat(tdsPctInput?.value) || 10;

        if (rateCard > 0) {
            const marginPct = Math.round((marginAmt / rateCard) * 10000) / 100;
            if (marginPctInput) {
                marginPctInput.value = marginPct.toFixed(2);
                marginPctInput.dataset.manuallyEdited = 'true';
            }
        }

        // TDS amount
        const tdsAmt = Math.round(rateCard * (tdsPct / 100) * 100) / 100;
        if (tdsAmtInput) {
            tdsAmtInput.value = tdsAmt > 0 ? tdsAmt.toFixed(2) : '0.00';
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

        // Initial setup
        const activeClientIds = initialSelectedClientIds.length > 0
            ? initialSelectedClientIds
            : getSelectedValues(clientSelect, clientChoices);

        updateEmployeeOptions(activeClientIds, initialSelectedEmployeeIds);
        onClientsChanged();
    }

    // Event listeners
    if (rateCardInput) {
        rateCardInput.addEventListener('input', recalculateFromPercentage);
    }
    if (billingPctInput) {
        billingPctInput.addEventListener('input', function() {
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
    if (employeeCtcInput) {
        employeeCtcInput.addEventListener('input', function() {
            this.dataset.manuallyEdited = 'true';
        });
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
