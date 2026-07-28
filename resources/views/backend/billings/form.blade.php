<div class="row gy-4">

    <div class="col-md-4"><label for="value" class="form-label">Value <span class="text-danger">*</span></label><input type="number" step="0.01"
            min="0" max="100" class="form-control" id="value" name="value"
            value="{{ old('value', $billing->value ?? '') }}">
        @error('value')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>


    <div class="col-md-6"><label class="form-label">Status</label>
        <div class="d-flex">
            <div class="form-check form-radio-success me-3"><input class="form-check-input" type="radio"
                    name="status" id="status_active" value="1"
                    {{ old('status', isset($billing) ? (int) $billing->status : 1) == 1 ? 'checked' : '' }}><label
                    class="form-check-label" for="status_active">Active</label></div>
            <div class="form-check form-radio-danger ms-3"><input class="form-check-input" type="radio" name="status"
                    id="status_inactive" value="0"
                    {{ old('status', isset($billing) ? (int) $billing->status : 1) == 0 ? 'checked' : '' }}><label
                    class="form-check-label" for="status_inactive">Inactive</label></div>
        </div>
        @error('status')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
</div>
<div class="d-flex gap-3 mt-5 justify-content-center"><button type="reset"
        class="btn btn-danger">Clear</button><button type="submit" class="btn btn-success">Submit</button></div>
