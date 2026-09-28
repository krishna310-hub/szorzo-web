<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadAssignmentHistory extends Model
{
    protected $fillable = [
        'lead_generation_id', 'assigned_by', 'previous_assigned_to', 'assigned_to', 'reason', 'assigned_at',
    ];

    protected $casts = ['assigned_at' => 'datetime'];

    public function lead()
    {
        return $this->belongsTo(LeadGeneration::class, 'lead_generation_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function previousAssignee()
    {
        return $this->belongsTo(User::class, 'previous_assigned_to');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
