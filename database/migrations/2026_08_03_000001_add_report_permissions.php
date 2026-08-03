<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        foreach (['Read', 'Export'] as $name) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $name, 'page' => 'reports'],
                ['updated_at' => $now, 'created_at' => $now]
            );
        }

        $permissionIds = DB::table('permissions')
            ->where('page', 'reports')
            ->whereIn('name', ['Read', 'Export'])
            ->pluck('id');
        $roleIds = DB::table('roles')
            ->where(function ($query) {
                $query->whereIn('access_level', ['delivery-lead', 'delivery_lead', 'recruiter'])
                    ->orWhereIn('id', [2, 3]);
            })
            ->pluck('id');

        foreach ($roleIds as $roleId) {
            foreach ($permissionIds as $permissionId) {
                DB::table('permission_roles')->updateOrInsert(
                    ['role_id' => $roleId, 'permission_id' => $permissionId],
                    ['updated_at' => $now, 'created_at' => $now]
                );
            }
        }
    }

    public function down(): void
    {
        $permissionIds = DB::table('permissions')
            ->where('page', 'reports')
            ->pluck('id');

        DB::table('permission_roles')->whereIn('permission_id', $permissionIds)->delete();
        DB::table('permissions')->whereIn('id', $permissionIds)->delete();
    }
};
