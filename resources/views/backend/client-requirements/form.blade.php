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
    <div class="col-md-4">
        <label for="billing" class="form-label">Billing</label>
        <select class="form-select" id="billing_id" name="billing_id">
            <option value="">Select Billing</option>
            @foreach ($billing as $bill)
                <option value="{{ $bill->id }}"
                    {{ old('billing_id', $clientRequirement->billing_id ?? '') == $bill->id ? 'selected' : '' }}>
                    {{ $bill->value }}</option>
            @endforeach
        </select>
        @error('billing_id')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-4"><label for="billing_amount" class="form-label">Revenue Amount</label><input type="number"
            step="0.01" min="0" max="100" class="form-control" id="billing" name="billing"
            value="{{ old('billing', $clientRequirement->billing ?? '') }}">
        @error('billing')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-4"><label for="job_description_id" class="form-label">Job Description</label><select
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
    </div>
    <div class="col-md-4"><label for="mode_id" class="form-label">Mode</label><select class="form-select"
            id="mode_id" name="mode_id">
            <option value="">Select mode</option>
            @foreach ($modes as $mode)
                <option value="{{ $mode->id }}"
                    {{ old('mode_id', $clientRequirement->mode_id ?? '') == $mode->id ? 'selected' : '' }}>
                    {{ $mode->mode }}</option>
            @endforeach
        </select>
        @error('mode_id')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-4"><label for="requirement_open_date" class="form-label">Requirement Open Date</label><input
            type="date" class="form-control" id="requirement_open_date" name="requirement_open_date"
            value="{{ old('requirement_open_date', isset($clientRequirement) && $clientRequirement->requirement_open_date ? $clientRequirement->requirement_open_date->format('Y-m-d') : '') }}">
        @error('requirement_open_date')
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
    <div class="col-md-4"><label for="project_owner" class="form-label">Project Owner</label><select
            class="form-select" id="project_owner" name="project_owner">
            <option value="">Select project owner</option>
            @foreach ($recruiters as $recruiter)
                <option value="{{ $recruiter->id }}"
                    {{ old('project_owner', $clientRequirement->project_owner ?? '') == $recruiter->id ? 'selected' : '' }}>
                    {{ $recruiter->recruiter_name }}</option>
            @endforeach
        </select>
        @error('project_owner')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-4"><label for="ctc" class="form-label">CTC</label><input type="number" step="0.01"
            min="0" class="form-control" id="ctc" name="ctc"
            value="{{ old('ctc', $clientRequirement->ctc ?? '') }}">
        @error('ctc')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-4"><label for="location_id" class="form-label">Location</label><select class="form-select"
            id="location_id" name="location_id">
            <option value="">Select location</option>
            @foreach ($locations as $location)
                <option value="{{ $location->id }}"
                    {{ old('location_id', $clientRequirement->location_id ?? '') == $location->id ? 'selected' : '' }}>
                    {{ $location->location }}</option>
            @endforeach
        </select>
        @error('location_id')
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
</div>
<div class="d-flex gap-3 mt-5 justify-content-center"><button type="reset"
        class="btn btn-danger">Clear</button><button type="submit" class="btn btn-success">Submit</button></div>
