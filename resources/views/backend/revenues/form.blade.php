@php
    $editing = isset($revenue);
    $selectedInvoiceType = old('invoice_type', request()->filled('contract_month') ? 'contract' : 'fte');
    $contractClients = $contractClients ?? collect();
    $contractCandidates = $contractCandidates ?? collect();
@endphp
<div class="row">
    @if(!$editing)
    <div class="col-12 mb-3">
        <label class="form-label d-block fw-semibold">Revenue Type <span class="text-danger">*</span></label>
        <div class="form-check form-check-inline">
            <input class="form-check-input revenue-type" type="radio" name="invoice_type" id="type_fte" value="fte" @checked($selectedInvoiceType === 'fte')>
            <label class="form-check-label" for="type_fte">FTE</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input revenue-type" type="radio" name="invoice_type" id="type_contract" value="contract" @checked($selectedInvoiceType === 'contract')>
            <label class="form-check-label" for="type_contract">Contract</label>
        </div>
        @error('invoice_type')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>

    {{-- FTE candidate selection --}}
    <div class="col-md-6 mb-3" id="fte_candidate_field">
        <label class="form-label fw-semibold">Offer Accepted Candidate <span class="text-danger">*</span></label>
        <select name="candidate_id" id="candidate_id" class="form-select" required>
            <option value="">Select candidate</option>
            @foreach($candidates as $candidate)
                <option value="{{ $candidate->id }}"
                    data-client="{{ $candidate->client?->client }}"
                    data-client-id="{{ $candidate->client_id }}"
                    data-base="{{ $candidate->onboarding_ctc }}"
                    data-billing="{{ $candidate->clientRequirement?->billing?->value }}"
                    @selected(old('candidate_id') == $candidate->id)>
                    {{ $candidate->candidate_name }} — {{ $candidate->client?->client ?? 'No client' }} — {{ $candidate->clientRequirement?->billing?->value ?? 0 }}%
                </option>
            @endforeach
        </select>
        <small class="text-muted">Only one FTE candidate can be selected.</small>
        @error('candidate_id')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>

    {{-- Contract Report Month --}}
    <div class="col-md-6 mb-3 d-none" id="contract_month_field">
        <label class="form-label fw-semibold">Contract Report Month <span class="text-danger">*</span></label>
        <div class="input-group">
            <input type="month" name="contract_month" id="contract_month" class="form-control"
                value="{{ old('contract_month', $contractMonth ?? now()->format('Y-m')) }}">
            <button type="button" class="btn btn-outline-primary" id="load_contract_month">Load Month</button>
        </div>
        @error('contract_month')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    </div>

    {{-- Contract Client Dropdown (Single Select) --}}
    <div class="col-md-6 mb-3 d-none" id="contract_client_field">
        <label class="form-label fw-semibold">Client Name <span class="text-danger">*</span></label>
        <select name="client_id" id="contract_client_id" class="form-select">
            <option value="">Select client for this month</option>
            @foreach($contractClients as $client)
                <option value="{{ $client->id }}"
                    data-client="{{ $client->client }}"
                    data-address="{{ $client->invoice_address }}"
                    data-gst="{{ $client->invoice_gst }}"
                    @selected(old('client_id') == $client->id)>
                    {{ $client->client }}
                </option>
            @endforeach
        </select>
        <small class="text-muted">Clients who have contract candidates in {{ \Carbon\CarbonImmutable::createFromFormat('!Y-m', $contractMonth ?? now()->format('Y-m'))->format('F Y') }}.</small>
        @error('client_id')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>

    {{-- Contract Candidate Multiple Select --}}
    <div class="col-12 mb-3 d-none" id="contract_candidates_field">
        <label class="form-label fw-semibold">Contract Candidate(s) <span class="text-danger">*</span></label>
        <div id="contract_candidate_picker" class="contract-candidate-picker">
            @foreach($contractCandidates as $candidate)
                <label class="contract-candidate-option" data-client-id="{{ $candidate->client_id }}" data-candidate-id="{{ $candidate->id }}">
                    <input type="checkbox" class="form-check-input contract-candidate-check"
                        value="{{ $candidate->id }}"
                        @checked(is_array(old('candidate_ids')) && in_array($candidate->id, old('candidate_ids')))>
                    <span class="contract-candidate-details">
                        <strong>{{ $candidate->candidate_name }}</strong>
                        <span>{{ $candidate->jobRole?->job_role ?? 'Candidate' }}</span>
                        <span>Salary: ₹{{ number_format((float) ($candidate->contract_invoice_base ?? 0), 2) }}</span>
                        @if($candidate->contract_is_hourly ?? false)
                            <span>Rate: ₹{{ number_format((float) ($candidate->contract_hourly_salary ?? 0), 2) }}/hr</span>
                            <span>Hours: {{ number_format((float) ($candidate->contract_worked_hours ?? 0), 2) }}</span>
                        @endif
                        <span>Billing: {{ number_format((float) ($candidate->contract_billing_percentage ?? 0), 2) }}%</span>
                        <span>Revenue: ₹{{ number_format((float) ($candidate->contract_invoice_service ?? 0), 2) }}</span>
                        @if(($candidate->contract_is_hourly ?? false) && (float) ($candidate->contract_worked_hours ?? 0) <= 0)
                            <span class="contract-hours-warning"><i class="ri-error-warning-line"></i> Enter worked hours in Contract Report to calculate this candidate.</span>
                        @endif
                    </span>
                </label>
            @endforeach
            <div id="contract_candidates_empty" class="text-muted text-center py-3 d-none">No candidates available for this client.</div>
        </div>
        <select name="candidate_ids[]" id="contract_candidate_ids" class="d-none" multiple aria-hidden="true" tabindex="-1">
            @foreach($contractCandidates as $candidate)
                <option value="{{ $candidate->id }}"
                    data-client-id="{{ $candidate->client_id }}"
                    data-client="{{ $candidate->client?->client }}"
                    data-base="{{ $candidate->contract_invoice_base ?? 0 }}"
                    data-service="{{ $candidate->contract_invoice_service ?? 0 }}"
                    data-billing="{{ $candidate->contract_billing_percentage ?? ($candidate->clientRequirement?->billing?->value ?? 0) }}"
                    @selected(is_array(old('candidate_ids')) && in_array($candidate->id, old('candidate_ids')))>
                    {{ $candidate->candidate_name }} — {{ $candidate->jobRole?->job_role ?? 'Candidate' }} — Salary: ₹{{ number_format((float) ($candidate->contract_invoice_base ?? 0), 2) }} — Billing: {{ number_format((float) ($candidate->contract_billing_percentage ?? 0), 2) }}% (Revenue: ₹{{ number_format((float) ($candidate->contract_invoice_service ?? 0), 2) }})
                </option>
            @endforeach
        </select>
        <div class="d-flex justify-content-between align-items-center mt-1">
            <small class="text-muted"><i class="ri-information-line"></i> Select one or more candidates. <span id="contract_selection_summary"></span></small>
            <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none" id="btn_select_all_candidates">Select All for this Client</button>
        </div>
        @error('candidate_ids')<div class="text-danger small">{{ $message }}</div>@enderror
        @error('candidate_ids.*')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>
    @else
        <input type="hidden" name="candidate_id" value="{{ $revenue->candidate_id }}">
        <div class="col-md-6 mb-3">
            <label class="form-label fw-semibold">Candidate(s)</label>
            <input class="form-control" value="{{ $revenue->candidates->isNotEmpty() ? $revenue->candidates->pluck('candidate_name')->join(', ') : ($revenue->candidate?->candidate_name ?? '-') }}" disabled>
        </div>
    @endif

    {{-- FTE Client Name Field --}}
    <div class="col-md-6 mb-3" id="fte_client_field">
        <label class="form-label fw-semibold">Client Name <span class="text-danger">*</span></label>
        <input name="client_name" id="client_name" class="form-control" @if($editing) required @endif value="{{ old('client_name', $revenue->client_name ?? '') }}">
        @error('client_name')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label fw-semibold">Invoice Number <span class="text-danger">*</span></label>
        <input name="invoice_number" class="form-control" required value="{{ old('invoice_number', $revenue->invoice_number ?? $invoiceNumber ?? '') }}">
        <small class="text-muted">Generated initially and editable before saving.</small>
        @error('invoice_number')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-3 mb-3">
        <label class="form-label fw-semibold">Invoice Date <span class="text-danger">*</span></label>
        <input type="date" name="invoice_date" class="form-control" required value="{{ old('invoice_date', isset($revenue) ? $revenue->invoice_date->format('Y-m-d') : now()->format('Y-m-d')) }}">
    </div>

    <div class="col-md-3 mb-3">
        <label class="form-label fw-semibold">SZ Universe Number</label>
        <input name="universe_number" class="form-control" value="{{ old('universe_number', $revenue->universe_number ?? '786') }}">
    </div>

    <div class="col-md-8 mb-3">
        <label class="form-label fw-semibold">Client Address</label>
        <textarea name="client_address" id="client_address" class="form-control" rows="3">{{ old('client_address', $revenue->client_address ?? '') }}</textarea>
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label fw-semibold">Client GST Number</label>
        <input name="client_gst_number" id="client_gst_number" class="form-control" value="{{ old('client_gst_number', $revenue->client_gst_number ?? '') }}">
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label fw-semibold" id="base_amount_label">Onboarding CTC (₹)</label>
        <input type="number" step="0.01" min="0" name="onboarding_ctc" id="onboarding_ctc" class="form-control bg-light" readonly value="{{ old('onboarding_ctc', $revenue->onboarding_ctc ?? '') }}">
        <small class="text-muted" id="base_amount_help">Taken from the selected candidate.</small>
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label fw-semibold">Billing Percentage <span class="text-danger">*</span></label>
        <input type="number" step="0.01" min="0" max="100" name="billing_percentage" id="billing_percentage" class="form-control calc" @if($editing) required @endif value="{{ old('billing_percentage', $revenue->billing_percentage ?? '') }}">
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label fw-semibold">GST Percentage <span class="text-danger">*</span></label>
        <input type="number" step="0.01" min="0" max="100" name="gst_percentage" id="gst_percentage" class="form-control calc" required value="{{ old('gst_percentage', $revenue->gst_percentage ?? 18) }}">
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label fw-semibold">Invoice Amount (₹) <span class="text-danger">*</span></label>
        <input type="number" step="0.01" min="0" name="service_amount" id="service_amount" class="form-control bg-light" @if($editing) required @endif value="{{ old('service_amount', $revenue->service_amount ?? '') }}">
        <small class="text-muted">Calculated from the selected candidate(s) and mapped billing percentage.</small>
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label fw-semibold">GST</label>
        <input id="gst_preview" class="form-control bg-light" disabled>
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label fw-semibold">Invoice Total</label>
        <input id="total_preview" class="form-control fw-bold text-success bg-light" disabled>
    </div>

    <div class="col-12 mb-3">
        <label class="form-label fw-semibold">Notes</label>
        <textarea name="notes" class="form-control" rows="2">{{ old('notes', $revenue->notes ?? '') }}</textarea>
    </div>
</div>

@push('style')
<style>
.contract-candidate-picker { border: 1px solid #d8dce3; border-radius: .375rem; max-height: 260px; overflow-y: auto; padding: 8px; background: #fff; }
.contract-candidate-option { display: flex; align-items: flex-start; gap: 12px; margin: 0 0 7px; padding: 11px 13px; border: 1px solid #e8eaf0; border-radius: 7px; cursor: pointer; transition: border-color .15s, background-color .15s, box-shadow .15s; }
.contract-candidate-option:last-of-type { margin-bottom: 0; }
.contract-candidate-option:hover { border-color: #9db9ef; background: #f8faff; }
.contract-candidate-option.is-selected { border-color: #376fd0; background: #edf4ff; box-shadow: 0 0 0 1px rgba(55,111,208,.12); }
.contract-candidate-option .form-check-input { flex: 0 0 auto; margin-top: 3px; }
.contract-candidate-details { display: flex; flex-wrap: wrap; align-items: center; gap: 3px 14px; width: 100%; color: #647086; }
.contract-candidate-details strong { flex-basis: 100%; color: #20283a; }
.contract-hours-warning { flex-basis: 100%; color: #d97706; font-weight: 600; }
</style>
@endpush

<div class="text-end">
    <a href="{{ route('admin.revenues.index') }}" class="btn btn-light me-2">Cancel</a>
    <button class="btn btn-primary">{{ $editing ? 'Update Invoice' : 'Generate Invoice' }}</button>
</div>

@push('script')
<script>
function calculateRevenue() {
    const gstRate = Number($('#gst_percentage').val() || 0);
    const revenue = Number($('#service_amount').val() || 0);
    const gst = Math.round((revenue * gstRate / 100 + Number.EPSILON) * 100) / 100;
    const total = Math.round((revenue + gst + Number.EPSILON) * 100) / 100;
    $('#gst_preview').val('₹' + gst.toFixed(2));
    $('#total_preview').val('₹' + total.toFixed(2));
}

$(function () {
    const fteCandidateSelect = document.getElementById('candidate_id');
    const contractClientSelect = document.getElementById('contract_client_id');
    const contractCandidateSelect = document.getElementById('contract_candidate_ids');
    const contractCandidateRows = document.querySelectorAll('.contract-candidate-option');

    function selectedType() {
        return $('input[name="invoice_type"]:checked').val() || 'fte';
    }

    function toggleRevenueType() {
        const isContract = selectedType() === 'contract';

        // Toggle FTE vs Contract fields
        $('#fte_candidate_field').toggleClass('d-none', isContract);
        $('#fte_client_field').toggleClass('d-none', isContract);
        $('#contract_month_field').toggleClass('d-none', !isContract);
        $('#contract_client_field').toggleClass('d-none', !isContract);
        $('#contract_candidates_field').toggleClass('d-none', !isContract);

        $('#candidate_id').prop('required', !isContract).prop('disabled', isContract);
        $('#contract_month').prop('required', isContract).prop('disabled', !isContract);
        $('#contract_client_id').prop('required', isContract).prop('disabled', !isContract);
        $('#contract_candidate_ids').prop('required', isContract).prop('disabled', !isContract);

        $('#billing_percentage, #service_amount').prop('readonly', true);
        $('#billing_percentage').attr('max', isContract ? 10000 : 100);
        $('#base_amount_label').text(isContract ? 'Contract Payable Salary (₹)' : 'Onboarding CTC (₹)');
        $('#base_amount_help').text(isContract ? 'Sum of selected candidates payable salaries.' : 'Taken from the selected FTE candidate.');

        if (isContract) {
            filterContractCandidates(true);
        } else {
            updateFteCandidateSelection();
        }
    }

    function updateFteCandidateSelection() {
        const option = fteCandidateSelect?.options[fteCandidateSelect.selectedIndex];
        const roundMoney = value => Math.round((value + Number.EPSILON) * 100) / 100;
        const gstRate = Number($('#gst_percentage').val() || 0);
        const base = Number(option?.dataset.base || 0);
        const billing = Number(option?.dataset.billing || 0);
        const service = roundMoney(base * billing / 100);

        $('#client_name').val(option?.dataset.client || '');
        $('#onboarding_ctc').val(base ? base.toFixed(2) : '');
        $('#billing_percentage').val(billing ? billing.toFixed(2) : '');
        $('#service_amount').val(service ? service.toFixed(2) : '0.00');
        calculateRevenue();
    }

    function filterContractCandidates(preserveSelection = false) {
        const selectedOption = contractClientSelect?.options[contractClientSelect.selectedIndex];
        const selectedClientId = selectedOption?.value;
        const clientName = selectedOption?.dataset.client || '';
        const clientAddress = selectedOption?.dataset.address || '';
        const clientGst = selectedOption?.dataset.gst || '';

        // Auto-fill client details
        $('#client_name').val(clientName);
        if (clientAddress) $('#client_address').val(clientAddress);
        if (clientGst) $('#client_gst_number').val(clientGst);

        if (!contractCandidateSelect) return;

        let matchingOptions = [];
        for (let i = 0; i < contractCandidateSelect.options.length; i++) {
            const opt = contractCandidateSelect.options[i];
            const matches = selectedClientId && String(opt.dataset.clientId) === String(selectedClientId);
            opt.hidden = !matches;
            opt.disabled = !matches;
            if (matches) {
                matchingOptions.push(opt);
                opt.selected = preserveSelection && opt.selected;
            } else {
                opt.selected = false;
            }
        }

        let visibleRows = 0;
        contractCandidateRows.forEach(row => {
            const matches = selectedClientId && String(row.dataset.clientId) === String(selectedClientId);
            const checkbox = row.querySelector('.contract-candidate-check');
            const option = [...contractCandidateSelect.options].find(opt => String(opt.value) === String(row.dataset.candidateId));
            row.classList.toggle('d-none', !matches);
            checkbox.disabled = !matches;
            checkbox.checked = Boolean(matches && option?.selected);
            row.classList.toggle('is-selected', checkbox.checked);
            if (matches) visibleRows++;
        });
        $('#contract_candidates_empty').toggleClass('d-none', visibleRows > 0);

        updateContractCandidateSums();
    }

    function updateContractCandidateSums() {
        if (!contractCandidateSelect) return;

        const roundMoney = value => Math.round((value + Number.EPSILON) * 100) / 100;
        let sumBase = 0;
        let sumService = 0;
        let billings = [];

        for (let i = 0; i < contractCandidateSelect.selectedOptions.length; i++) {
            const opt = contractCandidateSelect.selectedOptions[i];
            const base = Number(opt.dataset.base || 0);
            const service = opt.dataset.service !== undefined
                ? Number(opt.dataset.service || 0)
                : roundMoney(base * Number(opt.dataset.billing || 0) / 100);
            sumBase += base;
            sumService += service;
            billings.push(Number(opt.dataset.billing || 0));
        }

        sumBase = roundMoney(sumBase);
        sumService = roundMoney(sumService);

        const combinedBilling = roundMoney(billings.reduce((total, billing) => total + billing, 0));

        $('#onboarding_ctc').val(sumBase ? sumBase.toFixed(2) : '0.00');
        $('#billing_percentage').val(combinedBilling ? combinedBilling.toFixed(2) : '0.00');
        $('#service_amount').val(sumService ? sumService.toFixed(2) : '0.00');
        $('#contract_selection_summary').text(
            contractCandidateSelect.selectedOptions.length
                ? contractCandidateSelect.selectedOptions.length + ' candidate(s) selected.'
                : 'No candidates selected.'
        );
        calculateRevenue();
    }

    $('.revenue-type').on('change', toggleRevenueType);

    $('#load_contract_month').on('click', function () {
        const month = $('#contract_month').val();
        if (!month) {
            toastr.error('Select a contract report month.');
            return;
        }
        const url = new URL(@json(route('admin.revenues.create')), window.location.origin);
        url.searchParams.set('contract_month', month);
        window.location.href = url.toString();
    });

    $('#candidate_id').on('change', updateFteCandidateSelection);

    $('#contract_client_id').on('change', function () {
        filterContractCandidates(false);
    });

    $('#contract_candidate_ids').on('change', function () {
        updateContractCandidateSums();
    });

    $('.contract-candidate-check').on('change', function () {
        const option = [...contractCandidateSelect.options].find(opt => String(opt.value) === String(this.value));
        if (option) option.selected = this.checked;
        $(this).closest('.contract-candidate-option').toggleClass('is-selected', this.checked);
        updateContractCandidateSums();
    });

    $('#btn_select_all_candidates').on('click', function () {
        const selectedClientId = contractClientSelect?.value;
        if (!selectedClientId) {
            toastr.info('Please select a client first.');
            return;
        }
        for (let i = 0; i < contractCandidateSelect.options.length; i++) {
            const opt = contractCandidateSelect.options[i];
            if (String(opt.dataset.clientId) === String(selectedClientId)) {
                opt.selected = true;
            }
        }
        contractCandidateRows.forEach(row => {
            if (String(row.dataset.clientId) === String(selectedClientId)) {
                const checkbox = row.querySelector('.contract-candidate-check');
                checkbox.checked = true;
                row.classList.add('is-selected');
            }
        });
        updateContractCandidateSums();
    });

    $('.calc, #service_amount').on('input', function () {
        @if(!$editing)
        if (selectedType() === 'contract') {
            updateContractCandidateSums();
        } else {
            updateFteCandidateSelection();
        }
        return;
        @endif

        if (this.id === 'billing_percentage') {
            $('#service_amount').val((Number($('#onboarding_ctc').val() || 0) * Number($('#billing_percentage').val() || 0) / 100).toFixed(2));
        }
        calculateRevenue();
    });

    calculateRevenue();
    @if(!$editing)
        toggleRevenueType();
        // If a client was pre-selected or only one client exists, filter immediately
        if (contractClientSelect && contractClientSelect.value) {
            filterContractCandidates(true);
        }
    @endif
});
</script>
@endpush
