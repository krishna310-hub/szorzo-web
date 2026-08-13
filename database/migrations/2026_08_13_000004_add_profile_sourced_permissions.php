<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        foreach (['Read', 'Create', 'Edit', 'Delete'] as $ability) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $ability, 'page' => 'profile_sourced'],
                ['created_at' => $now, 'updated_at' => $now]
            );

            $permissionId = DB::table('permissions')
                ->where(['name' => $ability, 'page' => 'profile_sourced'])->value('id');
            $candidatePermissionId = DB::table('permissions')
                ->where(['name' => $ability, 'page' => 'candidate'])->value('id');

            if ($candidatePermissionId) {
                DB::table('permission_roles')->where('permission_id', $candidatePermissionId)
                    ->pluck('role_id')->each(fn ($roleId) => DB::table('permission_roles')->updateOrInsert([
                        'permission_id' => $permissionId,
                        'role_id' => $roleId,
                    ], ['created_at' => $now, 'updated_at' => $now]));
            }
        }
    }

    public function down(): void
    {
        $ids = DB::table('permissions')->where('page', 'profile_sourced')->pluck('id');
        DB::table('permission_roles')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('id', $ids)->delete();
    }
};
