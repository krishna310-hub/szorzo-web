@extends('backend.layouts.master')
@section('title', 'Sales Follow-ups')
@section('content')
<div class="main-content"><div class="page-content"><div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div><h4 class="mb-1">Sales Follow-ups</h4><p class="text-muted mb-0">Open a follow-up to see the complete lead history.</p></div>
        <a href="{{ route('admin.lead-generations.index') }}" class="btn btn-outline-secondary">All Leads</a>
    </div>
    <div class="card"><div class="card-body">
        <div class="btn-group mb-3" role="group">
            @foreach(['today' => 'Today', 'overdue' => 'Overdue', 'upcoming' => 'Upcoming'] as $key => $label)
                <a href="{{ route('admin.sales-follow-ups.index', ['filter' => $key]) }}" class="btn {{ $filter === $key ? 'btn-primary' : 'btn-outline-primary' }}">{{ $label }}</a>
            @endforeach
        </div>
        <div class="table-responsive"><table class="table table-bordered align-middle">
            <thead><tr><th>Due</th><th>Lead</th><th>Next action</th><th>Type</th><th>Responsible</th><th>Lead owner</th><th>Notes</th><th>Update</th></tr></thead>
            <tbody>
            @forelse($tasks as $task)
                <tr>
                    <td class="text-nowrap">{{ $task->next_follow_up_at->format('d M Y, h:i A') }}</td>
                    <td><a href="{{ route('admin.lead-generations.edit', $task->lead_generation_id) }}">{{ $task->lead?->account_name ?? 'Deleted lead' }}</a></td>
                    <td>{{ $task->next_action ?? '—' }}</td>
                    <td>{{ \App\Models\LeadActivity::TYPES[$task->next_follow_up_type] ?? ucfirst(str_replace('_', ' ', $task->next_follow_up_type ?? '')) }}</td>
                    <td>{{ $task->responsibleUser?->name ?? 'Unassigned' }}</td>
                    <td>{{ $task->lead?->assignee?->name ?? 'Unassigned' }}</td>
                    <td style="min-width:220px">{{ $task->notes }}</td>
                    <td style="min-width:270px">
                        <form method="POST" action="{{ route('admin.sales-follow-ups.update', $task->id) }}" class="follow-up-update-form">
                            @csrf
                            <div class="input-group input-group-sm">
                                <select class="form-select follow-up-action" name="action" required>
                                    <option value="completed">Completed</option><option value="missed">Missed</option><option value="rescheduled">Reschedule</option>
                                </select>
                                <button class="btn btn-primary" type="submit">Save</button>
                            </div>
                            <div class="reschedule-fields mt-2 d-none">
                                <input class="form-control form-control-sm mb-1" type="datetime-local" name="rescheduled_at" min="{{ now()->addMinute()->format('Y-m-d\TH:i') }}">
                                <select class="form-select form-select-sm mb-1" name="rescheduled_type">
                                    @foreach(['outbound_call','inbound_call','whatsapp','email','teams_meeting','client_visit','demo'] as $type)<option value="{{ $type }}">{{ \App\Models\LeadActivity::TYPES[$type] }}</option>@endforeach
                                </select>
                                <input class="form-control form-control-sm" name="reschedule_reason" placeholder="Reason for rescheduling">
                            </div>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted py-4">No {{ $filter }} follow-ups.</td></tr>
            @endforelse
            </tbody>
        </table></div>
        {{ $tasks->links() }}
    </div></div>
</div></div></div>
<script>
document.querySelectorAll('.follow-up-update-form').forEach(form => {
    const action = form.querySelector('.follow-up-action');
    const fields = form.querySelector('.reschedule-fields');
    const date = form.querySelector('[name="rescheduled_at"]');
    const reason = form.querySelector('[name="reschedule_reason"]');
    const update = () => {
        const visible = action.value === 'rescheduled';
        fields.classList.toggle('d-none', !visible);
        date.required = visible;
        reason.required = visible;
    };
    action.addEventListener('change', update);
    update();
});
</script>
@endsection
