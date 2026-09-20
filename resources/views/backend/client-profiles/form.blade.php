<div class="row">
    <div class="col-md-6 mb-3"><label>Account Source</label><input type="text" class="form-control" name="account_source" value="{{ $model->account_source ?? '' }}"></div>
    <div class="col-md-6 mb-3"><label>Account Name <span class="text-danger">*</span></label><input type="text" class="form-control" name="account_name" value="{{ $model->account_name ?? '' }}" required></div>
    <div class="col-md-6 mb-3"><label>Industry</label><input type="text" class="form-control" name="industry" value="{{ $model->industry ?? '' }}"></div>
    <div class="col-md-6 mb-3"><label>Sub Industry</label><input type="text" class="form-control" name="sub_industry" value="{{ $model->sub_industry ?? '' }}"></div>
    <div class="col-md-6 mb-3"><label>Website URL</label><input type="url" class="form-control" name="website_url" value="{{ $model->website_url ?? '' }}"></div>
    <div class="col-md-6 mb-3"><label>Country of Origin</label><input type="text" class="form-control" name="country_of_origin" value="{{ $model->country_of_origin ?? '' }}"></div>
    <div class="col-md-6 mb-3"><label>Region</label><input type="text" class="form-control" name="region" value="{{ $model->region ?? '' }}"></div>
    <div class="col-md-6 mb-3"><label>State</label><input type="text" class="form-control" name="state" value="{{ $model->state ?? '' }}"></div>
    <div class="col-md-6 mb-3"><label>City</label><input type="text" class="form-control" name="city" value="{{ $model->city ?? '' }}"></div>
    <div class="col-md-6 mb-3"><label>PIN Code</label><input type="text" class="form-control" name="pin_code" value="{{ $model->pin_code ?? '' }}"></div>
    <div class="col-md-12 mb-3"><label>Registered Address</label><textarea class="form-control" name="registered_address">{{ $model->registered_address ?? '' }}</textarea></div>
    <div class="col-md-6 mb-3"><label>Ownership Type</label><input type="text" class="form-control" name="ownership_type" value="{{ $model->ownership_type ?? '' }}"></div>
    <div class="col-md-6 mb-3"><label>Registration / Entity ID</label><input type="text" class="form-control" name="registration_id" value="{{ $model->registration_id ?? '' }}"></div>
    <div class="col-md-6 mb-3"><label>GSTIN / Tax ID</label><input type="text" class="form-control" name="gstin" value="{{ $model->gstin ?? '' }}"></div>
    <div class="col-md-6 mb-3"><label>Account Owner</label><input type="text" class="form-control" name="account_owner" value="{{ $model->account_owner ?? '' }}"></div>
    <div class="col-md-6 mb-3"><label>Relationship Manager</label><input type="text" class="form-control" name="relationship_manager" value="{{ $model->relationship_manager ?? '' }}"></div>
    <div class="col-md-6 mb-3"><label>Customer Since</label><input type="text" class="form-control" name="customer_since" value="{{ $model->customer_since ?? '' }}"></div>
    <div class="col-md-6 mb-3">
        <label>Status <span class="text-danger">*</span></label>
        <select name="status" class="form-select" required>
            <option value="1" {{ isset($model) && $model->status == 1 ? 'selected' : '' }}>Active</option>
            <option value="0" {{ isset($model) && $model->status == 0 ? 'selected' : '' }}>Inactive</option>
        </select>
    </div>
</div>