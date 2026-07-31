@php($editing = isset($revenue))
<div class="row">
    @if(!$editing)
    <div class="col-md-6 mb-3">
        <label class="form-label">Offer Accepted Candidate <span class="text-danger">*</span></label>
        <select name="candidate_id" id="candidate_id" class="form-select" required>
            <option value="">Select candidate</option>
            @foreach($candidates as $candidate)
                <option value="{{ $candidate->id }}"
                    data-client="{{ $candidate->client?->client }}"
                    data-ctc="{{ $candidate->expected_ctc }}"
                    data-billing="{{ $candidate->client?->billing?->value }}"
                    {{ old('candidate_id') == $candidate->id ? 'selected' : '' }}>
                    {{ $candidate->candidate_name }} — {{ $candidate->client?->client ?? 'No client' }}
                </option>
            @endforeach
        </select>
        <small class="text-muted">Only candidates at interview level ID 30 are listed.</small>
    </div>
    @else
        <input type="hidden" name="candidate_id" value="{{ $revenue->candidate_id }}">
        <div class="col-md-6 mb-3"><label class="form-label">Candidate</label><input class="form-control" value="{{ $revenue->candidate->candidate_name }}" disabled></div>
    @endif
    <div class="col-md-6 mb-3"><label class="form-label">Client Name <span class="text-danger">*</span></label><input name="client_name" id="client_name" class="form-control" required value="{{ old('client_name', $revenue->client_name ?? '') }}"></div>
    <div class="col-md-6 mb-3"><label class="form-label">Invoice Number</label><input class="form-control" value="{{ old('invoice_number', $revenue->invoice_number ?? $invoiceNumber ?? '') }}" readonly><input type="hidden" name="invoice_number" value="{{ old('invoice_number', $revenue->invoice_number ?? $invoiceNumber ?? '') }}"><small class="text-muted">Generated automatically by financial year.</small></div>
    <div class="col-md-3 mb-3"><label class="form-label">Invoice Date <span class="text-danger">*</span></label><input type="date" name="invoice_date" class="form-control" required value="{{ old('invoice_date', isset($revenue) ? $revenue->invoice_date->format('Y-m-d') : now()->format('Y-m-d')) }}"></div>
    <div class="col-md-3 mb-3"><label class="form-label">SZ Universe Number</label><input name="universe_number" class="form-control" value="{{ old('universe_number', $revenue->universe_number ?? '786') }}"></div>
    <div class="col-md-8 mb-3"><label class="form-label">Client Address</label><textarea name="client_address" class="form-control" rows="3">{{ old('client_address', $revenue->client_address ?? '') }}</textarea></div>
    <div class="col-md-4 mb-3"><label class="form-label">Client GST Number</label><input name="client_gst_number" class="form-control" value="{{ old('client_gst_number', $revenue->client_gst_number ?? '') }}"></div>
    <div class="col-md-4 mb-3"><label class="form-label">Offered CTC (₹) <span class="text-danger">*</span></label><input type="number" step="0.01" min="0" name="offered_ctc" id="offered_ctc" class="form-control calc" required value="{{ old('offered_ctc', $revenue->offered_ctc ?? '') }}"></div>
    <div class="col-md-4 mb-3"><label class="form-label">Billing Percentage <span class="text-danger">*</span></label><input type="number" step="0.01" min="0" max="100" name="billing_percentage" id="billing_percentage" class="form-control calc" required value="{{ old('billing_percentage', $revenue->billing_percentage ?? '') }}"></div>
    <div class="col-md-4 mb-3"><label class="form-label">GST Percentage <span class="text-danger">*</span></label><input type="number" step="0.01" min="0" max="100" name="gst_percentage" id="gst_percentage" class="form-control calc" required value="{{ old('gst_percentage', $revenue->gst_percentage ?? 18) }}"></div>
    <div class="col-md-4 mb-3"><label class="form-label">Invoice Amount (₹) <span class="text-danger">*</span></label><input type="number" step="0.01" min="0" name="service_amount" id="service_amount" class="form-control" required value="{{ old('service_amount', $revenue->service_amount ?? '') }}"><small class="text-muted">Editable; initially calculated from CTC and billing %.</small></div>
    <div class="col-md-4 mb-3"><label class="form-label">GST</label><input id="gst_preview" class="form-control" disabled></div>
    <div class="col-md-4 mb-3"><label class="form-label">Invoice Total</label><input id="total_preview" class="form-control fw-bold" disabled></div>
    <div class="col-12 mb-3"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2">{{ old('notes', $revenue->notes ?? '') }}</textarea></div>
</div>
<div class="text-end"><a href="{{ route('admin.revenues.index') }}" class="btn btn-light me-2">Cancel</a><button class="btn btn-primary">{{ $editing ? 'Update Invoice' : 'Generate Invoice' }}</button></div>

@push('script')
<script>
function calculateRevenue() {
    const ctc = Number($('#offered_ctc').val() || 0), billing = Number($('#billing_percentage').val() || 0), gstRate = Number($('#gst_percentage').val() || 0);
    const revenue = Number($('#service_amount').val() || 0), gst = revenue * gstRate / 100;
    $('#gst_preview').val('₹' + gst.toFixed(2));
    $('#total_preview').val('₹' + (revenue + gst).toFixed(2));
}
$(function () {
    $('#candidate_id').on('change', function () {
        const option = this.options[this.selectedIndex];
        $('#client_name').val(option.dataset.client || '');
        $('#offered_ctc').val(option.dataset.ctc || '');
        $('#billing_percentage').val(option.dataset.billing || '');
        $('#service_amount').val((Number(option.dataset.ctc || 0) * Number(option.dataset.billing || 0) / 100).toFixed(2));
        calculateRevenue();
    });
    $('.calc, #service_amount').on('input', function () {
        if (this.id === 'offered_ctc' || this.id === 'billing_percentage') {
            $('#service_amount').val((Number($('#offered_ctc').val() || 0) * Number($('#billing_percentage').val() || 0) / 100).toFixed(2));
        }
        calculateRevenue();
    });
    calculateRevenue();
});
</script>
@endpush
