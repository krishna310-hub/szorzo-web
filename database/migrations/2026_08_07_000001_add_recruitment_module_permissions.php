<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $modules = [
            'employee' => ['Read', 'Create', 'Edit', 'Delete'],
            'interview_mode' => ['Read', 'Create', 'Edit', 'Delete'],
            'reports' => ['Read', 'Export'],
            'revenue' => ['Read', 'Create', 'Edit', 'Download'],
        ];

        foreach ($modules as $page => $abilities) {
            foreach ($abilities as $name) {
                DB::table('permissions')->updateOrInsert(
                    ['name' => $name, 'page' => $page],
                    ['updated_at' => $now, 'created_at' => $now]
                );
            }
        }
    }

    public function down(): void
    {
        $permissionIds = DB::table('permissions')
            ->where('page', 'revenue')
            ->whereIn('name', ['Read', 'Create', 'Edit', 'Download'])
            ->pluck('id');
        DB::table('permission_roles')->whereIn('permission_id', $permissionIds)->delete();
        DB::table('permissions')->whereIn('id', $permissionIds)->delete();
    }
};
