<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientJobRole extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'client_id',
        'job_role_id',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function jobRole()
    {
        return $this->belongsTo(JobRole::class);
    }
}
