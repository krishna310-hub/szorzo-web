<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeOnboardingLink extends Model
{
    protected $fillable = ['token', 'created_by_user_id', 'employee_id', 'used_at'];

    protected $casts = ['used_at' => 'datetime'];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
