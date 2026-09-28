<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadActivity extends Model
{
    public const TYPES = [
        'outbound_call' => 'Outbound Call',
        'inbound_call' => 'Inbound Call',
        'whatsapp' => 'WhatsApp / Message',
        'email' => 'Email',
        'teams_meeting' => 'Teams Meeting',
        'client_visit' => 'Client Visit',
        'demo' => 'Demo / Presentation',
        'proposal_sent' => 'Proposal Sent',
        'agreement_discussion' => 'Agreement Discussion',
        'internal_note' => 'Internal Note',
        'stage_changed' => 'Stage Changed',
        'assignment' => 'Assignment',
        'lead_created' => 'Lead Created',
        'follow_up_completed' => 'Follow-up Completed',
        'follow_up_rescheduled' => 'Follow-up Rescheduled',
        'follow_up_missed' => 'Follow-up Missed',
    ];

    protected $fillable = [
        'lead_generation_id', 'activity_type', 'occurred_at', 'performed_by', 'outcome', 'notes',
        'duration_seconds', 'interest_level', 'attachment_path', 'next_follow_up_at',
        'next_follow_up_type', 'responsible_user_id', 'reminder_at', 'next_action',
        'follow_up_status', 'reschedule_reason', 'location', 'meeting_link', 'attendees',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'next_follow_up_at' => 'datetime',
        'reminder_at' => 'datetime',
    ];

    public function lead()
    {
        return $this->belongsTo(LeadGeneration::class, 'lead_generation_id');
    }

    public function performer()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function responsibleUser()
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }
}
