<div class="row gy-4">
    {{-- <div class="col-md-4">
        <label for="recruiter_id" class="form-label">Recruiter</label>
        <select class="form-select" id="recruiter_id" name="recruiter_id"><option value="">Select recruiter</option>
            @foreach($recruiters as $recruiter)<option value="{{ $recruiter->id }}" {{ old('recruiter_id', $candidate->recruiter_id ?? '') == $recruiter->id ? 'selected' : '' }}>{{ $recruiter->recruiter_name }}</option>@endforeach
        </select>@error('recruiter_id')<span class="text-danger small">{{ $message }}</span>@enderror
    </div> --}}
    <div class="col-md-4">
        @php
            $selectedRecruiterId = old(
                'recruiter_id',
                ($isRecruiterCandidateList ?? false)
                    ? $linkedRecruiter?->id
                    : ($candidate->recruiter_id ?? '')
            );
        @endphp
        <label for="recruiter_id" class="form-label">Recruiter <span class="text-danger">*</span></label>
        <select class="form-select" id="recruiter_id" name="recruiter_id" {{ ($isRecruiterCandidateList ?? false) ? 'disabled' : '' }}>
            <option value="">Select Recruiter</option>
            @foreach ($recruiters as $recruiter)
                <option value="{{ $recruiter->id }}"
                    {{ (string) $selectedRecruiterId == (string) $recruiter->id ? 'selected' : '' }}>
                    {{ $recruiter->recruiter_name }}</option>
            @endforeach
        </select>
        @if($isRecruiterCandidateList ?? false)
            <input type="hidden" name="recruiter_id" value="{{ $selectedRecruiterId }}">
            <small class="text-muted">Recruiter is fixed from your login.</small>
        @endif
        @error('recruiter_id')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-4">
        <label for="client_id" class="form-label">Client<span class="text-danger">*</span></label>
        <select class="form-select" id="client_id" name="client_id">
            <option value="">Select client</option>
            @foreach ($clients as $client)
                <option value="{{ $client->id }}"
                    {{ old('client_id', $candidate->client_id ?? '') == $client->id ? 'selected' : '' }}>
                    {{ $client->client }}</option>
            @endforeach
        </select>
        @error('client_id')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-4">
        <label for="job_role_id" class="form-label">Job Role <span class="text-danger">*</span></label>
        <select class="form-select" id="job_role_id" name="job_role_id">
            <option value="">Select job role</option>
            @foreach ($jobRoles as $jobRole)
                <option value="{{ $jobRole->id }}"
                    {{ old('job_role_id', $candidate->job_role_id ?? '') == $jobRole->id ? 'selected' : '' }}>
                    {{ $jobRole->job_role }}</option>
            @endforeach
        </select>
        @error('job_role_id')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-4">
        <label for="mode_id" class="form-label">Mode <span class="text-danger">*</span></label>
        <select class="form-select" id="mode_id" name="mode_id" required>
            <option value="">Select mode</option>
            @foreach ($modes as $mode)
                <option value="{{ $mode->id }}" @selected((string) old('mode_id', $candidate->mode_id ?? '') === (string) $mode->id)>{{ $mode->mode }}</option>
            @endforeach
        </select>
        @error('mode_id')<span class="text-danger small">{{ $message }}</span>@enderror
    </div>
    <div class="col-md-4 contract-date-field">
        <label for="contract_from_date" class="form-label">Contract From Date</label>
        <input type="date" class="form-control" id="contract_from_date" name="contract_from_date"
            value="{{ old('contract_from_date', isset($candidate) && $candidate->contract_from_date ? $candidate->contract_from_date->format('Y-m-d') : '') }}">
        @error('contract_from_date')<span class="text-danger small">{{ $message }}</span>@enderror
    </div>
    <div class="col-md-4 contract-date-field">
        <label for="contract_to_date" class="form-label">Contract To Date</label>
        <input type="date" class="form-control" id="contract_to_date" name="contract_to_date"
            value="{{ old('contract_to_date', isset($candidate) && $candidate->contract_to_date ? $candidate->contract_to_date->format('Y-m-d') : '') }}">
        @error('contract_to_date')<span class="text-danger small">{{ $message }}</span>@enderror
    </div>
    <div class="col-md-4 contract-hourly-field">
        <div class="form-check mt-4 pt-2">
            <input type="checkbox" class="form-check-input" id="is_hourly" name="is_hourly" value="1"
                @checked((bool) old('is_hourly', $candidate->is_hourly ?? false))>
            <label for="is_hourly" class="form-check-label">Hourly Contract</label>
        </div>
        @error('is_hourly')<span class="text-danger small">{{ $message }}</span>@enderror
    </div>
    <div class="col-md-4 contract-hourly-salary-field">
        <label for="hourly_salary" class="form-label">Hourly Salary</label>
        <input type="number" step="0.01" min="0.01" class="form-control" id="hourly_salary"
            name="hourly_salary" value="{{ old('hourly_salary', $candidate->hourly_salary ?? '') }}"
            placeholder="Enter salary per hour">
        @error('hourly_salary')<span class="text-danger small">{{ $message }}</span>@enderror
    </div>
    <div class="col-md-4"><label for="candidate_name" class="form-label">Candidate Name <span
                class="text-danger">*</span></label><input class="form-control" id="candidate_name"
            name="candidate_name" value="{{ old('candidate_name', $candidate->candidate_name ?? '') }}">
        @error('candidate_name')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-4"><label for="mobile_no" class="form-label">Mobile No <span class="text-danger">*</span></label><input class="form-control"
            id="mobile_no" name="mobile_no" value="{{ old('mobile_no', $candidate->mobile_no ?? '') }}">
        @error('mobile_no')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-4"><label for="email" class="form-label">Email <span class="text-danger">*</span></label><input type="email"
            class="form-control" id="email" name="email" value="{{ old('email', $candidate->email ?? '') }}">
        @error('email')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-4"><label for="qualification" class="form-label">Qualification</label><input class="form-control"
            id="qualification" name="qualification"
            value="{{ old('qualification', $candidate->qualification ?? '') }}">
        @error('qualification')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-4"><label for="total_experience" class="form-label">Total Experience <span class="text-danger">*</span></label><input type="number"
            step="0.01" min="0" class="form-control" id="total_experience" name="total_experience"
            value="{{ old('total_experience', $candidate->total_experience ?? '') }}">
        @error('total_experience')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-4"><label for="relevant_experience" class="form-label">Relevant Experience <span class="text-danger">*</span></label><input
            type="number" step="0.01" min="0" class="form-control" id="relevant_experience"
            name="relevant_experience" value="{{ old('relevant_experience', $candidate->relevant_experience ?? '') }}">
        @error('relevant_experience')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-4"><label for="take_home" class="form-label">Take Home</label><input type="number"
            step="0.01" min="0" class="form-control" id="take_home" name="take_home"
            value="{{ old('take_home', isset($candidate) ? (int) $candidate->take_home : '') }}">
        @error('take_home')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-4"><label for="variable" class="form-label">Variable</label><input type="number" step="0.01"
            min="0" class="form-control" id="variable" name="variable"
            value="{{ old('variable', isset($candidate) ? (int) $candidate->variable : '') }}">
        @error('variable')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-4"><label for="current_ctc" class="form-label">Current CTC <span class="text-danger">*</span></label><input type="number"
            step="0.01" min="0" class="form-control" id="current_ctc" name="current_ctc"
            value="{{ old('current_ctc', isset($candidate) ? (int) $candidate->current_ctc : '') }}">
        @error('current_ctc')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-4"><label for="expected_ctc" class="form-label">Expected CTC <span class="text-danger">*</span></label><input type="number"
            step="0.01" min="0" class="form-control" id="expected_ctc" name="expected_ctc"
            value="{{ old('expected_ctc', isset($candidate) ? (int) $candidate->expected_ctc : '') }}">
        @error('expected_ctc')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-4"><label for="onboarding_ctc" class="form-label">Onboarding CTC </label><input type="number"
            step="0.01" min="0" class="form-control" id="onboarding_ctc" name="onboarding_ctc"
            value="{{ old('onboarding_ctc', isset($candidate) ? (int) $candidate->onboarding_ctc : '') }}">
        @error('onboarding_ctc')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-4"><label for="notice_period" class="form-label">Notice Period <span class="text-danger">*</span></label><input
            class="form-control" id="notice_period" name="notice_period"
            value="{{ old('notice_period', $candidate->notice_period ?? '') }}">
        @error('notice_period')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-4"><label for="current_company" class="form-label">Current Company <span class="text-danger">*</span></label><input
            class="form-control" id="current_company" name="current_company"
            value="{{ old('current_company', $candidate->current_company ?? '') }}">
        @error('current_company')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-4"><label for="current_location" class="form-label">Current Location <span class="text-danger">*</span></label><input
            class="form-control" id="current_location" name="current_location"
            value="{{ old('current_location', $candidate->current_location ?? '') }}">
        @error('current_location')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-4"><label for="preferred_location" class="form-label">Preferred Location <span class="text-danger">*</span></label><input
            class="form-control" id="preferred_location" name="preferred_location"
            value="{{ old('preferred_location', $candidate->preferred_location ?? '') }}">
        @error('preferred_location')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    @php
        $isCandidateEdit = isset($candidate) && $candidate->exists;
        $hasExistingCv = $isCandidateEdit && filled($candidate->upload_cv);
    @endphp
    <div class="col-md-4">
        <label for="upload_cv" class="form-label">
            Upload CV @unless($isCandidateEdit)<span class="text-danger">*</span>@endunless

            @if($hasExistingCv)
                <a href="{{ asset($candidate->upload_cv) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                    View CV
                </a>
            @endif
        </label>

        <input type="file" class="form-control" id="upload_cv" name="upload_cv" accept=".pdf,.doc,.docx" @unless($isCandidateEdit) required @endunless>

        <span class="text-danger small">Maximum file size is 2MB</span>
        @error('upload_cv')<div class="text-danger small">{{ $message }}</div>@enderror

    </div>
    <div class="col-md-8"><label for="reason_for_change" class="form-label">Reason For Change</label>
        <textarea class="form-control" id="reason_for_change" name="reason_for_change" rows="3">{{ old('reason_for_change', $candidate->reason_for_change ?? '') }}</textarea>
        @error('reason_for_change')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>

    @php
        $sourcingLevels = [
            'CV Shared to DL',
            'Internal Duplicate',
            'Profile Feedback Pending',
            'Client Duplicate',
            'Screen Select',
            'Screen Reject',
            'Position Hold',
            'Position Closed',
            'Candidate Not Interested',
            'Candidate Not Responding',
        ];
        $interviewLevelsList = [
            'L1 Scheduled',
            'L1 Select',
            'L1 Reject',
            'L1 Re-Schedule',

            'L2 Scheduled',
            'L2 Select',
            'L2 Reject',
            'L2 Re-Schedule',

            'L3 Scheduled',
            'L3 Select',
            'L3 Reject',
            'L3 Re-Schedule',

            'L4 Scheduled',
            'L4 Select',
            'L4 Reject',
            'L4 Re-Schedule',
        ];

        $onboardingLevels = [
            'HR Discussion',
            'HR Select',
            'HR Reject',
            'Offer Released',
            'Offer Accepted',
            'Offer Declined',
            'Onboarded with Client',
            'Joiner Declined',
        ];
        $selectedLevel = old('level_of_interview_id', $candidate->level_of_interview_id ?? '');
    @endphp
    <div class="col-md-4">
    <label for="level_of_interview_id" class="form-label">
        Level Of Interview <span class="text-danger">*</span>
    </label>

    <select class="form-select" id="level_of_interview_id" name="level_of_interview_id">
        <option value="">Select Level</option>

        {{-- Sourcing Stage --}}
        <optgroup label="Sourcing Stage">
            @foreach ($interviewLevels as $level)
                @if(in_array($level->level, $sourcingLevels))
                    <option value="{{ $level->id }}"
                        {{ $selectedLevel == $level->id ? 'selected' : '' }}>
                        {{ $level->level }}
                    </option>
                @endif
            @endforeach
        </optgroup>

        {{-- Interview Stage --}}
        <optgroup label="Interview Stage">
            @foreach ($interviewLevels as $level)
                @if(in_array($level->level, $interviewLevelsList))
                    <option value="{{ $level->id }}"
                        {{ $selectedLevel == $level->id ? 'selected' : '' }}
                        {{ $selectedLevel != $level->id ? 'disabled' : '' }}>
                        {{ $level->level }}
                    </option>
                @endif
            @endforeach
        </optgroup>

        {{-- Onboarding Stage --}}
        <optgroup label="Onboarding Stage">
            @foreach ($interviewLevels as $level)
                @if(in_array($level->level, $onboardingLevels))
                    <option value="{{ $level->id }}"
                        {{ $selectedLevel == $level->id ? 'selected' : '' }}
                        {{ $selectedLevel != $level->id ? 'disabled' : '' }}>
                        {{ $level->level }}
                    </option>
                @endif
            @endforeach
        </optgroup>
    </select>

    @error('level_of_interview_id')
        <span class="text-danger small">{{ $message }}</span>
    @enderror
</div>
    <div class="col-md-4"><label class="form-label">Status</label>
        <div class="d-flex">
            <div class="form-check form-radio-success me-3"><input class="form-check-input" type="radio"
                    name="status" id="status_active" value="1"
                    {{ old('status', isset($candidate) ? (int) $candidate->status : 1) == 1 ? 'checked' : '' }}><label
                    class="form-check-label" for="status_active">Active</label></div>
            <div class="form-check form-radio-danger ms-3"><input class="form-check-input" type="radio"
                    name="status" id="status_inactive" value="0"
                    {{ old('status', isset($candidate) ? (int) $candidate->status : 1) == 0 ? 'checked' : '' }}><label
                    class="form-check-label" for="status_inactive">Inactive</label></div>
        </div>
        @error('status')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-4" id="onboardingDateDiv"></div>
    <div class="col-md-4" id="onboardingDateDiv" style="{{ !empty($candidate->onboarding_date) ? 'display:block;' : 'display:none;' }}">
        <label for="onboarding_date" class="form-label">Onboarding Date</label>
        <input type="date" class="form-control" id="onboarding_date" name="onboarding_date"
            value="{{ old('onboarding_date', isset($candidate) && $candidate->onboarding_date ? $candidate->onboarding_date->format('Y-m-d') : '') }}" readonly>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const client = document.getElementById('client_id');
    const role = document.getElementById('job_role_id');
    const roleMap = @json($clientJobRoleMap);
    const original = Array.from(role.options).map(option => ({
        value: option.value, text: option.text, selected: option.selected
    }));
    function filterRoles() {
        const selected = role.value;
        const allowed = (roleMap[client.value] || []).map(String);
        role.innerHTML = '';
        original.forEach(item => {
            if (!item.value || allowed.includes(String(item.value))) {
                role.add(new Option(item.text, item.value, false, String(item.value) === String(selected)));
            }
        });
        if (selected && !allowed.includes(String(selected))) role.value = '';
        role.disabled = !client.value;
    }
    client.addEventListener('change', filterRoles);
    filterRoles();

    const mode = document.getElementById('mode_id');
    const contractDateFields = document.querySelectorAll('.contract-date-field');
    const contractHourlyField = document.querySelector('.contract-hourly-field');
    const hourlySalaryField = document.querySelector('.contract-hourly-salary-field');
    const isHourly = document.getElementById('is_hourly');
    const hourlySalary = document.getElementById('hourly_salary');
    function toggleContractDates() {
        const isContract = mode.value === '2';
        contractDateFields.forEach(field => field.classList.toggle('d-none', !isContract));
        contractHourlyField.classList.toggle('d-none', !isContract);
        if (!isContract) isHourly.checked = false;
        toggleHourlySalary();
    }
    function toggleHourlySalary() {
        const showHourlySalary = mode.value === '2' && isHourly.checked;
        hourlySalaryField.classList.toggle('d-none', !showHourlySalary);
        if (!showHourlySalary) hourlySalary.value = '';
    }
    mode.addEventListener('change', toggleContractDates);
    isHourly.addEventListener('change', toggleHourlySalary);
    toggleContractDates();
});
</script>
<div class="d-flex gap-3 mt-5 justify-content-center"><button type="reset"
        class="btn btn-danger">Clear</button><button type="submit" class="btn btn-success">Submit</button></div>
