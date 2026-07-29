<div class="row gy-4">
    <div class="col-md-4"><label for="client_id" class="form-label">Client <span class="text-danger">*</span></label><select
            class="form-select" id="client_id" name="client_id">
            <option value="">Select client</option>
            @foreach ($clients as $client)
                <option value="{{ $client->id }}"
                    {{ old('client_id', $clientRequirement->client_id ?? '') == $client->id ? 'selected' : '' }}>
                    {{ $client->client }}</option>
            @endforeach
        </select>
        @error('client_id')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-4"><label for="job_role_id" class="form-label">Job Role</label><select class="form-select"
            id="job_role_id" name="job_role_id">
            <option value="">Select job role</option>
            @foreach ($jobRoles as $jobRole)
                <option value="{{ $jobRole->id }}"
                    {{ old('job_role_id', $clientRequirement->job_role_id ?? '') == $jobRole->id ? 'selected' : '' }}>
                    {{ $jobRole->job_role }}</option>
            @endforeach
        </select>
        @error('job_role_id')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-4"><label for="position_level" class="form-label">Position Level</label>
        <select class="form-select" id="position_level" name="position_level">
            <option value="">Select Position Level</option>
            <option value="Junior" {{ old('position_level', $clientRequirement->position_level ?? '') == 'Junior' ? 'selected' : '' }}>
                Junior
            </option>
            <option value="Mid" {{ old('position_level', $clientRequirement->position_level ?? '') == 'Mid' ? 'selected' : '' }}>
                Mid
            </option>
            <option value="Senior" {{ old('position_level', $clientRequirement->position_level ?? '') == 'Senior' ? 'selected' : '' }}>
                Senior
            </option>
        </select>
        @error('position_level')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    @php
        $selectedModes = array_map('strval', (array) old('mode_ids', $clientRequirement->mode_ids ?? array_filter([$clientRequirement->mode_id ?? null])));
        $selectedLocations = array_map('strval', (array) old('location_ids', $clientRequirement->location_ids ?? array_filter([$clientRequirement->location_id ?? null])));
        $selectedProjectOwners = array_map('strval', (array) old('project_owner_ids', $clientRequirement->project_owner_ids ?? array_filter([$clientRequirement->project_owner ?? null])));
    @endphp
    <div class="col-md-4"><label for="mode_ids" class="form-label">Modes</label><select class="form-select"
            id="mode_ids" name="mode_ids[]" multiple>
            @foreach ($modes as $mode)
                <option value="{{ $mode->id }}"
                    {{ in_array((string)$mode->id, $selectedModes, true) ? 'selected' : '' }}>
                    {{ $mode->mode }}</option>
            @endforeach
        </select>
        <small class="text-muted">Search and select one or more modes.</small>
        @error('mode_ids')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-4">
        <label for="billing" class="form-label">Billing</label>
        <select class="form-select" id="billing_id" name="billing_id">
            <option value="">Select billing</option>
            @foreach($billings as $billing)
                <option value="{{ $billing->id }}" data-value="{{ $billing->value }}" {{ old('billing_id', $clientRequirement->billing_id ?? '') == $billing->id ? 'selected' : '' }}>{{ $billing->value }} %</option>
            @endforeach
        </select>
        @error('billing_id')<span class="text-danger small">{{ $message }}</span>@enderror
    </div>
    <div class="col-md-4"><label for="ctc" class="form-label">CTC</label><input type="number" step="0.01"
            min="0" class="form-control" id="ctc" name="ctc"
            value="{{ old('ctc', $clientRequirement->ctc ?? '') }}">
        @error('ctc')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-4"><label for="revenue_amount" class="form-label">Revenue Amount</label><input type="number"
            class="form-control" id="revenue_amount" name="revenue_amount"
            value="" readonly>
        @error('revenue_amount')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    {{-- <div class="col-md-4"><label for="job_description_id" class="form-label">Job Description</label><select
            class="form-select" id="job_description_id" name="job_description_id">
            <option value="">Select job description</option>
            @foreach ($jobDescriptions as $jobDescription)
                <option value="{{ $jobDescription->id }}"
                    {{ old('job_description_id', $clientRequirement->job_description_id ?? '') == $jobDescription->id ? 'selected' : '' }}>
                    {{ $jobDescription->job_description ?: 'Job Description #' . $jobDescription->id }}</option>
            @endforeach
        </select>
        @error('job_description_id')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div> --}}
    <div class="col-md-4"><label for="requirement_open_date" class="form-label">Requirement Open Date</label><input
            type="date" class="form-control" id="requirement_open_date" name="requirement_open_date"
            value="{{ old('requirement_open_date', isset($clientRequirement) && $clientRequirement->requirement_open_date ? $clientRequirement->requirement_open_date->format('Y-m-d') : '') }}">
        @error('requirement_open_date')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-4"><label for="number_of_position" class="form-label">Number Of Position</label><input
            type="number" min="0" class="form-control" id="number_of_position" name="number_of_position"
            value="{{ old('number_of_position', $clientRequirement->number_of_position ?? 0) }}">
        @error('number_of_position')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-4"><label for="closure_target_date" class="form-label">Closure Target Date</label><input
            type="date" class="form-control" id="closure_target_date" name="closure_target_date"
            value="{{ old('closure_target_date', isset($clientRequirement) && $clientRequirement->closure_target_date ? $clientRequirement->closure_target_date->format('Y-m-d') : '') }}">
        @error('closure_target_date')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-4"><label for="cv_required" class="form-label">CV's Required</label><input type="number"
            min="0" class="form-control" id="cv_required" name="cv_required"
            value="{{ old('cv_required', $clientRequirement->cv_required ?? 0) }}">
        @error('cv_required')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-4"><label for="cv_uploaded" class="form-label">CV's Uploaded</label><input type="number"
            min="0" class="form-control" id="cv_uploaded" name="cv_uploaded"
            value="{{ old('cv_uploaded', $clientRequirement->cv_uploaded ?? 0) }}">
        @error('cv_uploaded')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-4"><label for="project_owner_ids" class="form-label">Project Owners</label><select
            class="form-select" id="project_owner_ids" name="project_owner_ids[]" multiple>
            @foreach ($recruiters as $recruiter)
                <option value="{{ $recruiter->id }}"
                    {{ in_array((string) $recruiter->id, $selectedProjectOwners, true) ? 'selected' : '' }}>
                    {{ $recruiter->recruiter_name }}</option>
            @endforeach
        </select>
        <small class="text-muted">Search and select one or more project owners.</small>
        @error('project_owner_ids')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-4"><label for="location_ids" class="form-label">Locations</label><select class="form-select"
            id="location_ids" name="location_ids[]" multiple>
            @foreach ($locations as $location)
                <option value="{{ $location->id }}"
                    {{ in_array((string)$location->id, $selectedLocations, true) ? 'selected' : '' }}>
                    {{ $location->location }}</option>
            @endforeach
        </select>
        <small class="text-muted">Search and select one or more locations.</small>
        @error('location_ids')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-4"><label class="form-label">Status</label>
        <div class="d-flex">
            <div class="form-check form-radio-success me-3"><input class="form-check-input" type="radio"
                    name="status" id="status_active" value="1"
                    {{ old('status', isset($clientRequirement) ? (int) $clientRequirement->status : 1) == 1 ? 'checked' : '' }}><label
                    class="form-check-label" for="status_active">Active</label></div>
            <div class="form-check form-radio-danger ms-3"><input class="form-check-input" type="radio"
                    name="status" id="status_inactive" value="0"
                    {{ old('status', isset($clientRequirement) ? (int) $clientRequirement->status : 1) == 0 ? 'checked' : '' }}><label
                    class="form-check-label" for="status_inactive">Inactive</label></div>
        </div>
        @error('status')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-4">
        <label class="form-label d-block" for="is_priority">Priority</label>
        <div class="form-check form-switch pt-2">
            <input type="hidden" name="is_priority" value="0">
            <input class="form-check-input" type="checkbox" role="switch" id="is_priority" name="is_priority"
                value="1" {{ old('is_priority', isset($clientRequirement) ? (int) $clientRequirement->is_priority : 0) == 1 ? 'checked' : '' }}>
            <label class="form-check-label" for="is_priority">Mark as priority requirement</label>
        </div>
        @error('is_priority')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
</div>
<div class="d-flex gap-3 mt-5 justify-content-center"><button type="reset"
        class="btn btn-danger">Clear</button><button type="submit" class="btn btn-success">Submit</button></div>

@push('script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const client = document.getElementById('client_id');
    const jobRole = document.getElementById('job_role_id');
    const clientJobRoleMap = @json($clientJobRoleMap);
    const jobRoleOptions = Array.from(jobRole.options).map(function (option) {
        return {
            value: option.value,
            text: option.text,
            selected: option.selected
        };
    });

    function filterJobRoles() {
        const selectedRole = jobRole.value;
        const allowedRoles = (clientJobRoleMap[client.value] || []).map(String);

        jobRole.innerHTML = '';
        jobRoleOptions.forEach(function (option) {
            if (!option.value || allowedRoles.includes(String(option.value))) {
                jobRole.add(new Option(
                    option.text,
                    option.value,
                    false,
                    String(option.value) === String(selectedRole)
                ));
            }
        });

        if (selectedRole && !allowedRoles.includes(String(selectedRole))) {
            jobRole.value = '';
        }
        jobRole.disabled = !client.value;
    }

    client.addEventListener('change', filterJobRoles);
    filterJobRoles();

    if (typeof Choices === 'undefined') {
        return;
    }

    const settings = {
        removeItemButton: true,
        searchEnabled: true,
        shouldSort: false,
        duplicateItemsAllowed: false,
        itemSelectText: '',
        noResultsText: 'No matching options found',
        noChoicesText: 'No more options available'
    };

    ['mode_ids', 'project_owner_ids', 'location_ids'].forEach(function (id) {
        const select = document.getElementById(id);
        if (!select || select.dataset.choicesInitialized === 'true') {
            return;
        }

        const initialValues = Array.from(select.selectedOptions).map(function (option) {
            return option.value;
        });
        const choices = new Choices(select, {
            ...settings,
            placeholder: true,
            placeholderValue: id === 'mode_ids'
                ? 'Select modes'
                : (id === 'project_owner_ids' ? 'Select project owners' : 'Select locations')
        });

        select.dataset.choicesInitialized = 'true';
        select.closest('form').addEventListener('reset', function () {
            window.setTimeout(function () {
                choices.removeActiveItems();
                if (initialValues.length) {
                    choices.setChoiceByValue(initialValues);
                }
            }, 0);
        });
    });
});
</script>
@endpush
