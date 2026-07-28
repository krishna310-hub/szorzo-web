<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Revenue extends Model
{
    protected $fillable = [
        'candidate_id', 'client_id', 'invoice_number', 'invoice_date', 'universe_number',
        'client_name', 'client_address', 'client_gst_number', 'offered_ctc',
        'billing_percentage', 'service_amount', 'gst_percentage', 'gst_amount',
        'total_amount', 'notes',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'offered_ctc' => 'decimal:2',
        'billing_percentage' => 'decimal:2',
        'service_amount' => 'decimal:2',
        'gst_percentage' => 'decimal:2',
        'gst_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
