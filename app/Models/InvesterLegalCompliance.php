<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvesterLegalCompliance extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'invester_legal_compliances';

    protected $fillable = [
        'invester_id',
        'tax_id_number',
        'kyc_aml_status',
        'nda_signed_doc',
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

