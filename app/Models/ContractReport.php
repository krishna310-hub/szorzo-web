<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractReport extends Model
{
    protected $fillable = [
        'candidate_id',
        'salary_month',
        'monthly_take_home',
        'is_hourly',
        'hourly_salary',
        'present_days',
        'absent_days',
        'worked_hours',
        'payable_salary',
    ];

    protected $casts = [
        'salary_month' => 'date',
        'monthly_take_home' => 'decimal:2',
        'is_hourly' => 'boolean',
        'hourly_salary' => 'decimal:2',
        'worked_hours' => 'decimal:2',
        'payable_salary' => 'decimal:2',
    ];

    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }
}
