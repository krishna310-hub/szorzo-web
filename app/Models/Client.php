<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'client_code',
        'name',
        'logo',
        'contact_person',
        'email',
        'mobile_no',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    protected static function booted()
    {
        static::created(function ($client) {
            if (!$client->client_code) {
                $client->update([
                    'client_code' => '#CL' . $client->id,
                ]);
            }
        });
    }

    public function clientJobRoles()
    {
        return $this->hasMany(ClientJobRole::class);
    }
}
