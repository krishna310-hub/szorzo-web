<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\LeadActivity;
use App\Models\LeadGeneration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class LeadActivityController extends Controller
{
    public function store(Request $request, $leadId)
    {
        $lead = $this->visibleLeads()->findOrFail($leadId);
        $data = $request->validate([
            'activity_type' => ['required', Rule::in(array_keys(LeadActivity::TYPES))],
            'occurred_at' => ['required', 'date'],
            'outcome' => ['nullable', 'string', 'max:255'],
            'notes' => ['required', 'string', 'max:10000'],
            'duration_minutes' => ['nullable', 'integer', 'min:0', 'max:1440'],
            'interest_level' => ['nullable', 'in:low,medium,high'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx', 'max:10240'],
            'next_follow_up_at' => ['nullable', 'date', 'after:occurred_at'],
            'next_follow_up_type' => ['nullable', 'required_with:next_follow_up_at', 'in:outbound_call,inbound_call,whatsapp,email,teams_meeting,client_visit,demo'],
            'agreement_status' => ['nullable', 'in:draft,sent,under_review,signed,rejected'],
            'responsible_user_id' => ['nullable', 'exists:users,id'],
            'reminder_at' => ['nullable', 'date', 'before_or_equal:next_follow_up_at'],
            'next_action' => ['nullable', 'required_with:next_follow_up_at', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'meeting_link' => ['nullable', 'url', 'max:2048'],
            'attendees' => ['nullable', 'string', 'max:3000'],
            'completed_follow_up_id' => ['nullable', 'integer'],
            'pipeline_stage' => ['nullable', Rule::in(array_keys(LeadGeneration::STAGES))],
        ]);

        $responsibleId = $this->resolveResponsibleUser($request, $data['responsible_user_id'] ?? null, $lead);
        $oldStage = $lead->pipeline_stage;
        $nextStage = $data['pipeline_stage'] ?? null;
        if (!$nextStage && $data['activity_type'] === 'proposal_sent') $nextStage = 'proposal_sent';
        if (!$nextStage && in_array($data['activity_type'], ['outbound_call', 'inbound_call', 'whatsapp', 'email', 'teams_meeting', 'client_visit', 'demo'], true)
            && in_array($lead->pipeline_stage, ['new', 'assigned', 'contact_attempted'], true)) {
            $nextStage = 'contacted';
        }

        DB::transaction(function () use ($request, $data, $lead, $responsibleId, $oldStage, $nextStage) {
            if (!empty($data['completed_follow_up_id'])) {
                $task = LeadActivity::where('lead_generation_id', $lead->id)
                    ->whereKey($data['completed_follow_up_id'])
                    ->whereIn('follow_up_status', ['pending', 'rescheduled'])
                    ->lockForUpdate()->firstOrFail();
                $task->update(['follow_up_status' => 'completed']);
                $lead->activities()->create([
                    'activity_type' => 'follow_up_completed', 'occurred_at' => now(),
                    'performed_by' => auth()->id(), 'outcome' => 'Follow-up completed',
                    'notes' => 'Follow-up task completed as part of this interaction.',
                ]);
            }

            $activity = $lead->activities()->create([
                'activity_type' => $data['activity_type'],
                'occurred_at' => $data['occurred_at'],
                'performed_by' => auth()->id(),
                'outcome' => $data['outcome'] ?? null,
                'notes' => $data['notes'],
                'duration_seconds' => isset($data['duration_minutes']) ? (int) $data['duration_minutes'] * 60 : null,
                'interest_level' => $data['interest_level'] ?? null,
                'attachment_path' => $request->file('attachment')?->store('lead-activity-attachments', 'public'),
                'next_follow_up_at' => $data['next_follow_up_at'] ?? null,
                'next_follow_up_type' => $data['next_follow_up_type'] ?? null,
                'responsible_user_id' => $responsibleId,
                'reminder_at' => $data['reminder_at'] ?? null,
                'next_action' => $data['next_action'] ?? null,
                'follow_up_status' => !empty($data['next_follow_up_at']) ? 'pending' : null,
                'location' => $data['location'] ?? null,
                'meeting_link' => $data['meeting_link'] ?? null,
                'attendees' => $data['attendees'] ?? null,
            ]);

            if (!empty($data['next_follow_up_at'])) {
                $lead->forceFill([
                    'next_follow_up_at' => $data['next_follow_up_at'],
                    'next_follow_up_type' => $data['next_follow_up_type'],
                    'next_follow_up_user_id' => $responsibleId,
                    'follow_up_reminder_at' => $data['reminder_at'] ?? null,
                    'next_action' => $data['next_action'],
                    'follow_up_status' => 'pending',
                    'follow_up_reason' => null,
                    'follow_up_notes' => $data['notes'],
                ])->save();
                if (in_array($lead->pipeline_stage, ['new', 'assigned', 'contact_attempted', 'contacted'], true) && !$nextStage) {
                    $nextStage = 'follow_up';
                }
            }

            if (isset($data['agreement_status'])) $lead->update(['agreement_status' => $data['agreement_status']]);

            if ($nextStage && $nextStage !== $oldStage) {
                $stageData = ['pipeline_stage' => $nextStage];
                if (in_array($nextStage, ['contacted', 'follow_up', 'qualified', 'proposal_sent', 'negotiation', 'agreement_signed', 'won'], true) && !$lead->first_contact_at) $stageData['first_contact_at'] = now();
                $lead->update($stageData);
                if ($nextStage === 'proposal_sent') $lead->update(['proposal_sent_at' => $lead->proposal_sent_at ?? now()]);
                if (in_array($nextStage, ['agreement_signed', 'won'], true)) {
                    $lead->update(['agreement_status' => 'signed', 'agreement_signed_at' => $lead->agreement_signed_at ?? now()]);
                }
                $lead->activities()->create([
                    'activity_type' => 'stage_changed', 'occurred_at' => now(),
                    'performed_by' => auth()->id(), 'outcome' => 'Stage changed',
                    'notes' => (LeadGeneration::STAGES[$oldStage] ?? $oldStage).' → '.(LeadGeneration::STAGES[$nextStage] ?? $nextStage),
                ]);
            }
        });

        return redirect()->route('admin.lead-generations.edit', $lead->id)->with('success', 'Activity saved to the lead timeline.');
    }

    public function followUps(Request $request)
    {
        $filter = $request->string('filter')->toString();
        if (!in_array($filter, ['today', 'overdue', 'upcoming'], true)) $filter = 'today';
        $query = LeadActivity::with(['lead.assignee', 'responsibleUser', 'performer'])
            ->whereNotNull('next_follow_up_at')
            ->whereIn('follow_up_status', ['pending', 'rescheduled'])
            ->whereHas('lead', function ($leadQuery) {
                $leadQuery->whereNull('deleted_at');
                if (auth()->user()->isSales()) $leadQuery->where('assigned_to', auth()->id());
            });

        if ($filter === 'overdue') $query->where('next_follow_up_at', '<', now());
        elseif ($filter === 'upcoming') $query->where('next_follow_up_at', '>', now()->endOfDay());
        else $query->whereBetween('next_follow_up_at', [now(), now()->endOfDay()]);

        return view('backend.lead-generations.follow-ups', [
            'tasks' => $query->orderBy('next_follow_up_at')->paginate(30)->withQueryString(),
            'filter' => $filter ?: 'today',
        ]);
    }

    public function updateFollowUp(Request $request, $activityId)
    {
        $activity = LeadActivity::with('lead')->findOrFail($activityId);
        $lead = $this->visibleLeads()->findOrFail($activity->lead_generation_id);
        abort_unless(in_array($activity->follow_up_status, ['pending', 'rescheduled'], true), 422, 'This follow-up is already closed.');

        $data = $request->validate([
            'action' => ['required', 'in:completed,missed,rescheduled'],
            'rescheduled_at' => ['nullable', 'required_if:action,rescheduled', 'date', 'after:now'],
            'rescheduled_type' => ['nullable', 'required_if:action,rescheduled', 'in:outbound_call,inbound_call,whatsapp,email,teams_meeting,client_visit,demo'],
            'reschedule_reason' => ['nullable', 'required_if:action,rescheduled', 'string', 'max:3000'],
        ]);

        DB::transaction(function () use ($activity, $lead, $data) {
            $activity = LeadActivity::whereKey($activity->id)->lockForUpdate()->firstOrFail();
            abort_unless(in_array($activity->follow_up_status, ['pending', 'rescheduled'], true), 422, 'This follow-up is already closed.');
            $activity->update([
                'follow_up_status' => $data['action'],
                'reschedule_reason' => $data['reschedule_reason'] ?? null,
            ]);
            $label = ucfirst($data['action']);
            $eventType = $data['action'] === 'missed' ? 'follow_up_missed' : 'follow_up_completed';
            $notes = 'Scheduled '.(LeadActivity::TYPES[$activity->next_follow_up_type] ?? $activity->next_follow_up_type).' follow-up '.$label.'.';
            if ($data['action'] === 'rescheduled') {
                $notes .= ' Reason: '.$data['reschedule_reason'];
                $lead->activities()->create([
                    'activity_type' => 'follow_up_rescheduled', 'occurred_at' => now(),
                    'performed_by' => auth()->id(), 'outcome' => 'Follow-up rescheduled',
                    'notes' => $notes,
                    'next_follow_up_at' => $data['rescheduled_at'],
                    'next_follow_up_type' => $data['rescheduled_type'],
                    'responsible_user_id' => $activity->responsible_user_id ?? $lead->assigned_to,
                    'next_action' => $activity->next_action,
                    'follow_up_status' => 'pending',
                ]);
                if ($lead->next_follow_up_at && $lead->next_follow_up_at->equalTo($activity->next_follow_up_at)) $lead->update([
                    'next_follow_up_at' => $data['rescheduled_at'],
                    'next_follow_up_type' => $data['rescheduled_type'],
                    'next_follow_up_user_id' => $activity->responsible_user_id ?? $lead->assigned_to,
                    'follow_up_status' => 'pending',
                    'follow_up_reason' => $data['reschedule_reason'],
                ]);
                $lead->activities()->whereKey($activity->id)->update(['follow_up_status' => 'rescheduled', 'reschedule_reason' => $data['reschedule_reason']]);
            } else {
                $lead->activities()->create([
                    'activity_type' => $eventType, 'occurred_at' => now(),
                    'performed_by' => auth()->id(), 'outcome' => 'Follow-up '.$label,
                    'notes' => $notes,
                ]);
                if ($lead->next_follow_up_at && $lead->next_follow_up_at->equalTo($activity->next_follow_up_at)) {
                    $lead->update(['follow_up_status' => $data['action']]);
                }
            }
        });

        return back()->with('success', 'Follow-up updated and recorded in the lead timeline.');
    }

    public function downloadAttachment($activityId)
    {
        $activity = LeadActivity::with('lead')->findOrFail($activityId);
        $this->visibleLeads()->findOrFail($activity->lead_generation_id);
        abort_unless($activity->attachment_path && Storage::disk('public')->exists($activity->attachment_path), 404);

        return Storage::disk('public')->download($activity->attachment_path);
    }

    private function resolveResponsibleUser(Request $request, ?int $requestedId, LeadGeneration $lead): ?int
    {
        if (auth()->user()->isSales()) return auth()->id();
        $id = $requestedId ?: $lead->assigned_to;
        if (!$id) return null;
        abort_unless(\App\Models\User::with('role')->where('is_active', 1)->find($id)?->isSales(), 422, 'Choose an active Sales role user for follow-up responsibility.');
        return (int) $id;
    }

    private function visibleLeads()
    {
        $query = LeadGeneration::query();
        if (auth()->check() && auth()->user()->isSales()) $query->where('assigned_to', auth()->id());
        return $query;
    }
}
