<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvesterTeamManagement extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'invester_team_managements';

    protected $fillable = [
        'invester_id',
        'team_name',
        'managed_employee_count',
        'operational_contacts',
        'governance_role',
        'notes',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function invester()
    {
        return $this->belongsTo(Invester::class, 'invester_id');
    }
}

