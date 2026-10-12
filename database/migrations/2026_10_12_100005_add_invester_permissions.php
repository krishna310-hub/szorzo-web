<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $now = now();
        $modules = [
            'invester' => ['Read', 'Create', 'Edit', 'Delete'],
            'invester_team_management' => ['Read', 'Create', 'Edit', 'Delete'],
            'invester_financial_detail' => ['Read', 'Create', 'Edit', 'Delete'],
            'invester_legal_compliance' => ['Read', 'Create', 'Edit', 'Delete'],
        ];

        $insertedIds = [];

        foreach ($modules as $page => $abilities) {
            foreach ($abilities as $name) {
                $permission = DB::table('permissions')->where('name', $name)->where('page', $page)->first();
                if ($permission) {
                    $insertedIds[] = $permission->id;
                } else {
                    $id = DB::table('permissions')->insertGetId([
                        'name' => $name,
                        'page' => $page,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                    $insertedIds[] = $id;
                }
            }
        }

        // Attach permissions to Super Admin role (role id 1 or access_level super_admin)
        $superAdminRoles = DB::table('roles')
            ->where('id', 1)
            ->orWhere('access_level', 'super_admin')
            ->orWhere('access_level', 'super-admin')
            ->pluck('id');

        foreach ($superAdminRoles as $roleId) {
            foreach ($insertedIds as $permId) {
                DB::table('permission_roles')->updateOrInsert(
                    ['role_id' => $roleId, 'permission_id' => $permId],
                    ['created_at' => $now, 'updated_at' => $now]
                );
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $pages = ['invester', 'invester_team_management', 'invester_financial_detail', 'invester_legal_compliance'];
        $permissionIds = DB::table('permissions')->whereIn('page', $pages)->pluck('id');

        DB::table('permission_roles')->whereIn('permission_id', $permissionIds)->delete();
        DB::table('permissions')->whereIn('id', $permissionIds)->delete();
    }
};

