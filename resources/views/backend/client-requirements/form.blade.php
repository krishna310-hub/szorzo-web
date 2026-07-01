<div class="row gy-4">
    <div class="col-md-4">
        <label for="client_id" class="form-label">Client <span class="text-danger">*</span></label>
        <select class="form-select" id="client_id" name="client_id">
            <option value="">Select client</option>
            @foreach($clients as $client)
                <option value="{{ $client->id }}" {{ old('client_id', $clientRequirement->client_id ?? '') == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
            @endforeach
        </select>
        @error('client_id')<span class="text-danger small">{{ $message }}</span>@enderror
    </div>
    <div class="col-md-4">
        <label for="billing_percentage" class="form-label">Billing %</label>
        <input type="number" step="0.01" min="0" max="100" class="form-control" id="billing_percentage" name="billing_percentage" value="{{ old('billing_percentage', $clientRequirement->billing_percentage ?? '') }}" placeholder="Enter billing percentage">
        @error('billing_percentage')<span class="text-danger small">{{ $message }}</span>@enderror
    </div>
    <div class="col-md-4">
        <label for="job_description_id" class="form-label">Job Description ID</label>
        <input type="text" class="form-control" id="job_description_id" name="job_description_id" value="{{ old('job_description_id', $clientRequirement->job_description_id ?? '') }}" placeholder="Enter job description ID">
        @error('job_description_id')<span class="text-danger small">{{ $message }}</span>@enderror
    </div>
    <div class="col-md-4">
        <label for="mode_id" class="form-label">Mode</label>
        <select class="form-select" id="mode_id" name="mode_id">
            <option value="">Select mode</option>
            @foreach($modes as $mode)
                <option value="{{ $mode->id }}" {{ old('mode_id', $clientRequirement->mode_id ?? '') == $mode->id ? 'selected' : '' }}>{{ $mode->name }}</option>
            @endforeach
        </select>
        @error('mode_id')<span class="text-danger small">{{ $message }}</span>@enderror
    </div>
    <div class="col-md-4">
        <label for="open_date" class="form-label">Open Date</label>
        <input type="date" class="form-control" id="open_date" name="open_date" value="{{ old('open_date', isset($clientRequirement) && $clientRequirement->open_date ? $clientRequirement->open_date->format('Y-m-d') : '') }}">
        @error('open_date')<span class="text-danger small">{{ $message }}</span>@enderror
    </div>
    <div class="col-md-4">
        <label for="job_role_id" class="form-label">Job Role</label>
        <select class="form-select" id="job_role_id" name="job_role_id">
            <option value="">Select job role</option>
            @foreach($jobRoles as $jobRole)
                <option value="{{ $jobRole->id }}" {{ old('job_role_id', $clientRequirement->job_role_id ?? '') == $jobRole->id ? 'selected' : '' }}>{{ $jobRole->name }}</option>
            @endforeach
        </select>
        @error('job_role_id')<span class="text-danger small">{{ $message }}</span>@enderror
    </div>
    <div class="col-md-4">
        <label for="ctc" class="form-label">CTC</label>
        <input type="number" step="0.01" min="0" class="form-control" id="ctc" name="ctc" value="{{ old('ctc', $clientRequirement->ctc ?? '') }}" placeholder="Enter CTC">
        @error('ctc')<span class="text-danger small">{{ $message }}</span>@enderror
    </div>
    <div class="col-md-4">
        <label for="location_id" class="form-label">Location</label>
        <select class="form-select" id="location_id" name="location_id">
            <option value="">Select location</option>
            @foreach($locations as $location)
                <option value="{{ $location->id }}" {{ old('location_id', $clientRequirement->location_id ?? '') == $location->id ? 'selected' : '' }}>{{ $location->name }}</option>
            @endforeach
        </select>
        @error('location_id')<span class="text-danger small">{{ $message }}</span>@enderror
    </div>
    <div class="col-md-4">
        <label for="no_of_positions" class="form-label">No. Of Positions</label>
        <input type="number" min="0" class="form-control" id="no_of_positions" name="no_of_positions" value="{{ old('no_of_positions', $clientRequirement->no_of_positions ?? 0) }}">
        @error('no_of_positions')<span class="text-danger small">{{ $message }}</span>@enderror
    </div>
    <div class="col-md-4">
        <label for="closure_target_date" class="form-label">Closure Target Date</label>
        <input type="date" class="form-control" id="closure_target_date" name="closure_target_date" value="{{ old('closure_target_date', isset($clientRequirement) && $clientRequirement->closure_target_date ? $clientRequirement->closure_target_date->format('Y-m-d') : '') }}">
        @error('closure_target_date')<span class="text-danger small">{{ $message }}</span>@enderror
    </div>
    <div class="col-md-4">
        <label for="cvs_required" class="form-label">CV's Required</label>
        <input type="number" min="0" class="form-control" id="cvs_required" name="cvs_required" value="{{ old('cvs_required', $clientRequirement->cvs_required ?? 0) }}">
        @error('cvs_required')<span class="text-danger small">{{ $message }}</span>@enderror
    </div>
    <div class="col-md-4">
        <label for="cvs_uploaded" class="form-label">CV's Uploaded</label>
        <input type="number" min="0" class="form-control" id="cvs_uploaded" name="cvs_uploaded" value="{{ old('cvs_uploaded', $clientRequirement->cvs_uploaded ?? 0) }}">
        @error('cvs_uploaded')<span class="text-danger small">{{ $message }}</span>@enderror
    </div>
    <div class="col-md-4">
        <label for="project_owner_id" class="form-label">Project Owner</label>
        <select class="form-select" id="project_owner_id" name="project_owner_id">
            <option value="">Select project owner</option>
            @foreach($recruiters as $recruiter)
                <option value="{{ $recruiter->id }}" {{ old('project_owner_id', $clientRequirement->project_owner_id ?? '') == $recruiter->id ? 'selected' : '' }}>{{ $recruiter->name }}</option>
            @endforeach
        </select>
        @error('project_owner_id')<span class="text-danger small">{{ $message }}</span>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">Status</label>
        <div class="d-flex">
            <div class="form-check form-radio-success me-3">
                <input class="form-check-input" type="radio" name="status" id="status_active" value="1" {{ old('status', isset($clientRequirement) ? (int) $clientRequirement->status : 1) == 1 ? 'checked' : '' }}>
                <label class="form-check-label" for="status_active">Active</label>
            </div>
            <div class="form-check form-radio-danger ms-3">
                <input class="form-check-input" type="radio" name="status" id="status_inactive" value="0" {{ old('status', isset($clientRequirement) ? (int) $clientRequirement->status : 1) == 0 ? 'checked' : '' }}>
                <label class="form-check-label" for="status_inactive">Inactive</label>
            </div>
        </div>
        @error('status')<span class="text-danger small">{{ $message }}</span>@enderror
    </div>
</div>
<div class="d-flex gap-3 mt-5 justify-content-center">
    <button type="reset" class="btn btn-danger">Clear</button>
    <button type="submit" class="btn btn-success">Submit</button>
</div>
