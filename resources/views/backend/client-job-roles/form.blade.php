<div class="row gy-4">
    <div class="col-md-4">
        <label for="client_id" class="form-label">Client <span class="text-danger">*</span></label>
        <select class="form-select" id="client_id" name="client_id">
            <option value="">Select client</option>
            @foreach($clients as $client)
                <option value="{{ $client->id }}" {{ old('client_id', $clientJobRole->client_id ?? '') == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
            @endforeach
        </select>
        @error('client_id')<span class="text-danger small">{{ $message }}</span>@enderror
    </div>
    <div class="col-md-4">
        <label for="job_role_id" class="form-label">Job Role <span class="text-danger">*</span></label>
        <select class="form-select" id="job_role_id" name="job_role_id">
            <option value="">Select job role</option>
            @foreach($jobRoles as $jobRole)
                <option value="{{ $jobRole->id }}" {{ old('job_role_id', $clientJobRole->job_role_id ?? '') == $jobRole->id ? 'selected' : '' }}>{{ $jobRole->name }}</option>
            @endforeach
        </select>
        @error('job_role_id')<span class="text-danger small">{{ $message }}</span>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">Status</label>
        <div class="d-flex">
            <div class="form-check form-radio-success me-3">
                <input class="form-check-input" type="radio" name="status" id="status_active" value="1" {{ old('status', isset($clientJobRole) ? (int) $clientJobRole->status : 1) == 1 ? 'checked' : '' }}>
                <label class="form-check-label" for="status_active">Active</label>
            </div>
            <div class="form-check form-radio-danger ms-3">
                <input class="form-check-input" type="radio" name="status" id="status_inactive" value="0" {{ old('status', isset($clientJobRole) ? (int) $clientJobRole->status : 1) == 0 ? 'checked' : '' }}>
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
