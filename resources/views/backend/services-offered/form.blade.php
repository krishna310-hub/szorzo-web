<div class="row">
    <div class="col-md-6 mb-3"><label>Service Name <span class="text-danger">*</span></label><input type="text" class="form-control" name="service_name" value="{{ $model->service_name ?? '' }}" required></div>
    <div class="col-md-6 mb-3"><label>Service Code</label><input type="text" class="form-control" name="service_code" value="{{ $model->service_code ?? '' }}"></div>
    <div class="col-md-6 mb-3"><label>Category</label><input type="text" class="form-control" name="category" value="{{ $model->category ?? '' }}"></div>
    <div class="col-md-12 mb-3"><label>Description</label><textarea class="form-control" name="description">{{ $model->description ?? '' }}</textarea></div>
    <div class="col-md-6 mb-3"><label>Display Order</label><input type="number" class="form-control" name="display_order" value="{{ $model->display_order ?? 0 }}"></div>
    <div class="col-md-6 mb-3">
        <label>Status <span class="text-danger">*</span></label>
        <select name="status" class="form-select" required>
            <option value="1" {{ isset($model) && $model->status == 1 ? 'selected' : '' }}>Active</option>
            <option value="0" {{ isset($model) && $model->status == 0 ? 'selected' : '' }}>Inactive</option>
        </select>
    </div>
</div>