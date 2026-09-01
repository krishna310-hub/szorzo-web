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
                ['name' => $name, 'page' => 'contract_report'],
                ['updated_at' => $now, 'created_at' => $now]
            );
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
