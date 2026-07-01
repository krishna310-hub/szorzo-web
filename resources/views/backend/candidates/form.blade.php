<div class="row gy-4">
    <div class="col-md-4">
        <label for="name" class="form-label">Candidate Name <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $candidate->name ?? '') }}" placeholder="Enter candidate name">
        @error('name')<span class="text-danger small">{{ $message }}</span>@enderror
    </div>
    <div class="col-md-4">
        <label for="profile_image" class="form-label">Profile Image</label>
        <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*">
        @if(isset($candidate) && $candidate->profile_image)
            <img src="{{ asset($candidate->profile_image) }}" alt="Candidate image" class="rounded mt-2" width="70" height="70" style="object-fit: cover;">
        @endif
        @error('profile_image')<span class="text-danger small">{{ $message }}</span>@enderror
    </div>
    <div class="col-md-4">
        <label for="location_id" class="form-label">Location</label>
        <select class="form-select" id="location_id" name="location_id">
            <option value="">Select location</option>
            @foreach($locations as $location)
                <option value="{{ $location->id }}" {{ old('location_id', $candidate->location_id ?? '') == $location->id ? 'selected' : '' }}>{{ $location->name }}</option>
            @endforeach
        </select>
        @error('location_id')<span class="text-danger small">{{ $message }}</span>@enderror
    </div>
    <div class="col-md-4">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $candidate->email ?? '') }}" placeholder="Enter email">
        @error('email')<span class="text-danger small">{{ $message }}</span>@enderror
    </div>
    <div class="col-md-4">
        <label for="mobile_no" class="form-label">Mobile No</label>
        <input type="text" class="form-control" id="mobile_no" name="mobile_no" value="{{ old('mobile_no', $candidate->mobile_no ?? '') }}" placeholder="Enter mobile no">
        @error('mobile_no')<span class="text-danger small">{{ $message }}</span>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">Status</label>
        <div class="d-flex">
            <div class="form-check form-radio-success me-3">
                <input class="form-check-input" type="radio" name="status" id="status_active" value="1" {{ old('status', isset($candidate) ? (int) $candidate->status : 1) == 1 ? 'checked' : '' }}>
                <label class="form-check-label" for="status_active">Active</label>
            </div>
            <div class="form-check form-radio-danger ms-3">
                <input class="form-check-input" type="radio" name="status" id="status_inactive" value="0" {{ old('status', isset($candidate) ? (int) $candidate->status : 1) == 0 ? 'checked' : '' }}>
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
