<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Invester <span class="text-danger">*</span></label>
        @php
            $preselectedInvesterId = old('invester_id', $teamManagement->invester_id ?? request('invester_id', ''));
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
        <label class="form-label">Team / Organization Name <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('team_name') is-invalid @enderror" 
            name="team_name" 
            value="{{ old('team_name', $teamManagement->team_name ?? '') }}" 
            placeholder="Enter Team or Organization Name" required>
        @error('team_name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label">Managed Employee Count</label>
        @php
            $countOptions = ['1-10', '11-50', '50+'];
            $currentCount = old('managed_employee_count', $teamManagement->managed_employee_count ?? '');
        @endphp
        <select class="form-select @error('managed_employee_count') is-invalid @enderror" name="managed_employee_count">
            <option value="">Select Employee Count</option>
            @foreach($countOptions as $opt)
                <option value="{{ $opt }}" {{ $currentCount == $opt ? 'selected' : '' }}>{{ $opt }}</option>
            @endforeach
            @if($currentCount && !in_array($currentCount, $countOptions))
                <option value="{{ $currentCount }}" selected>{{ $currentCount }}</option>
            @endif
        </select>
        <small class="text-muted">e.g., 1-10, 11-50, 50+</small>
        @error('managed_employee_count')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label">Management / Governance Role</label>
        <input type="text" class="form-control @error('governance_role') is-invalid @enderror" 
            name="governance_role" 
            value="{{ old('governance_role', $teamManagement->governance_role ?? '') }}" 
            placeholder="e.g., Managing Partner, Lead Operating Officer, Principal">
        <small class="text-muted">e.g., Managing Partner, Lead Operating Officer, Principal</small>
        @error('governance_role')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-12 mb-3">
        <label class="form-label">Key Operational Contacts / Delegates</label>
        <textarea class="form-control @error('operational_contacts') is-invalid @enderror" 
            name="operational_contacts" rows="2" 
            placeholder="Chiefs of staff, administrative assistants who handle day-to-day interactions">{{ old('operational_contacts', $teamManagement->operational_contacts ?? '') }}</textarea>
        <small class="text-muted">Chiefs of staff, or administrative assistants who handle day-to-day interactions.</small>
        @error('operational_contacts')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-12 mb-3">
        <label class="form-label">Notes</label>
        <textarea class="form-control @error('notes') is-invalid @enderror" 
            name="notes" rows="2" 
            placeholder="Additional notes...">{{ old('notes', $teamManagement->notes ?? '') }}</textarea>
        @error('notes')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label">Status <span class="text-danger">*</span></label>
        <div class="d-flex align-items-center gap-4 mt-2">
            <div class="form-check form-radio-success">
                <input class="form-check-input" type="radio" name="status" id="teamStatusActive" value="1" 
                    {{ old('status', $teamManagement->status ?? 1) == 1 ? 'checked' : '' }}>
                <label class="form-check-label" for="teamStatusActive">Active</label>
            </div>
            <div class="form-check form-radio-danger">
                <input class="form-check-input" type="radio" name="status" id="teamStatusInactive" value="0" 
                    {{ old('status', $teamManagement->status ?? 1) == 0 ? 'checked' : '' }}>
                <label class="form-check-label" for="teamStatusInactive">Inactive</label>
            </div>
        </div>
    </div>
</div>

