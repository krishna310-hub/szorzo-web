<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use HasFactory;
	use SoftDeletes;

    protected $table = 'roles';

	protected $fillable = [
		'name',
		'access_level',
		'status',
		'its_default',
	];

	protected $casts = [
		'status' => 'boolean',
		'its_default' => 'boolean',
	];

	public function permissions()
	{
		return $this->belongsToMany(Permission::class, 'permission_roles')
					->withPivot('id')
					->withTimestamps();
	}
}
