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
        <label for="need" class="form-label">Remarks</label>
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
        <div id="cv-parse-status" class="small text-muted mt-1">Upload a CV to automatically extract Name, Email, and Mobile number.</div>
        @isset($profileSourced)<a href="{{ asset($profileSourced->cv_path) }}" target="_blank" class="small">View current CV</a>@endisset
        @error('cv')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>
</div>
<div class="text-end mt-3">
    <a href="{{ route('admin.profile-sourced.index') }}" class="btn btn-light me-2">Cancel</a>
    <button class="btn btn-success">Save</button>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const cvInput = document.getElementById('cv');
    const statusDiv = document.getElementById('cv-parse-status');
    const nameInput = document.getElementById('candidate_name');
    const emailInput = document.getElementById('email');
    const mobileInput = document.getElementById('mobile_number');

    if (!cvInput) return;

    cvInput.addEventListener('change', function () {
        if (!this.files || !this.files[0]) return;

        const file = this.files[0];
        const validExtensions = ['pdf', 'doc', 'docx'];
        const extension = file.name.split('.').pop().toLowerCase();

        if (!validExtensions.includes(extension)) {
            return;
        }

        if (statusDiv) {
            statusDiv.innerHTML = '<span class="text-primary"><i class="bx bx-loader-alt bx-spin align-middle me-1"></i> Extracting details from CV...</span>';
        }

        const formData = new FormData();
        formData.append('cv', file);

        fetch("{{ route('admin.profile-sourced.parse-cv') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Server returned ' + response.status);
            }
            return response.json();
        })
        .then(res => {
            if (res.success && res.data) {
                let filledFields = [];
                if (res.data.candidate_name) {
                    nameInput.value = res.data.candidate_name;
                    nameInput.classList.add('is-valid');
                    filledFields.push('Name');
                }
                if (res.data.email) {
                    emailInput.value = res.data.email;
                    emailInput.classList.add('is-valid');
                    filledFields.push('Email');
                }
                if (res.data.mobile_number) {
                    mobileInput.value = res.data.mobile_number;
                    mobileInput.classList.add('is-valid');
                    filledFields.push('Mobile');
                }

                if (filledFields.length > 0) {
                    if (statusDiv) {
                        statusDiv.innerHTML = '<span class="text-success fw-medium"><i class="bx bx-check-circle align-middle me-1"></i> Auto-filled ' + filledFields.join(', ') + ' from CV!</span>';
                    }
                } else {
                    if (statusDiv) {
                        statusDiv.innerHTML = '<span class="text-muted"><i class="bx bx-info-circle align-middle me-1"></i> Text read from CV. Please verify details manually.</span>';
                    }
                }
            } else {
                if (statusDiv) {
                    statusDiv.innerHTML = '<span class="text-muted">Upload a CV to automatically extract Name, Email, and Mobile number.</span>';
                }
            }
        })
        .catch(err => {
            console.error('CV parse error:', err);
            if (statusDiv) {
                statusDiv.innerHTML = '<span class="text-muted">Upload a CV to automatically extract Name, Email, and Mobile number.</span>';
            }
        });
    });
});
</script>

