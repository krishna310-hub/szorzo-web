<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Recruiter extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'recruiter_name',
        'location',
        'email',
        'mobile_number',
        'performance_rating',
        'delivery_lead_user_id',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function deliveryLead()
    {
        return $this->belongsTo(User::class, 'delivery_lead_user_id');
    }

    /**
     * Limit recruiter records to the team represented by the logged-in user.
     *
     * Recruiter DL users see their own email-linked recruiter record and every
     * recruiter mapped to their user account. Recruiters only see their own
     * email-linked record. Other authorized roles retain the organization view.
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        $accessLevel = str_replace('_', '-', strtolower((string) $user->role?->access_level));
        $email = mb_strtolower(trim((string) $user->email));

        if (in_array($accessLevel, ['delivery-lead', 'recruiter-dl'], true)) {
            return $query->where(function (Builder $teamQuery) use ($user, $email) {
                $teamQuery->where('delivery_lead_user_id', $user->id);

                if ($email !== '') {
                    $teamQuery->orWhereRaw('LOWER(email) = ?', [$email]);
                }
            });
        }

        if ($accessLevel === 'recruiter') {
            return $email === ''
                ? $query->whereRaw('1 = 0')
                : $query->whereRaw('LOWER(email) = ?', [$email]);
        }

        return $query;
    }
}
