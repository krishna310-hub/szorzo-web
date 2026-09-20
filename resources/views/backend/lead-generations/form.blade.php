<div class="row">
    <div class="col-md-6 mb-3"><label>Title <span class="text-danger">*</span></label><input type="text" class="form-control" name="title" value="{{ $model->title ?? '' }}" required></div>
    <div class="col-md-6 mb-3"><label>Lead Source</label><input type="text" class="form-control" name="lead_source" value="{{ $model->lead_source ?? '' }}"></div>
    <div class="col-md-6 mb-3"><label>Lead Owner</label><input type="text" class="form-control" name="lead_owner" value="{{ $model->lead_owner ?? '' }}"></div>
    <div class="col-md-6 mb-3"><label>Relationship Manager</label><input type="text" class="form-control" name="relationship_manager" value="{{ $model->relationship_manager ?? '' }}"></div>
    <div class="col-md-6 mb-3"><label>Contact Info</label><input type="text" class="form-control" name="contact_info" value="{{ $model->contact_info ?? '' }}"></div>
    <div class="col-md-6 mb-3"><label>Lead Date</label><input type="date" class="form-control" name="lead_date" value="{{ isset($model) && $model->lead_date ? $model->lead_date->format('Y-m-d') : '' }}"></div>
    <div class="col-md-6 mb-3"><label>Follow Up Date</label><input type="date" class="form-control" name="follow_up_date" value="{{ isset($model) && $model->follow_up_date ? $model->follow_up_date->format('Y-m-d') : '' }}"></div>
    <div class="col-md-12 mb-3"><label>Notes</label><textarea class="form-control" name="notes">{{ $model->notes ?? '' }}</textarea></div>
    <div class="col-md-6 mb-3">
        <label>Status <span class="text-danger">*</span></label>
        <select name="status" class="form-select" required>
            <option value="1" {{ isset($model) && $model->status == 1 ? 'selected' : '' }}>Active</option>
            <option value="0" {{ isset($model) && $model->status == 0 ? 'selected' : '' }}>Inactive</option>
        </select>
    </div>
</div>