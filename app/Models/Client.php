<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [
        'client_code',
        'name',
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
}
