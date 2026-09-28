<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeadGeneration extends Model
{
    use SoftDeletes;

    public const STAGES = [
        'new' => 'New',
        'assigned' => 'Assigned',
        'contact_attempted' => 'Contact Attempted',
        'contacted' => 'Contacted',
        'follow_up' => 'Follow-up',
        'qualified' => 'Qualified',
        'proposal_sent' => 'Proposal Sent',
        'negotiation' => 'Negotiation',
        'agreement_signed' => 'Agreement Signed',
        'won' => 'Won',
        'lost' => 'Lost',
        'on_hold' => 'On Hold',
        'converted' => 'Client Profile Created',
    ];

    protected $fillable = [
        'account_id', 'account_source', 'account_name', 'industry', 'sub_industry', 'website_url',
        'country_of_origin', 'region', 'state', 'city', 'registered_address', 'pin_code',
        'ownership_type', 'registration_id', 'gstin', 'account_owner', 'relationship_manager',
        'assigned_to', 'pipeline_stage', 'opportunity_status', 'next_follow_up_at', 'follow_up_notes',
        'first_contact_at', 'client_profile_id', 'customer_since', 'account_created_date',
        'last_updated_date', 'status', 'contact_person', 'mobile', 'email', 'address',
        'interested_service', 'priority', 'created_by_user_id', 'next_follow_up_type',
        'follow_up_status', 'next_follow_up_user_id', 'follow_up_reminder_at', 'next_action',
        'follow_up_reason', 'proposal_sent_at', 'agreement_status', 'agreement_signed_at',
        'lost_reason', 'competitor', 'nurture_at',
    ];

    protected $casts = [
        'status' => 'boolean',
        'account_created_date' => 'date',
        'last_updated_date' => 'date',
        'next_follow_up_at' => 'datetime',
        'follow_up_reminder_at' => 'datetime',
        'first_contact_at' => 'datetime',
        'proposal_sent_at' => 'datetime',
        'agreement_signed_at' => 'datetime',
        'nurture_at' => 'datetime',
    ];

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function nextFollowUpOwner()
    {
        return $this->belongsTo(User::class, 'next_follow_up_user_id');
    }

    public function clientProfile()
    {
        return $this->belongsTo(ClientProfile::class);
    }

    public function activities()
    {
        return $this->hasMany(LeadActivity::class)->orderByDesc('occurred_at')->orderByDesc('id');
    }

    public function assignmentHistory()
    {
        return $this->hasMany(LeadAssignmentHistory::class)->with(['assignedBy', 'previousAssignee', 'assignee'])->orderByDesc('assigned_at');
    }
}
