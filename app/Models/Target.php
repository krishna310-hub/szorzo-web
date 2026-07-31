<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Target extends Model { use SoftDeletes; protected $fillable = ['target_name', 'monthly_target', 'status']; protected $casts = ['status' => 'boolean', 'monthly_target' => 'integer']; }
