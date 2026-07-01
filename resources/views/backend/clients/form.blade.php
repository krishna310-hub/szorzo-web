<div class="row gy-4">
    <div class="col-md-4">
        <label for="client" class="form-label">Client <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="client" name="client" value="{{ old('client', $client->client ?? '') }}">
        @error('client')<span class="text-danger small">{{ $message }}</span>@enderror
    </div>
    <div class="col-md-4">
        <label for="billing" class="form-label">Billing</label>
        <input type="number" step="0.01" min="0" max="100" class="form-control" id="billing" name="billing" value="{{ old('billing', $client->billing ?? '') }}">
        @error('billing')<span class="text-danger small">{{ $message }}</span>@enderror
    </div>
    <div class="col-md-4">
        <label for="location_id" class="form-label">Location</label>
        <select class="form-select" id="location_id" name="location_id">
            <option value="">Select location</option>
            @foreach($locations as $location)
                <option value="{{ $location->id }}" {{ old('location_id', $client->location_id ?? '') == $location->id ? 'selected' : '' }}>{{ $location->location }}</option>
            @endforeach
        </select>
        @error('location_id')<span class="text-danger small">{{ $message }}</span>@enderror
    </div>
    <div class="col-md-4">
        <label for="poc_name" class="form-label">PoC Name</label>
        <input type="text" class="form-control" id="poc_name" name="poc_name" value="{{ old('poc_name', $client->poc_name ?? '') }}">
        @error('poc_name')<span class="text-danger small">{{ $message }}</span>@enderror
    </div>
    <div class="col-md-4">
        <label for="signed_date" class="form-label">Signed Date</label>
        <input type="date" class="form-control" id="signed_date" name="signed_date" value="{{ old('signed_date', isset($client) && $client->signed_date ? $client->signed_date->format('Y-m-d') : '') }}">
        @error('signed_date')<span class="text-danger small">{{ $message }}</span>@enderror
    </div>
    <div class="col-md-4">
        <label for="renewal_date" class="form-label">Renewal Date</label>
        <input type="date" class="form-control" id="renewal_date" name="renewal_date" value="{{ old('renewal_date', isset($client) && $client->renewal_date ? $client->renewal_date->format('Y-m-d') : '') }}">
        @error('renewal_date')<span class="text-danger small">{{ $message }}</span>@enderror
    </div>
    <div class="col-md-4">
        <label for="division_id" class="form-label">Division</label>
        <select class="form-select" id="division_id" name="division_id">
            <option value="">Select division</option>
            @foreach($divisions as $division)
                <option value="{{ $division->id }}" {{ old('division_id', $client->division_id ?? '') == $division->id ? 'selected' : '' }}>{{ $division->name }}</option>
            @endforeach
        </select>
        @error('division_id')<span class="text-danger small">{{ $message }}</span>@enderror
    </div>
    <div class="col-md-4">
        <label for="contact_number" class="form-label">Contact Number</label>
        <input type="text" class="form-control" id="contact_number" name="contact_number" value="{{ old('contact_number', $client->contact_number ?? '') }}">
        @error('contact_number')<span class="text-danger small">{{ $message }}</span>@enderror
    </div>
    <div class="col-md-4">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $client->email ?? '') }}">
        @error('email')<span class="text-danger small">{{ $message }}</span>@enderror
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
