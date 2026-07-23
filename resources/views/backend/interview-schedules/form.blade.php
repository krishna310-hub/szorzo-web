@php
    $schedule = $interviewSchedule ?? null;
    $selectedCandidateId = old('candidate_id', $schedule->candidate_id ?? ($selectedCandidate->id ?? ''));
    $selectedClientId = old('client_id', $schedule->client_id ?? ($selectedCandidate->client_id ?? ''));
    $selectedJobRoleId = old('job_role_id', $schedule->job_role_id ?? ($selectedCandidate->job_role_id ?? ''));
    $selectedLevelId = old('level_of_interview_id', $schedule->level_of_interview_id ?? ($selectedCandidate->level_of_interview_id ?? ''));
    $selectedStatus = old('status', $schedule->status ?? 'scheduled');
@endphp

<div class="row gy-4">
    <div class="col-md-4">
        <label for="candidate_id" class="form-label">Candidate <span class="text-danger">*</span></label>
        <select class="form-select" id="candidate_id" name="candidate_id">
            <option value="">Select candidate</option>
            @foreach ($candidates as $candidate)
                <option value="{{ $candidate->id }}"
                    data-client-id="{{ $candidate->client_id }}"
                    data-job-role-id="{{ $candidate->job_role_id }}"
                    data-level-id="{{ $candidate->level_of_interview_id }}"
                    {{ (string) $selectedCandidateId === (string) $candidate->id ? 'selected' : '' }}>
                    {{ $candidate->candidate_name }}{{ $candidate->mobile_no ? ' - '.$candidate->mobile_no : '' }}
                </option>
            @endforeach
        </select>
        @error('candidate_id')<span class="text-danger small">{{ $message }}</span>@enderror
    </div>

    <div class="col-md-4">
        <label for="client_id" class="form-label">Client</label>
        <select class="form-select" id="client_id" name="client_id">
            <option value="">Select client</option>
            @foreach ($clients as $client)
                <option value="{{ $client->id }}" {{ (string) $selectedClientId === (string) $client->id ? 'selected' : '' }}>{{ $client->client }}</option>
            @endforeach
        </select>
        @error('client_id')<span class="text-danger small">{{ $message }}</span>@enderror
    </div>
    <div class="col-md-4">
        <label for="job_role_id" class="form-label">Job Role</label>
        <select class="form-select" id="job_role_id" name="job_role_id">
            <option value="">Select job role</option>
            @foreach ($jobRoles as $jobRole)
                <option value="{{ $jobRole->id }}" {{ (string) $selectedJobRoleId === (string) $jobRole->id ? 'selected' : '' }}>{{ $jobRole->job_role }}</option>
            @endforeach
        </select>
        @error('job_role_id')<span class="text-danger small">{{ $message }}</span>@enderror
    </div>
    <div class="col-md-4">
        <label for="schedule_date" class="form-label">Schedule Date <span class="text-danger">*</span></label>
        <input type="datetime-local" class="form-control" id="schedule_date" name="schedule_date" value="{{ old('schedule_date', $schedule?->schedule_date?->format('Y-m-d\TH:i')) }}">
        @error('schedule_date')<span class="text-danger small">{{ $message }}</span>@enderror
    </div>
    <div class="col-md-4">
        <label for="interview_mode_id" class="form-label">Interview Mode <span class="text-danger">*</span></label>
        <select class="form-select" id="interview_mode_id" name="interview_mode_id">
            <option value="">Select interview mode</option>
            @foreach ($interviewMode as $mode)
                <option value="{{ $mode->id }}"{{ old('interview_mode_id', $selectedModeId ?? '') == $mode->id ? 'selected' : '' }}>{{ $mode->interview_mode }}</option>
            @endforeach
        </select>
        @error('interview_mode_id')<span class="text-danger small">{{ $message }}</span>@enderror
    </div>

    @php
        $sourcingLevels = [
            'L1 Scheduled',
            'L1 Select',
            'L1 Reject',
            'L1 Re-Schedule',
            'L2 Scheduled',
            'L2 Select',
            'L2 Reject',
            'L2 Re-Schedule',
            'L3 Scheduled',
            'L3 Select',
            'L3 Reject',
            'L3 Re-Schedule',
            'L4 Scheduled',
            'L4 Select',
            'L4 Reject',
            'L4 Re-Schedule',
            'HR Discussion',
            'HR Select',
            'HR Reject',
            'Offer Released',
            'Offer Accepted',
            'Offer Declined',
            'Onboarded with Client',
            'Joiner Declined',
            'Position Hold',
            'Candidate Not Interested',
            'Candidate Not Responding',
        ];
    @endphp
    <div class="col-md-4">
        <label for="level_of_interview_id" class="form-label">Level Of Interview <span class="text-danger">*</span></label>
        <select class="form-select" id="level_of_interview_id" name="level_of_interview_id">
            <option value="">Select level</option>
            @foreach ($interviewLevels as $level)
                @if(in_array($level->level, $sourcingLevels))
                    <option value="{{ $level->id }}" data-level="{{ $level->level }}"
                        {{ (string) old('level_of_interview_id', $selectedLevelId) === (string) $level->id ? 'selected' : '' }}>
                        {{ $level->level }}
                    </option>
                @endif
            @endforeach
        </select>
        @error('level_of_interview_id')<span class="text-danger small">{{ $message }}</span>@enderror
    </div>
    <div class="col-md-4" id="onboardingDateDiv" style="display: none;">
        <label for="onboarding_date" class="form-label">Onboarding Date <span class="text-danger">*</span></label>
        <input type="date" class="form-control" id="onboarding_date" name="onboarding_date" value="{{ old('onboarding_date', $onboarding_candidate->onboarding_date ?? '') }}">
        @error('onboarding_date')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-4">
        <label for="statuss" class="form-label">Status <span class="text-danger">*</span></label>
        <select class="form-select" id="statuss" name="status">
            <option value="">Select Status</option>
            @foreach ($statuses as $value => $label)
                <option value="{{ $value }}" {{ $selectedStatus === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        @error('status')<span class="text-danger small">{{ $message }}</span>@enderror
    </div>
    <div class="col-md-12">
        <label for="notes" class="form-label">Notes</label>
        <textarea class="form-control" id="notes" name="notes" rows="4">{{ old('notes', $schedule->notes ?? '') }}</textarea>
        @error('notes')<span class="text-danger small">{{ $message }}</span>@enderror
    </div>
</div>

<div class="d-flex gap-3 mt-5 justify-content-center">
    <button type="reset" class="btn btn-danger">Clear</button>
    <button type="submit" class="btn btn-success">Submit</button>
</div>
