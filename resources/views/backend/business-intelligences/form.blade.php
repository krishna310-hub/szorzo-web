<div class="row">
    <div class="col-md-4 mb-3"><label>Contact ID</label><input type="text" class="form-control" name="contact_id" value="{{ $model->contact_id ?? '' }}"></div>
    <div class="col-md-4 mb-3"><label>Account ID</label><input type="text" class="form-control" name="account_id" value="{{ $model->account_id ?? '' }}"></div>
    <div class="col-md-4 mb-3"><label>Account Name <span class="text-danger">*</span></label><input type="text" class="form-control" name="account_name" value="{{ $model->account_name ?? '' }}" required></div>
    <div class="col-md-4 mb-3"><label>Contact Name <span class="text-danger">*</span></label><input type="text" class="form-control" name="contact_name" value="{{ $model->contact_name ?? '' }}" required></div>
    <div class="col-md-4 mb-3"><label>Designation</label><input type="text" class="form-control" name="designation" value="{{ $model->designation ?? '' }}"></div>
    <div class="col-md-4 mb-3"><label>Department</label><input type="text" class="form-control" name="department" value="{{ $model->department ?? '' }}"></div>
    <div class="col-md-4 mb-3"><label>Mobile Number</label><input type="text" class="form-control" name="mobile_number" value="{{ $model->mobile_number ?? '' }}"></div>
    <div class="col-md-4 mb-3"><label>Alternate Contact</label><input type="text" class="form-control" name="alternate_contact" value="{{ $model->alternate_contact ?? '' }}"></div>
    <div class="col-md-4 mb-3"><label>Email ID</label><input type="email" class="form-control" name="email_id" value="{{ $model->email_id ?? '' }}"></div>
    <div class="col-md-4 mb-3"><label>Contact Type</label><input type="text" class="form-control" name="contact_type" value="{{ $model->contact_type ?? '' }}" placeholder="Decision Maker / Influencer / User / Procurement"></div>
    <div class="col-md-4 mb-3"><label>Last Contacted Date</label><input type="date" class="form-control" name="last_contacted_date" value="{{ isset($model->last_contacted_date) ? $model->last_contacted_date->format('Y-m-d') : '' }}"></div>
    <div class="col-md-4 mb-3"><label>Next Follow-up Date</label><input type="date" class="form-control" name="next_follow_up_date" value="{{ isset($model->next_follow_up_date) ? $model->next_follow_up_date->format('Y-m-d') : '' }}"></div>
    <div class="col-md-12 mb-3"><label>Contact Notes</label><textarea class="form-control" name="contact_notes">{{ $model->contact_notes ?? '' }}</textarea></div>
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