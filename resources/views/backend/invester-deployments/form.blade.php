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
                        <label class="form-label">Invester <span class="text-danger">*</span></label>
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
                        <label class="form-label">Status <span class="text-danger">*</span></label>
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

    <!-- Section: Employment Details (Matching Photo Layout) -->
    <div class="col-12 mb-3">
        <div class="card border shadow-none">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0">
                    <i class="ri-briefcase-4-line me-1 text-primary"></i> 2. Employment Details
                </h6>
                <span class="badge bg-warning-subtle text-warning fw-semibold fs-12">
                    <i class="ri-calculator-line me-1"></i> Live Margin & Commission Calculation
                </span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Employee Name <span class="text-danger">*</span></label>
                        @php
                            $selectedEmployeeId = old('employee_id', $deployment->employee_id ?? '');
                        @endphp
                        <select class="form-select @error('employee_id') is-invalid @enderror" name="employee_id" id="employee_id" required>
                            <option value="">Select Employee</option>
                            @foreach($employees as $emp)
                                @php
                                    $empNo = $emp->employee_no ?: ('SZ' . str_pad($emp->id, 3, '0', STR_PAD_LEFT));
                                    $empLabel = $emp->employee_name . ' (' . $empNo . ($emp->designation ? ' - ' . $emp->designation : '') . ')';
                                @endphp
                                <option value="{{ $emp->id }}" {{ $selectedEmployeeId == $emp->id ? 'selected' : '' }}>
                                    {{ $empLabel }}
                                </option>
                            @endforeach
                        </select>
                        @error('employee_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Deployed To (Client Name) <span class="text-danger">*</span></label>
                        @php
                            $selectedClientId = old('client_id', $deployment->client_id ?? '');
                        @endphp
                        <select class="form-select @error('client_id') is-invalid @enderror" name="client_id" id="client_id" required>
                            <option value="">Select Client</option>
                            @foreach($clients as $cli)
                                <option value="{{ $cli->id }}" {{ $selectedClientId == $cli->id ? 'selected' : '' }}>
                                    {{ $cli->client }}
                                </option>
                            @endforeach
                        </select>
                        @error('client_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Mode of Engagement <span class="text-danger">*</span></label>
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
                        <label class="form-label">Contract Duration</label>
                        <input type="text" class="form-control @error('contract_duration') is-invalid @enderror" 
                            name="contract_duration" 
                            value="{{ old('contract_duration', $deployment->contract_duration ?? '6 Months') }}" 
                            placeholder="e.g. 6 Months, 1 Year, Long-term">
                        @error('contract_duration')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row pt-2 border-top mt-2">
                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-semibold">Approved Rate Card (₹) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="number" step="0.01" min="0" class="form-control @error('approved_rate_card') is-invalid @enderror" 
                                name="approved_rate_card" id="approved_rate_card"
                                value="{{ old('approved_rate_card', $deployment->approved_rate_card ?? '200000') }}" 
                                placeholder="200000" required>
                        </div>
                        <small class="text-muted">Client billing rate</small>
                        @error('approved_rate_card')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-semibold">Employee CTC (₹)</label>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="number" step="0.01" min="0" class="form-control @error('employee_ctc') is-invalid @enderror" 
                                name="employee_ctc" id="employee_ctc"
                                value="{{ old('employee_ctc', $deployment->employee_ctc ?? '1790000') }}" 
                                placeholder="1790000">
                        </div>
                        <small class="text-muted">Employee cost</small>
                        @error('employee_ctc')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-semibold">Margin %</label>
                        <div class="input-group">
                            <input type="number" step="0.01" min="0" max="100" class="form-control @error('margin_percentage') is-invalid @enderror" 
                                name="margin_percentage" id="margin_percentage"
                                value="{{ old('margin_percentage', $deployment->margin_percentage ?? '15') }}" 
                                placeholder="15">
                            <span class="input-group-text">%</span>
                        </div>
                        <small class="text-muted">Target margin percentage</small>
                        @error('margin_percentage')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-semibold">TDS %</label>
                        <div class="input-group">
                            <input type="number" step="0.01" min="0" max="100" class="form-control @error('tds_percentage') is-invalid @enderror" 
                                name="tds_percentage" id="tds_percentage"
                                value="{{ old('tds_percentage', $deployment->tds_percentage ?? '10') }}" 
                                placeholder="10">
                            <span class="input-group-text">%</span>
                        </div>
                        <small class="text-muted">Applicable TDS %</small>
                        @error('tds_percentage')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Highlighted Yellow Margin in Rupees (Directly matching Excel photo) -->
                    <div class="col-md-12 mb-3">
                        <div class="p-3 rounded-3" style="background: #fff8bd; border: 2px solid #ecd83c;">
                            <div class="row align-items-center">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold text-dark fs-5 mb-0">
                                        <i class="ri-money-rupee-circle-fill text-warning me-1"></i> Margin In Rupees
                                    </label>
                                    <div class="small text-muted">Auto-calculated or manually specified</div>
                                </div>
                                <div class="col-md-8">
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
                        <label class="form-label">Deployment Start Date</label>
                        <input type="date" class="form-control" name="start_date" 
                            value="{{ old('start_date', isset($deployment) && $deployment->start_date ? $deployment->start_date->format('Y-m-d') : '') }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Deployment End Date</label>
                        <input type="date" class="form-control" name="end_date" 
                            value="{{ old('end_date', isset($deployment) && $deployment->end_date ? $deployment->end_date->format('Y-m-d') : '') }}">
                    </div>

                    <div class="col-12 mb-3">
                        <label class="form-label">Deployment Notes / Engagement Remarks</label>
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
    const rateCardInput = document.getElementById('approved_rate_card');
    const marginPctInput = document.getElementById('margin_percentage');
    const marginAmtInput = document.getElementById('margin_amount');
    const szPctInput = document.getElementById('szorzo_share_percentage');
    const invPctInput = document.getElementById('investor_share_percentage');
    const szDisplay = document.getElementById('sz_calc_display');
    const invDisplay = document.getElementById('inv_calc_display');

    function formatCurrency(val) {
        return '₹ ' + (Number(val) || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function recalculateFromPercentage() {
        const rateCard = parseFloat(rateCardInput.value) || 0;
        const marginPct = parseFloat(marginPctInput.value) || 0;
        const marginAmt = Math.round(rateCard * (marginPct / 100) * 100) / 100;
        marginAmtInput.value = marginAmt > 0 ? marginAmt.toFixed(2) : '0.00';
        recalculateSplits();
    }

    function recalculateFromAmount() {
        const rateCard = parseFloat(rateCardInput.value) || 0;
        const marginAmt = parseFloat(marginAmtInput.value) || 0;
        if (rateCard > 0) {
            const marginPct = Math.round((marginAmt / rateCard) * 10000) / 100;
            marginPctInput.value = marginPct.toFixed(2);
        }
        recalculateSplits();
    }

    function recalculateSplits() {
        const marginAmt = parseFloat(marginAmtInput.value) || 0;
        const szPct = parseFloat(szPctInput.value) || 70;
        const invPct = parseFloat(invPctInput.value) || 30;

        const szAmt = Math.round(marginAmt * (szPct / 100) * 100) / 100;
        const invAmt = Math.round(marginAmt * (invPct / 100) * 100) / 100;

        if (szDisplay) szDisplay.textContent = formatCurrency(szAmt);
        if (invDisplay) invDisplay.textContent = formatCurrency(invAmt);
    }

    if (rateCardInput) rateCardInput.addEventListener('input', recalculateFromPercentage);
    if (marginPctInput) marginPctInput.addEventListener('input', recalculateFromPercentage);
    if (marginAmtInput) marginAmtInput.addEventListener('input', recalculateFromAmount);

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

    // Run on initial load
    recalculateSplits();
})();
</script>
@endpush

