<div class="row">
    <div class="col-md-6 mb-3">
        <label for="candidate_name" class="form-label">Candidate Name <span class="text-danger">*</span></label>
        <input id="candidate_name" name="candidate_name" class="form-control" required
            value="{{ old('candidate_name', $profileSourced->candidate_name ?? '') }}">
        @error('candidate_name')<span class="text-danger small">{{ $message }}</span>@enderror
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Recruiter Name</label>
        @if ($canChooseRecruiter)
            <select class="form-select" name="recruiter_id" required>
                <option value="">Choose recruiter or delivery lead</option>
                @foreach ($recruiters as $item)
                    <option value="{{ $item->id }}" @selected((string) old('recruiter_id', $profileSourced->recruiter_id ?? '') === (string) $item->id)>
                        {{ $item->recruiter_name }}{{ $item->deliveryLead ? ' — DL: '.$item->deliveryLead->name : '' }}
                    </option>
                @endforeach
            </select>
            <small class="text-muted">Delivery-lead mappings are shown beside recruiter names.</small>
        @else
            <input class="form-control" value="{{ $profileSourced->recruiter?->recruiter_name ?? ($recruiter ?? null)?->recruiter_name ?? '' }}" readonly>
            <small class="text-muted">Automatically populated from your login.</small>
        @endif
        @error('recruiter_id')<div class="text-danger small">{{ $message }}</div>@enderror
        @error('recruiter')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-3">
        <label for="job_role_id" class="form-label">Job Role <span class="text-danger">*</span></label>
        <select id="job_role_id" name="job_role_id" class="form-select" required>
            <option value="">Select job role</option>
            @foreach ($jobRoles as $jobRole)
                <option value="{{ $jobRole->id }}" @selected((string) old('job_role_id', $profileSourced->job_role_id ?? '') === (string) $jobRole->id)>{{ $jobRole->job_role }}</option>
            @endforeach
        </select>
        @error('job_role_id')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-3">
        <label for="need" class="form-label">Need</label>
        <input id="need" name="need" class="form-control" value="{{ old('need', $profileSourced->need ?? '') }}">
        @error('need')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-3">
        <label for="mobile_number" class="form-label">Mobile Number <span class="text-danger">*</span></label>
        <input id="mobile_number" name="mobile_number" class="form-control" required
            value="{{ old('mobile_number', $profileSourced->mobile_number ?? '') }}">
        @error('mobile_number')<span class="text-danger small">{{ $message }}</span>@enderror
    </div>
    <div class="col-md-6 mb-3">
        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
        <input type="email" id="email" name="email" class="form-control" required
            value="{{ old('email', $profileSourced->email ?? '') }}">
        @error('email')<span class="text-danger small">{{ $message }}</span>@enderror
    </div>
    <div class="col-md-6 mb-3">
        <label for="cv" class="form-label">CV @unless(isset($profileSourced))<span class="text-danger">*</span>@endunless</label>
        <input type="file" id="cv" name="cv" class="form-control" accept=".pdf,.doc,.docx" @unless(isset($profileSourced)) required @endunless>
        @isset($profileSourced)<a href="{{ asset($profileSourced->cv_path) }}" target="_blank" class="small">View current CV</a>@endisset
        @error('cv')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>
</div>
<div class="text-end mt-3">
    <a href="{{ route('admin.profile-sourced.index') }}" class="btn btn-light me-2">Cancel</a>
    <button class="btn btn-success">Save</button>
</div>
