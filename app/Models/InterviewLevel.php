<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class InterviewLevel extends Model
{
    use SoftDeletes;

    public const CANDIDATE_NOT_INTERESTED_ID = 5;
    public const CANDIDATE_NOT_RESPONDING_ID = 36;
    public const CANDIDATE_POSITION_CLOSED_ID = 37;
    public const CANDIDATE_NOT_POSITION_ID = 6;

    protected $table = 'level_of_interviews';

    protected $fillable = [
        'level',
        'sort_order',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function candidates(): HasMany
    {
        return $this->hasMany(Candidate::class, 'level_of_interview_id');
    }
}
