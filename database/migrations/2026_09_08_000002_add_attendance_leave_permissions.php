<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $permissions = [
        ['name' => 'Read', 'page' => 'attendance'],
        ['name' => 'Create', 'page' => 'attendance'],
        ['name' => 'Edit', 'page' => 'attendance'],
        ['name' => 'Export', 'page' => 'attendance'],
        ['name' => 'Read', 'page' => 'leave'],
        ['name' => 'Create', 'page' => 'leave'],
        ['name' => 'Approve', 'page' => 'leave'],
    ];

    public function up(): void
    {
        foreach ($this->permissions as $permission) {
            DB::table('permissions')->updateOrInsert($permission, ['updated_at' => now(), 'created_at' => now()]);
        }
    }

    public function down(): void
    {
        foreach ($this->permissions as $permission) {
            $id = DB::table('permissions')->where($permission)->value('id');
            if ($id) {
                DB::table('permission_roles')->where('permission_id', $id)->delete();
                DB::table('permissions')->where('id', $id)->delete();
            }
        }
    }
};
