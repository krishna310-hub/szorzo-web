<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Invester <span class="text-danger">*</span></label>
        @php
            $preselectedInvesterId = old('invester_id', $legalCompliance->invester_id ?? request('invester_id', ''));
        @endphp
        <select class="form-select @error('invester_id') is-invalid @enderror" name="invester_id" required>
            <option value="">Select Invester</option>
            @foreach($investers as $inv)
                <option value="{{ $inv->id }}" {{ $preselectedInvesterId == $inv->id ? 'selected' : '' }}>
                    {{ $inv->name }} ({{ $inv->invester_id }})
                </option>
            @endforeach
        </select>
        @error('invester_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label">Tax Identification Number (TIN) / PAN / SSN</label>
        <input type="text" class="form-control @error('tax_id_number') is-invalid @enderror" 
            name="tax_id_number" 
            value="{{ old('tax_id_number', $legalCompliance->tax_id_number ?? '') }}" 
            placeholder="e.g. ABCDE1234F / SSN / Tax ID">
        <small class="text-muted">Enter Tax Identification Number (TIN) / PAN / SSN</small>
        @error('tax_id_number')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label">KYC / AML Status <span class="text-danger">*</span></label>
        @php
            $kycStatuses = ['Pending', 'Verified', 'Rejected'];
            $currentKyc = old('kyc_aml_status', $legalCompliance->kyc_aml_status ?? 'Pending');
        @endphp
        <select class="form-select @error('kyc_aml_status') is-invalid @enderror" name="kyc_aml_status" required>
            @foreach($kycStatuses as $status)
                <option value="{{ $status }}" {{ $currentKyc == $status ? 'selected' : '' }}>{{ $status }}</option>
            @endforeach
        </select>
        <small class="text-muted">Dropdown (Verified, Pending, Rejected)</small>
        @error('kyc_aml_status')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label">NDA / Legal Agreements Signed (Document File)</label>
        <input type="file" class="form-control @error('nda_signed_doc') is-invalid @enderror" 
            name="nda_signed_doc" accept=".pdf,.doc,.docx,.zip,image/*">
        <small class="text-muted">Attachment link for Investor Rights Agreement (IRA), Shareholders' Agreement (SHA), and Non-Disclosure Agreement (NDA).</small>
        @error('nda_signed_doc')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror

        @if(isset($legalCompliance) && $legalCompliance->nda_signed_doc && file_exists(public_path($legalCompliance->nda_signed_doc)))
            <div class="mt-2">
                <a href="{{ asset($legalCompliance->nda_signed_doc) }}" target="_blank" class="btn btn-sm btn-soft-info">
                    <i class="ri-file-download-line me-1"></i> View Current Attached Agreement
                </a>
            </div>
        @endif
    </div>

    <div class="col-md-12 mb-3">
        <label class="form-label">Compliance Notes</label>
        <textarea class="form-control @error('notes') is-invalid @enderror" 
            name="notes" rows="2" 
            placeholder="Verification notes, legal conditions, or expiry dates...">{{ old('notes', $legalCompliance->notes ?? '') }}</textarea>
        @error('notes')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label">Status <span class="text-danger">*</span></label>
        <div class="d-flex align-items-center gap-4 mt-2">
            <div class="form-check form-radio-success">
                <input class="form-check-input" type="radio" name="status" id="legalStatusActive" value="1" 
                    {{ old('status', $legalCompliance->status ?? 1) == 1 ? 'checked' : '' }}>
                <label class="form-check-label" for="legalStatusActive">Active</label>
            </div>
            <div class="form-check form-radio-danger">
                <input class="form-check-input" type="radio" name="status" id="legalStatusInactive" value="0" 
                    {{ old('status', $legalCompliance->status ?? 1) == 0 ? 'checked' : '' }}>
                <label class="form-check-label" for="legalStatusInactive">Inactive</label>
            </div>
        </div>
    </div>
</div>

