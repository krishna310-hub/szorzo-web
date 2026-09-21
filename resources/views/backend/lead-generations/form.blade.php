<div class="row">
    <div class="col-md-4 mb-3"><label>Account ID</label><input type="text" class="form-control" name="account_id" value="{{ $model->account_id ?? '' }}"></div>
    <div class="col-md-4 mb-3"><label>Account / Lead Source</label><input type="text" class="form-control" name="account_source" value="{{ $model->account_source ?? '' }}"></div>
    <div class="col-md-4 mb-3"><label>Account Name (Display) <span class="text-danger">*</span></label><input type="text" class="form-control" name="account_name" value="{{ $model->account_name ?? '' }}" required></div>
    <div class="col-md-4 mb-3">
        <label>Industry (Division)</label>
        <select class="form-select" name="industry">
            <option value="">Select Industry</option>
            @foreach(\App\Models\Division::where('status', 1)->get() as $division)
                <option value="{{ $division->name }}" {{ (isset($model) && $model->industry == $division->name) ? 'selected' : '' }}>{{ $division->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 mb-3"><label>Sub Industry</label><input type="text" class="form-control" name="sub_industry" value="{{ $model->sub_industry ?? '' }}"></div>
    <div class="col-md-4 mb-3"><label>Website URL</label><input type="url" class="form-control" name="website_url" value="{{ $model->website_url ?? '' }}"></div>
    <div class="col-md-4 mb-3">
        <label>Country of Origin</label>
        <select class="form-select" name="country_of_origin">
            <option value="">Select Country</option>
            @foreach(\App\Models\Country::all() as $country)
                <option value="{{ $country->name }}" {{ (isset($model) && $model->country_of_origin == $country->name) ? 'selected' : '' }}>{{ $country->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 mb-3"><label>Region</label><input type="text" class="form-control" name="region" value="{{ $model->region ?? '' }}"></div>
    <div class="col-md-4 mb-3">
        <label>State</label>
        <select class="form-select" name="state">
            <option value="">Select State</option>
            @foreach(\App\Models\State::all() as $state)
                <option value="{{ $state->name }}" {{ (isset($model) && $model->state == $state->name) ? 'selected' : '' }}>{{ $state->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 mb-3">
        <label>City</label>
        <select class="form-select" name="city">
            <option value="">Select City</option>
            @foreach(\App\Models\City::all() as $city)
                <option value="{{ $city->name }}" {{ (isset($model) && $model->city == $city->name) ? 'selected' : '' }}>{{ $city->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 mb-3"><label>PIN Code</label><input type="text" class="form-control" name="pin_code" value="{{ $model->pin_code ?? '' }}"></div>
    <div class="col-md-12 mb-3"><label>Registered Address</label><textarea class="form-control" name="registered_address">{{ $model->registered_address ?? '' }}</textarea></div>
    <div class="col-md-4 mb-3"><label>Ownership Type</label><input type="text" class="form-control" name="ownership_type" value="{{ $model->ownership_type ?? '' }}"></div>
    <div class="col-md-4 mb-3"><label>Registration / Entity ID</label><input type="text" class="form-control" name="registration_id" value="{{ $model->registration_id ?? '' }}"></div>
    <div class="col-md-4 mb-3"><label>GSTIN / Tax ID</label><input type="text" class="form-control" name="gstin" value="{{ $model->gstin ?? '' }}"></div>
    <div class="col-md-4 mb-3"><label>Account Owner</label><input type="text" class="form-control" name="account_owner" value="{{ $model->account_owner ?? '' }}"></div>
    <div class="col-md-4 mb-3"><label>Relationship Manager</label><input type="text" class="form-control" name="relationship_manager" value="{{ $model->relationship_manager ?? '' }}"></div>
    <div class="col-md-4 mb-3"><label>Customer Since</label><input type="text" class="form-control" name="customer_since" value="{{ $model->customer_since ?? '' }}"></div>
    <div class="col-md-4 mb-3"><label>Account Created Date</label><input type="date" class="form-control" name="account_created_date" value="{{ isset($model->account_created_date) ? $model->account_created_date->format('Y-m-d') : '' }}"></div>
    <div class="col-md-4 mb-3"><label>Last Updated Date</label><input type="date" class="form-control" name="last_updated_date" value="{{ isset($model->last_updated_date) ? $model->last_updated_date->format('Y-m-d') : '' }}"></div>
    <div class="col-md-4 mb-3">
        <label>Status <span class="text-danger">*</span></label>
        <div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="status" id="statusActive" value="1" {{ (isset($model) && $model->status == 1) || !isset($model) ? 'checked' : '' }} required>
                <label class="form-check-label" for="statusActive">Active</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="status" id="statusInactive" value="0" {{ isset($model) && $model->status == 0 ? 'checked' : '' }} required>
                <label class="form-check-label" for="statusInactive">Inactive</label>
            </div>
        </div>
    </div>
</div>