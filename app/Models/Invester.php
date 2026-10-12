<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invester extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'investers';

    protected $fillable = [
        'invester_id',
        'name',
        'profile_picture',
        'investor_type',
        'contact',
        'primary_email',
        'alternate_email',
        'address',
        'account_manager_id',
        'internal_notes',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * Account Manager / Internal Point of Contact (Employee/User).
     */
    public function accountManager()
    {
        return $this->belongsTo(User::class, 'account_manager_id');
    }

    /**
     * Team & Management Scope records.
     */
    public function teamManagements()
    {
        return $this->hasMany(InvesterTeamManagement::class, 'invester_id');
    }

    /**
     * Financial & Investment Details records.
     */
    public function financialDetails()
    {
        return $this->hasMany(InvesterFinancialDetail::class, 'invester_id');
    }

    /**
     * Legal, Compliance & Governance records.
     */
    public function legalCompliances()
    {
        return $this->hasMany(InvesterLegalCompliance::class, 'invester_id');
    }

    /**
     * Generate sequential Invester ID (e.g., SZ INV 0001).
     */
    public static function nextInvesterId(): string
    {
        $prefix = 'SZ INV ';
        try {
            $last = static::withTrashed()
                ->where('invester_id', 'LIKE', $prefix . '%')
                ->orderBy('id', 'desc')
                ->first();

            $nextNumber = 1;

            if ($last && preg_match('/' . preg_quote($prefix, '/') . '(\d+)/', $last->invester_id, $matches)) {
                $nextNumber = (int) $matches[1] + 1;
            }

            return $prefix . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
        } catch (\Throwable $e) {
            return $prefix . '0001';
        }
    }
}
