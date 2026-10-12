<!-- Basic Information -->
<div class="card mb-4 border shadow-none">
    <div class="card-header bg-light">
        <h6 class="card-title mb-0"><i class="ri-user-3-line me-1 text-primary"></i> Basic Information</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label">Invester ID <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('invester_id') is-invalid @enderror" 
                    name="invester_id" 
                    value="{{ old('invester_id', $invester->invester_id ?? $suggestedId ?? '') }}" 
                    placeholder="e.g. SZ INV 0001" required>
                @error('invester_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label">Invester Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                    name="name" 
                    value="{{ old('name', $invester->name ?? '') }}" 
                    placeholder="Enter Investor Name" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label">Investor Type</label>
                @php
                    $investorTypes = ['Angel Investor', 'Venture Capitalist', 'Institutional', 'Strategic Partner', 'Family Office'];
                    $currentType = old('investor_type', $invester->investor_type ?? '');
                @endphp
                <select class="form-select @error('investor_type') is-invalid @enderror" name="investor_type">
                    <option value="">Select Investor Type</option>
                    @foreach($investorTypes as $type)
                        <option value="{{ $type }}" {{ $currentType == $type ? 'selected' : '' }}>{{ $type }}</option>
                    @endforeach
                    @if($currentType && !in_array($currentType, $investorTypes))
                        <option value="{{ $currentType }}" selected>{{ $currentType }}</option>
                    @endif
                </select>
                @error('investor_type')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label">Contact Number</label>
                <input type="text" class="form-control @error('contact') is-invalid @enderror" 
                    name="contact" 
                    value="{{ old('contact', $invester->contact ?? '') }}" 
                    placeholder="+91 9876543210">
                @error('contact')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label">Primary Email</label>
                <input type="email" class="form-control @error('primary_email') is-invalid @enderror" 
                    name="primary_email" 
                    value="{{ old('primary_email', $invester->primary_email ?? '') }}" 
                    placeholder="primary@investor.com">
                @error('primary_email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label">Alternate Email</label>
                <input type="email" class="form-control @error('alternate_email') is-invalid @enderror" 
                    name="alternate_email" 
                    value="{{ old('alternate_email', $invester->alternate_email ?? '') }}" 
                    placeholder="alternate@investor.com">
                @error('alternate_email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Profile Picture</label>
                <input type="file" class="form-control @error('profile_picture') is-invalid @enderror" 
                    name="profile_picture" accept="image/*">
                @error('profile_picture')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                @if(isset($invester) && $invester->profile_picture && file_exists(public_path($invester->profile_picture)))
                    <div class="mt-2 d-flex align-items-center gap-2">
                        <img src="{{ asset($invester->profile_picture) }}" alt="Preview" class="rounded-circle avatar-sm" style="width:48px;height:48px;object-fit:cover;">
                        <span class="text-muted small">Current Profile Picture</span>
                    </div>
                @endif
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Residential / Office Address</label>
                <textarea class="form-control @error('address') is-invalid @enderror" 
                    name="address" rows="2" placeholder="Full address details...">{{ old('address', $invester->address ?? '') }}</textarea>
                @error('address')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>
</div>

<!-- Relationship Management & Notes -->
<div class="card mb-4 border shadow-none">
    <div class="card-header bg-light">
        <h6 class="card-title mb-0"><i class="ri-user-star-line me-1 text-primary"></i> Relationship Management & Notes</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Account Manager / Internal Point of Contact</label>
                <select class="form-select @error('account_manager_id') is-invalid @enderror" name="account_manager_id">
                    <option value="">Select Employee / User</option>
                    @foreach($accountManagers as $user)
                        <option value="{{ $user->id }}" {{ (old('account_manager_id', $invester->account_manager_id ?? '') == $user->id) ? 'selected' : '' }}>
                            {{ $user->name }} ({{ $user->email }})
                        </option>
                    @endforeach
                </select>
                <small class="text-muted">The employee within your company responsible for managing this investor relationship.</small>
                @error('account_manager_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Status <span class="text-danger">*</span></label>
                <div class="d-flex align-items-center gap-4 mt-2">
                    <div class="form-check form-radio-success">
                        <input class="form-check-input" type="radio" name="status" id="statusActive" value="1" 
                            {{ old('status', $invester->status ?? 1) == 1 ? 'checked' : '' }}>
                        <label class="form-check-label" for="statusActive">Active</label>
                    </div>
                    <div class="form-check form-radio-danger">
                        <input class="form-check-input" type="radio" name="status" id="statusInactive" value="0" 
                            {{ old('status', $invester->status ?? 1) == 0 ? 'checked' : '' }}>
                        <label class="form-check-label" for="statusInactive">Inactive</label>
                    </div>
                </div>
            </div>

            <div class="col-md-12 mb-3">
                <label class="form-label">Internal Notes</label>
                <textarea class="form-control @error('internal_notes') is-invalid @enderror" 
                    name="internal_notes" rows="3" placeholder="Enter internal notes, relationship highlights, or key discussions...">{{ old('internal_notes', $invester->internal_notes ?? '') }}</textarea>
                @error('internal_notes')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>
</div>

@if(!isset($invester))
<!-- Optional Initial Details on Create -->
<div class="accordion mb-4" id="investerInitialSections">
    <!-- Team & Management Scope -->
    <div class="accordion-item border">
        <h2 class="accordion-header" id="headingTeam">
            <button class="accordion-button collapsed bg-light text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTeam" aria-expanded="false" aria-controls="collapseTeam">
                <i class="ri-team-line me-2 text-primary"></i> <strong>Team & Management Scope</strong> <span class="badge bg-secondary-subtle text-secondary ms-2">Optional Initial Entry</span>
            </button>
        </h2>
        <div id="collapseTeam" class="accordion-collapse collapse" aria-labelledby="headingTeam" data-bs-parent="#investerInitialSections">
            <div class="accordion-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Team / Organization Name</label>
                        <input type="text" class="form-control" name="team_name" value="{{ old('team_name') }}" placeholder="Organization / Fund Name">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Managed Employee Count</label>
                        <select class="form-select" name="managed_employee_count">
                            <option value="">Select Employee Count</option>
                            <option value="1-10" {{ old('managed_employee_count') == '1-10' ? 'selected' : '' }}>1-10</option>
                            <option value="11-50" {{ old('managed_employee_count') == '11-50' ? 'selected' : '' }}>11-50</option>
                            <option value="50+" {{ old('managed_employee_count') == '50+' ? 'selected' : '' }}>50+</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Management / Governance Role</label>
                        <input type="text" class="form-control" name="governance_role" value="{{ old('governance_role') }}" placeholder="e.g., Managing Partner, Lead Operating Officer, Principal">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Key Operational Contacts / Delegates</label>
                        <textarea class="form-control" name="operational_contacts" rows="2" placeholder="Chiefs of staff, administrative assistants who handle day-to-day interactions">{{ old('operational_contacts') }}</textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Financial & Investment Details -->
    <div class="accordion-item border mt-2">
        <h2 class="accordion-header" id="headingFinancial">
            <button class="accordion-button collapsed bg-light text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFinancial" aria-expanded="false" aria-controls="collapseFinancial">
                <i class="ri-money-dollar-circle-line me-2 text-primary"></i> <strong>Financial & Investment Details</strong> <span class="badge bg-secondary-subtle text-secondary ms-2">Optional Initial Entry</span>
            </button>
        </h2>
        <div id="collapseFinancial" class="accordion-collapse collapse" aria-labelledby="headingFinancial" data-bs-parent="#investerInitialSections">
            <div class="accordion-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Total Investment Amount / Committed Capital (INR / Currency)</label>
                        <input type="number" step="0.01" class="form-control" name="investment_amount" value="{{ old('investment_amount') }}" placeholder="e.g. 5000000.00">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Equity Ownership Percentage (%)</label>
                        <input type="number" step="0.01" min="0" max="100" class="form-control" name="equity_percentage" value="{{ old('equity_percentage') }}" placeholder="e.g. 5.25">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Number of Shares / Units Held</label>
                        <input type="text" class="form-control" name="shares_units_held" value="{{ old('shares_units_held') }}" placeholder="e.g. 100000">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Date of Investment</label>
                        <input type="date" class="form-control" name="investment_date" value="{{ old('investment_date') }}">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Legal, Compliance & Governance -->
    <div class="accordion-item border mt-2">
        <h2 class="accordion-header" id="headingLegal">
            <button class="accordion-button collapsed bg-light text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseLegal" aria-expanded="false" aria-controls="collapseLegal">
                <i class="ri-shield-check-line me-2 text-primary"></i> <strong>Legal, Compliance & Governance</strong> <span class="badge bg-secondary-subtle text-secondary ms-2">Optional Initial Entry</span>
            </button>
        </h2>
        <div id="collapseLegal" class="accordion-collapse collapse" aria-labelledby="headingLegal" data-bs-parent="#investerInitialSections">
            <div class="accordion-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tax Identification Number (TIN) / PAN / SSN</label>
                        <input type="text" class="form-control" name="tax_id_number" value="{{ old('tax_id_number') }}" placeholder="e.g. ABCDE1234F">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">KYC / AML Status</label>
                        <select class="form-select" name="kyc_aml_status">
                            <option value="Pending" {{ old('kyc_aml_status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                            <option value="Verified" {{ old('kyc_aml_status') == 'Verified' ? 'selected' : '' }}>Verified</option>
                            <option value="Rejected" {{ old('kyc_aml_status') == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="form-label">NDA / Legal Agreements Signed (Document Attachment)</label>
                        <input type="file" class="form-control" name="nda_signed_doc" accept=".pdf,.doc,.docx,.zip,image/*">
                        <small class="text-muted">Document attachment link for Investor Rights Agreement (IRA), Shareholders' Agreement (SHA), and Non-Disclosure Agreement.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

