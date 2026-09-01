<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        foreach (['Read', 'Edit', 'Download'] as $name) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $name, 'page' => 'contract_report'],
                ['updated_at' => $now, 'created_at' => $now]
            );
        }

        $newPermissions = DB::table('permissions')
            ->where('page', 'contract_report')
            ->pluck('id', 'name');

        $abilitySources = [
            'Read' => 'Read',
            'Edit' => 'Export',
            'Download' => 'Export',
        ];

        foreach ($abilitySources as $ability => $sourceAbility) {
            $sourcePermissionId = DB::table('permissions')
                ->where('page', 'reports')
                ->where('name', $sourceAbility)
                ->value('id');

            if (! $sourcePermissionId || ! isset($newPermissions[$ability])) {
                continue;
            }

            foreach (DB::table('permission_roles')->where('permission_id', $sourcePermissionId)->pluck('role_id') as $roleId) {
                DB::table('permission_roles')->updateOrInsert(
                    ['role_id' => $roleId, 'permission_id' => $newPermissions[$ability]],
                    ['updated_at' => $now, 'created_at' => $now]
                );
            }
        }
    }

    public function down(): void
    {
        $permissionIds = DB::table('permissions')
            ->where('page', 'contract_report')
            ->pluck('id');

        DB::table('permission_roles')->whereIn('permission_id', $permissionIds)->delete();
        DB::table('permissions')->whereIn('id', $permissionIds)->delete();
    }
};
