<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactEnquiry extends Model
{
    protected $fillable = [
        'firstname', 'lastname', 'email', 'company',
        'relationship', 'phone', 'info', 'status'
    ];
}
