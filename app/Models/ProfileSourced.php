<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProfileSourced extends Model
{
    use SoftDeletes;

    protected $table = 'profile_sourced';

    protected $fillable = [
        'recruiter_id', 'created_by_user_id', 'candidate_name', 'cv_path', 'mobile_number', 'email',
    ];

    public function recruiter()
    {
        return $this->belongsTo(Recruiter::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $query->whereIn('recruiter_id', Recruiter::visibleTo($user)->select('id'));
    }
}
