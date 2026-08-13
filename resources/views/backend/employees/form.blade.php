<div class="row">
    <div class="col-12">
        <h4 class="mt-4 mb-3 text-primary">Personal Information</h4>
    </div>
    <div class="col-md-4">
        <label for="employee_name" class="form-label">Employee Name</label>
        <input type="text" class="form-control" id="employee_name" name="employee_name"
            value="{{ old('employee_name', $employee->employee_name ?? '') }}" placeholder="Enter employee name">
        @error('employee_name')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>

    @if(!empty($employee->employee_image))
    <div class="modal fade" id="employeeImageModal" tabindex="-1" aria-labelledby="employeeImageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="employeeImageModalLabel">
                        Employee Image
                    </h5>

                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body text-center">
                    <img src="{{ asset('uploads/employees/'.$employee->employee_image) }}"
                        class="img-fluid rounded"
                        alt="Employee Image">
                </div>

            </div>
        </div>
    </div>
    @endif
    <div class="col-md-4">
        <label for="employee_image" class="form-label">Employee Image
            @if(!empty($employee->employee_image))
                <a href="#" data-bs-toggle="modal" data-bs-target="#employeeImageModal" class="ms-2">
                    <i class="ri-image-line fs-5"></i>
                </a>
            @endif
        </label>
        <input type="file" class="form-control" id="employee_image" name="employee_image" accept="image/*">
        @error('employee_image')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-4">
        <label for="dob" class="form-label">Date of Birth</label>
        <input type="date" class="form-control" id="dob" name="dob"
            value="{{ old('dob', $employee->dob ?? '') }}">
        @error('dob')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-4 mt-3">
        <label for="gender" class="form-label">Gender</label>
        <select class="form-select" id="gender" name="gender">
            <option value="">Select Gender</option>
            <option value="Male" {{ old('gender', $employee->gender ?? '') == 'Male' ? 'selected' : '' }}>Male
            </option>
            <option value="Female" {{ old('gender', $employee->gender ?? '') == 'Female' ? 'selected' : '' }}>Female
            </option>
            <option value="Other" {{ old('gender', $employee->gender ?? '') == 'Other' ? 'selected' : '' }}>Other
            </option>
        </select>
        @error('gender')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-4 mt-3">
        <label for="marital_status" class="form-label">Marital Status</label>
        <select class="form-select" id="marital_status" name="marital_status">
            <option value="">Select Marital Status</option>
            <option value="Single"
                {{ old('marital_status', $employee->marital_status ?? '') == 'Single' ? 'selected' : '' }}>Single
            </option>
            <option value="Married"
                {{ old('marital_status', $employee->marital_status ?? '') == 'Married' ? 'selected' : '' }}>Married
            </option>
        </select>
        @error('marital_status')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-4 mt-3">
        <label for="nationality" class="form-label">Nationality</label>
        <input type="text" class="form-control" id="nationality" name="nationality" placeholder="Enter nationality"
            value="{{ old('nationality', $employee->nationality ?? '') }}">
        @error('nationality')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-4 mt-3">
        <label for="blood_group" class="form-label">Blood Group</label>
        <input type="text" class="form-control" id="blood_group" name="blood_group" placeholder="Enter blood group"
            value="{{ old('blood_group', $employee->blood_group ?? '') }}">
        @error('blood_group')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-12 mt-5">
        <h4 class="mt-4 mb-3 text-primary">Employee Details</h4>
    </div>
    <div class="col-md-4">
        <label for="employee_no" class="form-label">Employee ID</label>
        <input type="text" class="form-control" id="employee_no" name="employee_no" placeholder="Enter employee id"
            value="{{ old('employee_no', $employee->employee_no ?? '') }}">
        @error('employee_no')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-4">
        <label for="designation" class="form-label">Designation</label>
        <input type="text" class="form-control" id="designation" name="designation" placeholder="Enter designation"
            value="{{ old('designation', $employee->designation ?? '') }}">
        @error('designation')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-4">
        <label for="date_of_joining" class="form-label">Date of Joining (DOJ)</label>
        <input type="date" class="form-control" id="date_of_joining" name="date_of_joining"
            value="{{ old('date_of_joining', isset($employee) && $employee->date_of_joining ? $employee->date_of_joining->format('Y-m-d') : '') }}">
        @error('date_of_joining')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-4 mt-3">
        <label for="client_id" class="form-label">Client</label>
        <select class="form-select" id="client_id" name="client_id">
            <option value="">Select Client</option>
            @foreach($clients as $client)
                <option value="{{ $client->id }}" {{ (string) old('client_id', $employee->client_id ?? '') === (string) $client->id ? 'selected' : '' }}>
                    {{ $client->client }}{{ $client->status ? '' : ' (Inactive)' }}
                </option>
            @endforeach
        </select>
        @error('client_id')<span class="text-danger small">{{ $message }}</span>@enderror
    </div>
    <div class="col-md-4 mt-3">
        <label for="mode_id" class="form-label">Mode</label>
        <select class="form-select" id="mode_id" name="mode_id">
            <option value="" data-requires-contract-dates="0">Select Mode</option>
            @foreach($modes as $mode)
                @php($requiresContractDates = in_array(strtolower(trim($mode->mode)), ['contract', 'c2h'], true))
                <option value="{{ $mode->id }}" data-requires-contract-dates="{{ $requiresContractDates ? '1' : '0' }}"
                    {{ (string) old('mode_id', $employee->mode_id ?? '') === (string) $mode->id ? 'selected' : '' }}>
                    {{ $mode->mode }}{{ $mode->status ? '' : ' (Inactive)' }}
                </option>
            @endforeach
        </select>
        @error('mode_id')<span class="text-danger small">{{ $message }}</span>@enderror
    </div>
    <div class="col-md-4 mt-3 contract-date-field">
        <label for="contract_from_date" class="form-label">From Date <span class="text-danger">*</span></label>
        <input type="date" class="form-control" id="contract_from_date" name="contract_from_date"
            value="{{ old('contract_from_date', isset($employee) && $employee->contract_from_date ? $employee->contract_from_date->format('Y-m-d') : '') }}">
        @error('contract_from_date')<span class="text-danger small">{{ $message }}</span>@enderror
    </div>
    <div class="col-md-4 mt-3 contract-date-field">
        <label for="contract_to_date" class="form-label">To Date <span class="text-danger">*</span></label>
        <input type="date" class="form-control" id="contract_to_date" name="contract_to_date"
            value="{{ old('contract_to_date', isset($employee) && $employee->contract_to_date ? $employee->contract_to_date->format('Y-m-d') : '') }}">
        @error('contract_to_date')<span class="text-danger small">{{ $message }}</span>@enderror
    </div>
    @foreach(['offer_letter' => 'Offer Letter', 'intent_letter' => 'Intent Letter'] as $field => $label)
        <div class="col-md-4 mt-3">
            <label for="{{ $field }}" class="form-label">{{ $label }}
                @if(!empty($employee->{$field}))
                    <a href="{{ asset('uploads/employees/documents/'.$employee->{$field}) }}" target="_blank" class="ms-2">View</a>
                @endif
            </label>
            <input type="file" class="form-control" id="{{ $field }}" name="{{ $field }}" accept=".pdf,.jpg,.jpeg,.png">
            @error($field)<span class="text-danger small">{{ $message }}</span>@enderror
        </div>
    @endforeach
    <div class="col-12 mt-4">
        <h5 class="text-primary mb-3">Previous Employment &amp; Bank Documents</h5>
    </div>
    @php
        $multipleDocumentFields = [
            'previous_company_offer_letters' => "Previous Company's Offer Letter (All Companies)",
            'relieving_letters' => 'Relieving Letter (All Companies)',
            'pay_slips' => "3 Months' Pay Slips",
            'bank_statements' => 'Bank Statements for the Past 3 Months',
            'passbook_cheques' => 'Passbook Front Page / Cancelled Cheque (Photocopy)',
        ];
    @endphp
    @foreach($multipleDocumentFields as $field => $label)
        <div class="col-md-6 mt-3 repeatable-document" data-field="{{ $field }}">
            <label class="form-label">{{ $label }}</label>
            @if(!empty($employee->{$field}))
                <div class="mb-2 existing-documents">
                    @foreach($employee->{$field} as $document)
                        <div class="d-flex align-items-center gap-2 mb-1 existing-document">
                            <a href="{{ asset('uploads/employees/documents/'.$document) }}" target="_blank">View document {{ $loop->iteration }}</a>
                            <button type="button" class="btn btn-sm btn-outline-danger remove-existing-document" data-file="{{ $document }}">Remove</button>
                        </div>
                    @endforeach
                </div>
            @endif
            <div class="document-inputs">
                <div class="input-group mb-2 document-input-row">
                    <input type="file" class="form-control" name="{{ $field }}[]" accept=".pdf,.jpg,.jpeg,.png">
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary add-document">+ Add more</button>
            @error($field.'.*')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
    @endforeach
    <div class="col-md-4 mt-3">
        <label for="employee_uan_pf_number" class="form-label">UAN / PF Number</label>
        <input type="text" class="form-control" id="employee_uan_pf_number" name="employee_uan_pf_number" placeholder="Enter UAN/PF number"
            value="{{ old('employee_uan_pf_number', $employee->employee_uan_pf_number ?? '') }}">
        @error('employee_uan_pf_number')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-4 mt-3">
        <label for="employee_esi_number" class="form-label">ESI Number</label>
        <input type="text" class="form-control" id="employee_esi_number" name="employee_esi_number" placeholder="Enter ESI number"
            value="{{ old('employee_esi_number', $employee->employee_esi_number ?? '') }}">
        @error('employee_esi_number')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-12 mt-5">
        <h4 class="mt-4 mb-3 text-primary">Contact Information</h4>
    </div>
    <div class="col-md-4">
        <label for="mobile_number" class="form-label">Mobile Number</label>
        <input type="text" class="form-control" id="mobile_number" name="mobile_number" placeholder="Enter mobile number"
            value="{{ old('mobile_number', $employee->mobile_number ?? '') }}">
        @error('mobile_number')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-4">
        <label for="alternate_mobile_number" class="form-label">Alternate Mobile Number</label>
        <input type="text" class="form-control" id="alternate_mobile_number" name="alternate_mobile_number" placeholder="Enter alternate mobile number"
            value="{{ old('alternate_mobile_number', $employee->alternate_mobile_number ?? '') }}">
        @error('alternate_mobile_number')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-4">
        <label for="official_mail" class="form-label">Official Mail</label>
        <input type="email" class="form-control" id="official_mail" name="official_mail" placeholder="Enter official mail"
            value="{{ old('official_mail', $employee->official_mail ?? '') }}">
        @error('official_mail')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-4 mt-3">
        <label for="personal_mail" class="form-label">Personal Mail</label>
        <input type="email" class="form-control" id="personal_mail" name="personal_mail" placeholder="Enter personal mail"
            value="{{ old('personal_mail', $employee->personal_mail ?? '') }}">
        @error('personal_mail')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-12">
        <h4 class="mt-4 mb-3 mt-5 text-primary">Address Details</h4>
    </div>
    <div class="col-md-6">
        <label for="permanent_address" class="form-label">Permanent Address</label>
        <textarea class="form-control" id="permanent_address" name="permanent_address" placeholder="Enter permanent address" rows="3">{{ old('permanent_address', $employee->permanent_address ?? '') }}</textarea>
        @error('permanent_address')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-6">
        <label for="current_residential_address" class="form-label">Current Residential Address</label>
        <textarea class="form-control" id="current_residential_address" name="current_residential_address" placeholder="Enter current residential address" rows="3">{{ old('current_residential_address', $employee->current_residential_address ?? '') }}</textarea>
        @error('current_residential_address')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-12 mt-5">
        <h4 class="mt-4 mb-3 text-primary">Emergency Contact</h4>
    </div>
    <div class="col-md-4">
        <label for="emergency_contact_name" class="form-label">Emergency Contact Name</label>
        <input type="text" class="form-control" id="emergency_contact_name" name="emergency_contact_name" placeholder="Enter emergency contact name"
            value="{{ old('emergency_contact_name', $employee->emergency_contact_name ?? '') }}">
        @error('emergency_contact_name')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-4">
        <label for="relationship" class="form-label">Relationship</label>
        <input type="text" class="form-control" id="relationship" name="relationship" placeholder="Enter relationship"
            value="{{ old('relationship', $employee->relationship ?? '') }}">
        @error('relationship')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-4">
        <label for="emergency_contact_number" class="form-label">Emergency Contact Number</label>
        <input type="text" class="form-control" id="emergency_contact_number" name="emergency_contact_number" placeholder="Enter emergency contact number"
            value="{{ old('emergency_contact_number', $employee->emergency_contact_number ?? '') }}">
        @error('emergency_contact_number')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-4 mt-3">
        <label for="emergency_contact_mail" class="form-label">Emergency Contact Mail</label>
        <input type="email" class="form-control" id="emergency_contact_mail" name="emergency_contact_mail" placeholder="Enter emergency contact mail"
            value="{{ old('emergency_contact_mail', $employee->emergency_contact_mail ?? '') }}">
        @error('emergency_contact_mail')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-6 mt-3">
        <label for="emergency_contact_address" class="form-label">Emergency Contact Address</label>
        <textarea class="form-control" id="emergency_contact_address" name="emergency_contact_address" placeholder="Enter emergency contact address" rows="3">{{ old('emergency_contact_address', $employee->emergency_contact_address ?? '') }}</textarea>
        @error('emergency_contact_address')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-12 mt-5">
        <h4 class="mt-4 mb-3 text-primary">Identity Documents</h4>
    </div>
    <div class="col-md-4">
        <label for="pan_card_number" class="form-label">PAN Card Number</label>
        <input type="text" class="form-control" id="pan_card_number" name="pan_card_number" placeholder="Enter pan card number"
            value="{{ old('pan_card_number', $employee->pan_card_number ?? '') }}">
        @error('pan_card_number')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-4">
        <label for="aadhaar_card_number" class="form-label">Aadhaar Card Number</label>
        <input type="text" class="form-control" id="aadhaar_card_number" name="aadhaar_card_number" placeholder="Enter aadhaar card number "
            value="{{ old('aadhaar_card_number', $employee->aadhaar_card_number ?? '') }}">
        @error('aadhaar_card_number')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-4">
        <label for="passport_number" class="form-label">Passport Number</label>
        <input type="text" class="form-control" id="passport_number" name="passport_number" placeholder="Enter passport number"
            value="{{ old('passport_number', $employee->passport_number ?? '') }}">
        @error('passport_number')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-4 mt-3">
        <label for="passport_validity_date" class="form-label">Passport Validity Date</label>
        <input type="date" class="form-control" id="passport_validity_date" name="passport_validity_date"
            value="{{ old('passport_validity_date', $employee->passport_validity_date ?? '') }}">
        @error('passport_validity_date')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-12 mt-5">
        <h4 class="mt-4 mb-3 text-primary">Documents</h4>
        <p class="text-muted mb-3">Accepted formats: PDF, JPG, JPEG, PNG (maximum 5 MB each).</p>
    </div>
    @foreach([
        'pan_card_file' => 'PAN Card File',
        'aadhaar_file' => 'Aadhaar File',
        'twelfth_marksheet' => '12th Marksheet',
        'tenth_marksheet' => '10th Marksheet',
        'degree_certificate' => 'Degree Certificate',
    ] as $field => $label)
        <div class="col-md-4 {{ $loop->index >= 3 ? 'mt-3' : '' }}">
            <label for="{{ $field }}" class="form-label">{{ $label }}
                @if(!empty($employee->{$field}))
                    <a href="{{ asset('uploads/employees/documents/'.$employee->{$field}) }}" target="_blank" class="ms-2">View</a>
                @endif
            </label>
            <input type="file" class="form-control" id="{{ $field }}" name="{{ $field }}" accept=".pdf,.jpg,.jpeg,.png">
            @error($field)<span class="text-danger small">{{ $message }}</span>@enderror
        </div>
    @endforeach
    <div class="col-12 mt-5">
        <h4 class="mt-4 mb-3 text-primary">Family Information</h4>
    </div>
    <div class="col-md-4">
        <label for="fathers_name" class="form-label">Father's Name</label>
        <input type="text" class="form-control" id="fathers_name" name="fathers_name" placeholder="Enter father's name"
            value="{{ old('fathers_name', $employee->fathers_name ?? '') }}">
        @error('fathers_name')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-4">
        <label for="fathers_mobile_number" class="form-label">Father's Mobile Number</label>
        <input type="text" class="form-control" id="fathers_mobile_number" name="fathers_mobile_number" placeholder="Enter father's mobile number"
            value="{{ old('fathers_mobile_number', $employee->fathers_mobile_number ?? '') }}">
        @error('fathers_mobile_number')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-4">
        <label for="mothers_name" class="form-label">Mother's Name</label>
        <input type="text" class="form-control" id="mothers_name" name="mothers_name" placeholder="Enter mother's name"
            value="{{ old('mothers_name', $employee->mothers_name ?? '') }}">
        @error('mothers_name')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-4 mt-3">
        <label for="siblings_name" class="form-label">Siblings Name</label>
        <input type="text" class="form-control" id="siblings_name" name="siblings_name" placeholder="Enter siblings name"
            value="{{ old('siblings_name', $employee->siblings_name ?? '') }}">
        @error('siblings_name')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-4 mt-3">
        <label for="husband_wife_name" class="form-label">Husband / Wife Name</label>
        <input type="text" class="form-control" id="husband_wife_name" name="husband_wife_name" placeholder="Enter husband / wife name"
            value="{{ old('husband_wife_name', $employee->husband_wife_name ?? '') }}">
        @error('husband_wife_name')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-4 mt-3">
        <label for="husband_wife_dob" class="form-label">Husband / Wife DOB</label>
        <input type="date" class="form-control" id="husband_wife_dob" name="husband_wife_dob"
            value="{{ old('husband_wife_dob', $employee->husband_wife_dob ?? '') }}">
        @error('husband_wife_dob')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-4 mt-3">
        <label for="spouse_mobile_number" class="form-label">Spouse Mobile Number</label>
        <input type="text" class="form-control" id="spouse_mobile_number" name="spouse_mobile_number" placeholder="Enter spouse mobile number"
            value="{{ old('spouse_mobile_number', $employee->spouse_mobile_number ?? '') }}">
        @error('spouse_mobile_number')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-6 mt-3">
        <label for="childrens_name_dob" class="form-label">Children's Name & DOB</label>
        <textarea class="form-control" id="childrens_name_dob" name="childrens_name_dob" placeholder="Enter children's name & dob" rows="3">{{ old('childrens_name_dob', $employee->childrens_name_dob ?? '') }}</textarea>
        @error('childrens_name_dob')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-12 mt-5">
        <h4 class="mt-4 mb-3 text-primary">Bank Details</h4>
    </div>
    <div class="col-md-4">
        <label for="bank_name" class="form-label">Bank Name</label>
        <input type="text" class="form-control" id="bank_name" name="bank_name" placeholder="Enter bank name"
            value="{{ old('bank_name', $employee->bank_name ?? '') }}">
        @error('bank_name')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-4">
        <label for="account_holders_name" class="form-label">Account Holder's Name</label>
        <input type="text" class="form-control" id="account_holders_name" name="account_holders_name" placeholder="Enter account holder's name"
            value="{{ old('account_holders_name', $employee->account_holders_name ?? '') }}">
        @error('account_holders_name')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-4">
        <label for="account_number" class="form-label">Account Number</label>
        <input type="text" class="form-control" id="account_number" name="account_number" placeholder="Enter account number"
            value="{{ old('account_number', $employee->account_number ?? '') }}">
        @error('account_number')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-4 mt-3">
        <label for="branch_ifsc_code" class="form-label">Branch & IFSC Code</label>
        <input type="text" class="form-control" id="branch_ifsc_code" name="branch_ifsc_code" placeholder="Enter branch & ifsc code"
            value="{{ old('branch_ifsc_code', $employee->branch_ifsc_code ?? '') }}">
        @error('branch_ifsc_code')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-4 mt-3">
        <label for="mode_of_salary" class="form-label">Mode of Salary</label>
        <input type="text" class="form-control" id="mode_of_salary" name="mode_of_salary" placeholder="Enter mode of salary"
            value="{{ old('mode_of_salary', $employee->mode_of_salary ?? '') }}">
        @error('mode_of_salary')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-4 mt-3">
        <label for="bank_uan_pf_number" class="form-label">Bank UAN / PF Number</label>
        <input type="text" class="form-control" id="bank_uan_pf_number" name="bank_uan_pf_number" placeholder="Enter bank UAN/PF number"
            value="{{ old('bank_uan_pf_number', $employee->bank_uan_pf_number ?? '') }}">
        @error('bank_uan_pf_number')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-4 mt-3">
        <label for="bank_esi_number" class="form-label">Bank ESI Number</label>
        <input type="text" class="form-control" id="bank_esi_number" name="bank_esi_number" placeholder="Enter bank ESI number"
            value="{{ old('bank_esi_number', $employee->bank_esi_number ?? '') }}">
        @error('bank_esi_number')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-12 mt-5">
        <h4 class="mt-4 mb-3 text-primary">Health Information</h4>
    </div>
    <div class="col-md-6">
        <label for="any_health_issue" class="form-label">If Any Health Issue is there ?</label>
        <textarea class="form-control" id="any_health_issue" name="any_health_issue" placeholder="Enter health issue" rows="3">{{ old('any_health_issue', $employee->any_health_issue ?? '') }}</textarea>
        @error('any_health_issue')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-12">
        <h4 class="mt-4 mb-3 text-primary">Additional Information</h4>
    </div>
    <div class="col-md-6">
        <label for="passion" class="form-label">Passion</label>
        <textarea class="form-control" id="passion" name="passion" placeholder="Enter passion" rows="3">{{ old('passion', $employee->passion ?? '') }}</textarea>
        @error('passion')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-6">
        <label for="awards_appreciation" class="form-label">Awards / Appreciation</label>
        <textarea class="form-control" id="awards_appreciation" name="awards_appreciation" placeholder="Enter awards & appreciation" rows="3">{{ old('awards_appreciation', $employee->awards_appreciation ?? '') }}</textarea>
        @error('awards_appreciation')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-4 mt-5">
        <label class="form-label">Status</label>
        <div class="d-flex">
            <div class="form-check form-radio-success me-3">
                <input class="form-check-input" type="radio" name="status" id="status_active" value="1" {{ old('status', isset($employee) ? (int) $employee->status : 1) == 1 ? 'checked' : '' }}>
                <label class="form-check-label" for="status_active">Active</label>
            </div>
            <div class="form-check form-radio-danger ms-3">
                <input class="form-check-input" type="radio" name="status" id="status_inactive" value="0" {{ old('status', isset($employee) ? (int) $employee->status : 1) == 0 ? 'checked' : '' }}>
                <label class="form-check-label" for="status_inactive">Inactive</label>
            </div>
        </div>
        @error('status')<span class="text-danger small">{{ $message }}</span>@enderror
    </div>
</div>
@push('script')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const mode = document.getElementById('mode_id');
        const dateFields = document.querySelectorAll('.contract-date-field');
        const dateInputs = [document.getElementById('contract_from_date'), document.getElementById('contract_to_date')];

        function toggleContractDates() {
            const requiresDates = mode.options[mode.selectedIndex]?.dataset.requiresContractDates === '1';
            dateFields.forEach(field => field.classList.toggle('d-none', !requiresDates));
            dateInputs.forEach(input => input.required = requiresDates);
        }

        mode.addEventListener('change', toggleContractDates);
        toggleContractDates();

        document.querySelectorAll('.repeatable-document').forEach(function (container) {
            const inputs = container.querySelector('.document-inputs');

            container.querySelector('.add-document').addEventListener('click', function () {
                const row = document.createElement('div');
                row.className = 'input-group mb-2 document-input-row';
                row.innerHTML = '<input type="file" class="form-control" name="' + container.dataset.field + '[]" accept=".pdf,.jpg,.jpeg,.png">' +
                    '<button type="button" class="btn btn-outline-danger remove-document-input" aria-label="Remove upload">Remove</button>';
                inputs.appendChild(row);
            });

            container.addEventListener('click', function (event) {
                const removeInput = event.target.closest('.remove-document-input');
                if (removeInput) {
                    removeInput.closest('.document-input-row').remove();
                }

                const removeExisting = event.target.closest('.remove-existing-document');
                if (removeExisting) {
                    const hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = 'removed_documents[]';
                    hidden.value = removeExisting.dataset.file;
                    container.appendChild(hidden);
                    removeExisting.closest('.existing-document').remove();
                }
            });
        });
    });
</script>
@endpush
<div class="d-flex gap-3 mt-5 justify-content-center">
    <button type="reset" class="btn btn-danger">Clear</button>
    <button type="submit" class="btn btn-success">Submit</button>
</div>
