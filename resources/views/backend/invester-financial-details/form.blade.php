<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Invester <span class="text-danger">*</span></label>
        @php
            $preselectedInvesterId = old('invester_id', $financialDetail->invester_id ?? request('invester_id', ''));
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
        <label class="form-label">Total Investment Amount / Committed Capital</label>
        <div class="input-group">
            <span class="input-group-text"><i class="ri-money-dollar-circle-line"></i></span>
            <input type="number" step="0.01" min="0" class="form-control @error('investment_amount') is-invalid @enderror" 
                name="investment_amount" 
                value="{{ old('investment_amount', $financialDetail->investment_amount ?? '') }}" 
                placeholder="e.g. 5000000.00">
        </div>
        <small class="text-muted">Total committed or invested capital amount</small>
        @error('investment_amount')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label">Equity Ownership Percentage (%)</label>
        <div class="input-group">
            <input type="number" step="0.01" min="0" max="100" class="form-control @error('equity_percentage') is-invalid @enderror" 
                name="equity_percentage" 
                value="{{ old('equity_percentage', $financialDetail->equity_percentage ?? '') }}" 
                placeholder="e.g. 10.50">
            <span class="input-group-text">%</span>
        </div>
        @error('equity_percentage')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label">Date of Investment</label>
        <input type="date" class="form-control @error('investment_date') is-invalid @enderror" 
            name="investment_date" 
            value="{{ old('investment_date', isset($financialDetail) && $financialDetail->investment_date ? $financialDetail->investment_date->format('Y-m-d') : '') }}">
        @error('investment_date')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-12 mb-3">
        <label class="form-label">Investment Notes / Remarks</label>
        <textarea class="form-control @error('notes') is-invalid @enderror" 
            name="notes" rows="2" 
            placeholder="Investment tranche details, valuation notes, etc...">{{ old('notes', $financialDetail->notes ?? '') }}</textarea>
        @error('notes')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label">Status <span class="text-danger">*</span></label>
        <div class="d-flex align-items-center gap-4 mt-2">
            <div class="form-check form-radio-success">
                <input class="form-check-input" type="radio" name="status" id="finStatusActive" value="1" 
                    {{ old('status', $financialDetail->status ?? 1) == 1 ? 'checked' : '' }}>
                <label class="form-check-label" for="finStatusActive">Active</label>
            </div>
            <div class="form-check form-radio-danger">
                <input class="form-check-input" type="radio" name="status" id="finStatusInactive" value="0" 
                    {{ old('status', $financialDetail->status ?? 1) == 0 ? 'checked' : '' }}>
                <label class="form-check-label" for="finStatusInactive">Inactive</label>
            </div>
        </div>
    </div>
</div>

