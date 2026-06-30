<div class="row gy-4">
    <div class="col-md-4">
        <label for="name" class="form-label">Recruiter Name <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $client->name ?? '') }}" placeholder="Enter recruiter name">
        @error('name')<span class="text-danger small">{{ $message }}</span>@enderror
    </div>
    <div class="col-md-4">
        <label for="contact_person" class="form-label">Location</label>
        <input type="text" class="form-control" id="contact_person" name="contact_person" value="{{ old('contact_person', $client->contact_person ?? '') }}" placeholder="Enter contact person">
        @error('contact_person')<span class="text-danger small">{{ $message }}</span>@enderror
    </div>
    <div class="col-md-4">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $client->email ?? '') }}" placeholder="Enter email">
        @error('email')<span class="text-danger small">{{ $message }}</span>@enderror
    </div>
    <div class="col-md-4">
        <label for="mobile_no" class="form-label">Mobile No</label>
        <input type="text" class="form-control" id="mobile_no" name="mobile_no" value="{{ old('mobile_no', $client->mobile_no ?? '') }}" placeholder="Enter mobile no">
        @error('mobile_no')<span class="text-danger small">{{ $message }}</span>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">Status</label>
        <div class="d-flex">
            <div class="form-check form-radio-success me-3">
                <input class="form-check-input" type="radio" name="status" id="status_active" value="1" {{ old('status', isset($client) ? (int) $client->status : 1) == 1 ? 'checked' : '' }}>
                <label class="form-check-label" for="status_active">Active</label>
            </div>
            <div class="form-check form-radio-danger ms-3">
                <input class="form-check-input" type="radio" name="status" id="status_inactive" value="0" {{ old('status', isset($client) ? (int) $client->status : 1) == 0 ? 'checked' : '' }}>
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
