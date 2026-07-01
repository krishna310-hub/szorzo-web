<div class="row gy-4">
    <div class="col-md-4">
        <label for="recruiter_name" class="form-label">Recruiter Name <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="recruiter_name" name="recruiter_name" value="{{ old('recruiter_name', $recruiter->recruiter_name ?? '') }}" placeholder="Enter recruiter name">
        @error('recruiter_name')<span class="text-danger small">{{ $message }}</span>@enderror
    </div>
    <div class="col-md-4">
        <label for="location" class="form-label">Location</label>
        <input type="text" class="form-control" id="location" name="location" value="{{ old('location', $recruiter->location ?? '') }}" placeholder="Enter location">
        @error('location')<span class="text-danger small">{{ $message }}</span>@enderror
    </div>
    <div class="col-md-4">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $recruiter->email ?? '') }}" placeholder="Enter email">
        @error('email')<span class="text-danger small">{{ $message }}</span>@enderror
    </div>
    <div class="col-md-4">
        <label for="mobile_number" class="form-label">Mobile Number</label>
        <input type="text" class="form-control" id="mobile_number" name="mobile_number" value="{{ old('mobile_number', $recruiter->mobile_number ?? '') }}" placeholder="Enter mobile number">
        @error('mobile_number')<span class="text-danger small">{{ $message }}</span>@enderror
    </div>
    {{-- <div class="col-md-4">
        <label for="performance_rating" class="form-label">Performance Rating</label>
        <input type="number" step="0.01" min="0" max="10" class="form-control" id="performance_rating" name="performance_rating" value="{{ old('performance_rating', $recruiter->performance_rating ?? '') }}" placeholder="Enter performance rating">
        @error('performance_rating')<span class="text-danger small">{{ $message }}</span>@enderror
    </div> --}}
    <div class="col-md-4">
        <label class="form-label">Status</label>
        <div class="d-flex">
            <div class="form-check form-radio-success me-3">
                <input class="form-check-input" type="radio" name="status" id="status_active" value="1" {{ old('status', isset($recruiter) ? (int) $recruiter->status : 1) == 1 ? 'checked' : '' }}>
                <label class="form-check-label" for="status_active">Active</label>
            </div>
            <div class="form-check form-radio-danger ms-3">
                <input class="form-check-input" type="radio" name="status" id="status_inactive" value="0" {{ old('status', isset($recruiter) ? (int) $recruiter->status : 1) == 0 ? 'checked' : '' }}>
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
