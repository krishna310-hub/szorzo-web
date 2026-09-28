<div class="row">
    <div class="col-md-4 mb-3">
        <label>Assigned Sales Person</label>
        <select class="form-select" name="assigned_to" {{ auth()->user()->isSales() ? 'disabled' : '' }}>
            <option value="">Unassigned</option>
            @foreach(($salesUsers ?? collect()) as $salesUser)
                <option value="{{ $salesUser->id }}" {{ (isset($model) && $model->assigned_to == $salesUser->id) || (!isset($model) && auth()->id() == $salesUser->id) ? 'selected' : '' }}>{{ $salesUser->name }}</option>
            @endforeach
        </select>
        @if(auth()->user()->isSales())<input type="hidden" name="assigned_to" value="{{ auth()->id() }}">@endif
    </div>
    @if(!auth()->user()->isSales())<div class="col-md-4 mb-3"><label>Assignment / Reassignment Reason</label><input type="text" class="form-control" name="assignment_reason" value="{{ old('assignment_reason') }}" placeholder="e.g. assigned by sales coordinator"></div>@endif
    <div class="col-md-4 mb-3">
        <label>Pipeline Stage <span class="text-danger">*</span></label>
        <select class="form-select" name="pipeline_stage" required>
            @foreach(\App\Models\LeadGeneration::STAGES as $value => $label)
                <option value="{{ $value }}" {{ (isset($model) && $model->pipeline_stage === $value) || (!isset($model) && $value === 'new') ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 mb-3"><label>Opportunity / Proposal Status</label><input type="text" class="form-control" name="opportunity_status" value="{{ $model->opportunity_status ?? '' }}" placeholder="e.g. proposal sent"></div>
    <div class="col-md-4 mb-3"><label>Priority <span class="text-danger">*</span></label><select class="form-select" name="priority" required>@foreach(['low'=>'Low','medium'=>'Medium','high'=>'High'] as $value => $label)<option value="{{ $value }}" @selected(($model->priority ?? 'medium') === $value)>{{ $label }}</option>@endforeach</select></div>
    <div class="col-md-4 mb-3"><label>Contact Person</label><input type="text" class="form-control" name="contact_person" value="{{ $model->contact_person ?? '' }}"></div>
    <div class="col-md-4 mb-3"><label>Mobile</label><input type="tel" class="form-control" name="mobile" value="{{ $model->mobile ?? '' }}"></div>
    <div class="col-md-4 mb-3"><label>Email</label><input type="email" class="form-control" name="email" value="{{ $model->email ?? '' }}"></div>
    <div class="col-md-4 mb-3"><label>Interested Service</label><select class="form-select" name="interested_service"><option value="">Select Service</option>@foreach(\App\Models\ServiceOffered::where('status', 1)->orderBy('service_name')->get() as $service)<option value="{{ $service->service_name }}" @selected(($model->interested_service ?? '') === $service->service_name)>{{ $service->service_name }}</option>@endforeach</select></div>
    <div class="col-md-4 mb-3"><label>Lost Reason</label><select class="form-select" name="lost_reason"><option value="">Select if lost</option>@foreach(['price'=>'Price','competitor'=>'Competitor','no_requirement'=>'No Requirement','no_response'=>'No Response','duplicate'=>'Duplicate','budget'=>'Budget Issue','other'=>'Other'] as $value => $label)<option value="{{ $value }}" @selected(($model->lost_reason ?? '') === $value)>{{ $label }}</option>@endforeach</select></div>
    <div class="col-md-4 mb-3"><label>Competitor</label><input type="text" class="form-control" name="competitor" value="{{ $model->competitor ?? '' }}"></div>
    <div class="col-md-4 mb-3"><label>Nurture / Re-contact Date</label><input type="datetime-local" class="form-control" name="nurture_at" value="{{ isset($model->nurture_at) ? $model->nurture_at->format('Y-m-d\TH:i') : '' }}"></div>
    <div class="col-md-4 mb-3"><label>Agreement Status</label><select class="form-select" name="agreement_status"><option value="">Not started</option>@foreach(['draft'=>'Draft','sent'=>'Sent','under_review'=>'Under review','signed'=>'Signed','rejected'=>'Rejected'] as $value => $label)<option value="{{ $value }}" @selected(($model->agreement_status ?? '') === $value)>{{ $label }}</option>@endforeach</select></div>
    @if(isset($model) && $model->next_follow_up_at)<div class="col-12"><div class="alert alert-info mb-3"><strong>Next follow-up:</strong> {{ $model->next_follow_up_at->format('d M Y, h:i A') }} — {{ $model->next_action ?: 'No action detail' }} ({{ ucfirst($model->follow_up_status ?: 'pending') }})</div></div>@endif
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
        <select class="form-select" name="country_of_origin" id="country_id">
            <option value="">Select Country</option>
            @foreach(\App\Models\Country::all() as $country)
                <option value="{{ $country->name }}" data-id="{{ $country->id }}" {{ (isset($model) && $model->country_of_origin == $country->name) ? 'selected' : '' }}>{{ $country->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 mb-3"><label>Region</label><input type="text" class="form-control" name="region" value="{{ $model->region ?? '' }}"></div>
    <div class="col-md-4 mb-3">
        <label>State</label>
        @php
            $selectedCountry = isset($model) && $model->country_of_origin
                ? \App\Models\Country::where('name', $model->country_of_origin)->first()
                : null;
            $states = $selectedCountry ? \App\Models\State::where('country_id', $selectedCountry->id)->get() : collect();
        @endphp
        <select class="form-select" name="state" id="state_id">
            <option value="">Select State</option>
            @foreach($states as $state)
                <option value="{{ $state->name }}" data-id="{{ $state->id }}" @selected(isset($model) && $model->state == $state->name)>{{ $state->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 mb-3">
        <label>City</label>
        @php
            $selectedState = isset($model) && $model->state
                ? \App\Models\State::where('name', $model->state)->first()
                : null;
            $cities = $selectedState ? \App\Models\City::where('state_id', $selectedState->id)->get() : collect();
        @endphp
        <select class="form-select" name="city" id="city_id">
            <option value="">Select City</option>
            @foreach($cities as $city)
                <option value="{{ $city->name }}" @selected(isset($model) && $model->city == $city->name)>{{ $city->name }}</option>
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        $('#country_id').on('change', function() {
            var country_id = $(this).find(':selected').data('id');
            $('#state_id').html('<option value="">Select State</option>');
            $('#city_id').html('<option value="">Select City</option>');
            if (country_id) {
                $.ajax({
                    url: "{{ route('ajax.states') }}",
                    type: "GET",
                    data: { country_id: country_id },
                    success: function(data) {
                        $.each(data, function(key, value) {
                            $('#state_id').append('<option value="' + value.name + '" data-id="' + value.id + '">' + value.name + '</option>');
                        });
                    }
                });
            }
        });

        $('#state_id').on('change', function() {
            var state_id = $(this).find(':selected').data('id');
            $('#city_id').html('<option value="">Select City</option>');
            if (state_id) {
                $.ajax({
                    url: "{{ route('ajax.cities') }}",
                    type: "GET",
                    data: { state_id: state_id },
                    success: function(data) {
                        $.each(data, function(key, value) {
                            $('#city_id').append('<option value="' + value.name + '">' + value.name + '</option>');
                        });
                    }
                });
            }
        });
    });
</script>