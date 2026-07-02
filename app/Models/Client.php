<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'client',
        'billing_id',
        'location_id',
        'poc_name',
        'signed_date',
        'renewal_date',
        'division_id',
        'contact_number',
        'email',
        'mobile_number',
        'status',
    ];

    protected $casts = [
        'signed_date' => 'date',
        'renewal_date' => 'date',
        'status' => 'boolean',
    ];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    public function clientJobRoles()
    {
        return $this->hasMany(ClientJobRole::class);
    }

    public function billing()
    {
        return $this->belongsTo(Billing::class);
    }
}
