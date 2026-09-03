@php
    $editing = isset($revenue);
    $selectedInvoiceType = old('invoice_type', request()->filled('contract_month') ? 'contract' : 'fte');
@endphp
<div class="row">
    @if(!$editing)
    <div class="col-12 mb-3">
        <label class="form-label d-block">Revenue Type <span class="text-danger">*</span></label>
        <div class="form-check form-check-inline"><input class="form-check-input revenue-type" type="radio" name="invoice_type" id="type_fte" value="fte" @checked($selectedInvoiceType === 'fte')><label class="form-check-label" for="type_fte">FTE</label></div>
        <div class="form-check form-check-inline"><input class="form-check-input revenue-type" type="radio" name="invoice_type" id="type_contract" value="contract" @checked($selectedInvoiceType === 'contract')><label class="form-check-label" for="type_contract">Contract</label></div>
        @error('invoice_type')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-3" id="fte_candidate_field">
        <label class="form-label">Offer Accepted Candidate <span class="text-danger">*</span></label>
        <select name="candidate_id" id="candidate_id" class="form-select" required>
            <option value="">Select candidate</option>
            @foreach($candidates as $candidate)
                <option value="{{ $candidate->id }}"
                    data-client="{{ $candidate->client?->client }}"
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
    <div class="col-md-6 mb-3 d-none" id="contract_candidates_field">
        <label class="form-label">Contract Report Month <span class="text-danger">*</span></label>
        <div class="input-group mb-3">
            <input type="month" name="contract_month" id="contract_month" class="form-control"
                value="{{ old('contract_month', $contractMonth ?? now()->format('Y-m')) }}">
            <button type="button" class="btn btn-outline-primary" id="load_contract_month">Load Month</button>
        </div>
        @error('contract_month')<div class="text-danger small mb-2">{{ $message }}</div>@enderror
        <label class="form-label">Contract Candidate <span class="text-danger">*</span></label>
        <select name="contract_candidate_id" id="contract_candidate_id" class="form-select">
            <option value="">Select contract candidate</option>
            @foreach($contractCandidates as $candidate)
                <option value="{{ $candidate->id }}" data-client-id="{{ $candidate->client_id }}"
                    data-client="{{ $candidate->client?->client }}"
                    data-base="{{ $candidate->contract_invoice_base ?? 0 }}"
                    data-service="{{ $candidate->contract_invoice_service ?? 0 }}"
                    data-billing="{{ $candidate->clientRequirement?->billing?->value ?? 0 }}"
                    @selected(old('contract_candidate_id') == $candidate->id)>
                    {{ $candidate->candidate_name }} — {{ $candidate->client?->client ?? 'No client' }} — {{ $candidate->clientRequirement?->billing?->value ?? 0 }}%{{ $candidate->contract_is_hourly ? ' — '.$candidate->contract_worked_hours.' hrs' : ' — Monthly' }}
                </option>
            @endforeach
        </select>
        <small class="text-muted">Select one candidate from the loaded Contract Report month.</small>
        @error('contract_candidate_id')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>
    @else
        <input type="hidden" name="candidate_id" value="{{ $revenue->candidate_id }}">
        <div class="col-md-6 mb-3"><label class="form-label">Candidate</label><input class="form-control" value="{{ $revenue->candidate->candidate_name }}" disabled></div>
    @endif
    <div class="col-md-6 mb-3"><label class="form-label">Client Name <span class="text-danger">*</span></label><input name="client_name" id="client_name" class="form-control" @if($editing) required @endif value="{{ old('client_name', $revenue->client_name ?? '') }}"></div>
    <div class="col-md-6 mb-3"><label class="form-label">Invoice Number <span class="text-danger">*</span></label><input name="invoice_number" class="form-control" required value="{{ old('invoice_number', $revenue->invoice_number ?? $invoiceNumber ?? '') }}"><small class="text-muted">Generated initially and editable before saving.</small>@error('invoice_number')<div class="text-danger small">{{ $message }}</div>@enderror</div>
    <div class="col-md-3 mb-3"><label class="form-label">Invoice Date <span class="text-danger">*</span></label><input type="date" name="invoice_date" class="form-control" required value="{{ old('invoice_date', isset($revenue) ? $revenue->invoice_date->format('Y-m-d') : now()->format('Y-m-d')) }}"></div>
    <div class="col-md-3 mb-3"><label class="form-label">SZ Universe Number</label><input name="universe_number" class="form-control" value="{{ old('universe_number', $revenue->universe_number ?? '786') }}"></div>
    <div class="col-md-8 mb-3"><label class="form-label">Client Address</label><textarea name="client_address" class="form-control" rows="3">{{ old('client_address', $revenue->client_address ?? '') }}</textarea></div>
    <div class="col-md-4 mb-3"><label class="form-label">Client GST Number</label><input name="client_gst_number" class="form-control" value="{{ old('client_gst_number', $revenue->client_gst_number ?? '') }}"></div>
    <div class="col-md-4 mb-3"><label class="form-label" id="base_amount_label">Onboarding CTC (₹)</label><input type="number" step="0.01" min="0" name="onboarding_ctc" id="onboarding_ctc" class="form-control" readonly value="{{ old('onboarding_ctc', $revenue->onboarding_ctc ?? '') }}"><small class="text-muted" id="base_amount_help">Taken from the selected candidate.</small></div>
    <div class="col-md-4 mb-3"><label class="form-label">Billing Percentage <span class="text-danger">*</span></label><input type="number" step="0.01" min="0" max="100" name="billing_percentage" id="billing_percentage" class="form-control calc" @if($editing) required @endif value="{{ old('billing_percentage', $revenue->billing_percentage ?? '') }}"></div>
    <div class="col-md-4 mb-3"><label class="form-label">GST Percentage <span class="text-danger">*</span></label><input type="number" step="0.01" min="0" max="100" name="gst_percentage" id="gst_percentage" class="form-control calc" required value="{{ old('gst_percentage', $revenue->gst_percentage ?? 18) }}"></div>
    <div class="col-md-4 mb-3"><label class="form-label">Invoice Amount (₹) <span class="text-danger">*</span></label><input type="number" step="0.01" min="0" name="service_amount" id="service_amount" class="form-control" @if($editing) required @endif value="{{ old('service_amount', $revenue->service_amount ?? '') }}"><small class="text-muted">Calculated from the selected candidates and mapped billing percentages.</small></div>
    <div class="col-md-4 mb-3"><label class="form-label">GST</label><input id="gst_preview" class="form-control" disabled></div>
    <div class="col-md-4 mb-3"><label class="form-label">Invoice Total</label><input id="total_preview" class="form-control fw-bold" disabled></div>
    <div class="col-12 mb-3"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2">{{ old('notes', $revenue->notes ?? '') }}</textarea></div>
</div>
<div class="text-end"><a href="{{ route('admin.revenues.index') }}" class="btn btn-light me-2">Cancel</a><button class="btn btn-primary">{{ $editing ? 'Update Invoice' : 'Generate Invoice' }}</button></div>

@push('script')
<script>
function calculateRevenue() {
    const gstRate = Number($('#gst_percentage').val() || 0);
    const revenue = Number($('#service_amount').val() || 0), gst = revenue * gstRate / 100;
    $('#gst_preview').val('₹' + gst.toFixed(2));
    $('#total_preview').val('₹' + (revenue + gst).toFixed(2));
}
$(function () {
    const fteSelect = document.getElementById('candidate_id');
    const contractSelect = document.getElementById('contract_candidate_id');

    function selectedType() { return $('input[name="invoice_type"]:checked').val() || 'fte'; }
    function toggleRevenueType() {
        const contract = selectedType() === 'contract';
        $('#fte_candidate_field').toggleClass('d-none', contract);
        $('#contract_candidates_field').toggleClass('d-none', !contract);
        $('#candidate_id').prop('required', !contract).prop('disabled', contract);
        $('#contract_candidate_id').prop('required', contract).prop('disabled', !contract);
        $('#contract_month').prop('required', contract).prop('disabled', !contract);
        $('#billing_percentage, #service_amount').prop('readonly', true);
        $('#base_amount_label').text(contract ? 'Billing Base Total (₹)' : 'Onboarding CTC (₹)');
        $('#base_amount_help').text(contract ? 'Total from the mapped client requirements.' : 'Taken from the selected FTE candidate.');
        updateCandidateSelection(contract ? contractSelect : fteSelect);
    }
    function updateCandidateSelection(select) {
        const option = select?.options[select.selectedIndex];
        const roundMoney = value => Math.round((value + Number.EPSILON) * 100) / 100;
        const gstRate = Number($('#gst_percentage').val() || 0);
        const base = Number(option?.dataset.base || 0);
        const service = roundMoney(option?.dataset.service !== undefined
            ? Number(option.dataset.service || 0)
            : base * Number(option?.dataset.billing || 0) / 100);
        const gst = roundMoney(service * gstRate / 100);
        $('#client_name').val(option?.dataset.client || '');
        $('#onboarding_ctc').val(base ? base.toFixed(2) : '');
        $('#billing_percentage').val(option?.dataset.billing || '');
        $('#service_amount').val(service.toFixed(2));
        $('#gst_preview').val('₹' + gst.toFixed(2));
        $('#total_preview').val('₹' + roundMoney(service + gst).toFixed(2));
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
    $('#candidate_id').on('change', function () { updateCandidateSelection(fteSelect); });
    $('#contract_candidate_id').on('change', function () { updateCandidateSelection(contractSelect); });
    $('.calc, #service_amount').on('input', function () {
        @if(!$editing)
        updateCandidateSelection(selectedType() === 'contract' ? contractSelect : fteSelect);
        return;
        @endif
        if (this.id === 'billing_percentage') {
            $('#service_amount').val((Number($('#onboarding_ctc').val() || 0) * Number($('#billing_percentage').val() || 0) / 100).toFixed(2));
        }
        calculateRevenue();
    });
    calculateRevenue();
    @if(!$editing) toggleRevenueType(); @endif
});
</script>
@endpush
