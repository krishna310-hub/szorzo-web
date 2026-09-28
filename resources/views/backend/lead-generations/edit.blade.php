@extends('backend.layouts.master')
@section('title', 'Edit Lead Generations')
@section('content')
<div class="main-content"><div class="page-content"><div class="container-fluid"><div class="row"><div class="col-lg-12"><div class="card">
    <div class="card-header"><h5 class="card-title mb-0">Edit Lead Generations</h5></div>
    <div class="card-body">
        <form action="{{ route('admin.lead-generations.update', $model->id) }}" method="POST">
            @csrf
            @method('PUT')
            @include('backend.lead-generations.form')
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('admin.lead-generations.index') }}" class="btn btn-secondary ms-2">Back</a>
            </div>
        </form>
    </div>
</div></div></div></div>
<div class="row mt-4">
    <div class="col-lg-7">
        <div class="card"><div class="card-header"><h5 class="mb-0">Add Interaction</h5></div><div class="card-body">
            <form action="{{ route('admin.lead-generations.activities.store', $model->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-md-4 mb-3"><label>Activity type *</label><select class="form-select" name="activity_type" required>@foreach($activityTypes as $type => $label)<option value="{{ $type }}">{{ $label }}</option>@endforeach</select></div>
                    <div class="col-md-4 mb-3"><label>Date and time *</label><input class="form-control" type="datetime-local" name="occurred_at" value="{{ now()->format('Y-m-d\TH:i') }}" required></div>
                    <div class="col-md-4 mb-3"><label>Outcome</label><input class="form-control" name="outcome" maxlength="255"></div>
                    <div class="col-md-4 mb-3"><label>Call duration (minutes)</label><input class="form-control" type="number" name="duration_minutes" min="0"></div>
                    <div class="col-md-4 mb-3"><label>Interest level</label><select class="form-select" name="interest_level"><option value="">Not recorded</option>@foreach(['low','medium','high'] as $level)<option value="{{ $level }}">{{ ucfirst($level) }}</option>@endforeach</select></div>
                    <div class="col-md-4 mb-3"><label>Attachment</label><input class="form-control" type="file" name="attachment" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx"></div>
                    <div class="col-12 mb-3"><label>Discussion notes *</label><textarea class="form-control" name="notes" rows="3" required maxlength="10000"></textarea></div>
                    <div class="col-md-6 mb-3"><label>Visit location</label><input class="form-control" name="location" maxlength="255"></div>
                    <div class="col-md-6 mb-3"><label>Teams meeting link</label><input class="form-control" type="url" name="meeting_link"></div>
                    <div class="col-12 mb-3"><label>Attendees</label><textarea class="form-control" name="attendees" rows="2"></textarea></div>
                    <div class="col-md-6 mb-3"><label>Update lead stage</label><select class="form-select" name="pipeline_stage"><option value="">Keep current stage (automatic contact updates apply)</option>@foreach(\App\Models\LeadGeneration::STAGES as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></div>
                    <div class="col-md-6 mb-3"><label>Agreement status</label><select class="form-select" name="agreement_status"><option value="">Keep current status</option>@foreach(['draft'=>'Draft','sent'=>'Sent','under_review'=>'Under review','signed'=>'Signed','rejected'=>'Rejected'] as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></div>
                    <div class="col-md-6 mb-3"><label>Complete existing follow-up</label><select class="form-select" name="completed_follow_up_id"><option value="">No existing follow-up</option>@foreach($pendingFollowUps as $task)<option value="{{ $task->id }}">{{ $task->next_follow_up_at->format('d M Y H:i') }} — {{ $task->next_action }}</option>@endforeach</select></div>
                    <div class="col-md-6 mb-3"><label>Next follow-up</label><input class="form-control" type="datetime-local" name="next_follow_up_at" min="{{ now()->addMinute()->format('Y-m-d\TH:i') }}"></div>
                    <div class="col-md-4 mb-3"><label>Follow-up type</label><select class="form-select" name="next_follow_up_type"><option value="">Select type</option>@foreach(['outbound_call','inbound_call','whatsapp','email','teams_meeting','client_visit','demo'] as $type)<option value="{{ $type }}">{{ \App\Models\LeadActivity::TYPES[$type] }}</option>@endforeach</select></div>
                    <div class="col-md-4 mb-3"><label>Responsible employee</label><select class="form-select" name="responsible_user_id"><option value="">Lead owner</option>@foreach($salesUsers as $salesUser)<option value="{{ $salesUser->id }}" @selected($model->assigned_to == $salesUser->id)>{{ $salesUser->name }}</option>@endforeach</select></div>
                    <div class="col-md-4 mb-3"><label>Reminder at</label><input class="form-control" type="datetime-local" name="reminder_at"></div>
                    <div class="col-12 mb-3"><label>Next action</label><input class="form-control" name="next_action" maxlength="255"></div>
                </div>
                <button class="btn btn-success" type="submit">Save Interaction</button>
            </form>
        </div></div>
    </div>
    <div class="col-lg-5">
        <div class="card"><div class="card-header"><h5 class="mb-0">Lead Timeline</h5></div><div class="card-body" style="max-height:900px;overflow:auto">
            @forelse($model->activities as $activity)
                <div class="border-start border-3 border-primary ps-3 pb-3 mb-3">
                    <div class="d-flex justify-content-between"><strong>{{ \App\Models\LeadActivity::TYPES[$activity->activity_type] ?? ucfirst(str_replace('_', ' ', $activity->activity_type)) }}</strong><small class="text-muted">{{ $activity->occurred_at->format('d M Y, h:i A') }}</small></div>
                    <div class="small text-muted">{{ $activity->performer?->name ?? 'System' }}@if($activity->outcome) · {{ $activity->outcome }}@endif</div>
                    @if($activity->notes)<p class="mb-1 mt-2">{{ $activity->notes }}</p>@endif
                    @if($activity->duration_seconds)<small>Duration: {{ intdiv($activity->duration_seconds, 60) }} min</small>@endif
                    @if($activity->interest_level)<small class="ms-2">Interest: {{ ucfirst($activity->interest_level) }}</small>@endif
                    @if($activity->location)<div class="small">Location: {{ $activity->location }}</div>@endif
                    @if($activity->meeting_link)<div><a href="{{ $activity->meeting_link }}" target="_blank" rel="noopener">Meeting link</a></div>@endif
                    @if($activity->attendees)<div class="small">Attendees: {{ $activity->attendees }}</div>@endif
                    @if($activity->next_follow_up_at)<div class="small mt-1">Follow-up: {{ $activity->next_follow_up_at->format('d M Y, h:i A') }} · {{ $activity->next_action }} · {{ ucfirst($activity->follow_up_status) }}</div>@endif
                    @if($activity->attachment_path)<a class="btn btn-sm btn-outline-secondary mt-2" href="{{ route('admin.lead-activities.attachment', $activity->id) }}">Download attachment</a>@endif
                </div>
            @empty
                <p class="text-muted">No interactions recorded yet.</p>
            @endforelse
            @if($model->assignmentHistory->isNotEmpty())
                <hr><h6>Assignment history</h6>
                @foreach($model->assignmentHistory as $assignment)
                    <div class="small mb-2">{{ $assignment->assigned_at->format('d M Y, h:i A') }}: {{ $assignment->previousAssignee?->name ?? 'Unassigned' }} → {{ $assignment->assignee?->name ?? 'Unassigned' }} by {{ $assignment->assignedBy?->name ?? 'System' }}@if($assignment->reason) · {{ $assignment->reason }}@endif</div>
                @endforeach
            @endif
        </div></div>
    </div>
</div>
</div></div></div>
<script>
document.querySelectorAll('form[action*="/activities"]').forEach(form => {
    const due=form.querySelector('[name="next_follow_up_at"]'), type=form.querySelector('[name="next_follow_up_type"]'), action=form.querySelector('[name="next_action"]');
    form.addEventListener('submit', () => { if (due.value) { type.required=true; action.required=true; } });
});
</script>
@endsection