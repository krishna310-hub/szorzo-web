<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create holidays table
        if (!Schema::hasTable('holidays')) {
            Schema::create('holidays', function (Blueprint $table) {
                $table->id();
                $table->string('title')->comment('Name or title of holiday');
                $table->date('date')->unique()->comment('Holiday date');
                $table->unsignedSmallInteger('year')->index()->comment('Year of holiday');
                $table->string('type', 100)->default('Company')->comment('National, Festival, Company, Public, Restricted');
                $table->text('description')->nullable()->comment('Additional details or notes');
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['year', 'date']);
            });
        }

        // 2. Seed holiday permissions
        $now = now();
        $abilities = ['Read', 'Create', 'Edit', 'Delete'];
        $insertedIds = [];

        foreach ($abilities as $name) {
            $permission = DB::table('permissions')->where('name', $name)->where('page', 'holiday')->first();
            if ($permission) {
                $insertedIds[] = $permission->id;
            } else {
                $id = DB::table('permissions')->insertGetId([
                    'name' => $name,
                    'page' => 'holiday',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $insertedIds[] = $id;
            }
        }

        // 3. Attach holiday permissions to Admin / Super Admin roles
        $adminRoles = DB::table('roles')
            ->where('id', 1)
            ->orWhere('access_level', 'super_admin')
            ->orWhere('access_level', 'super-admin')
            ->orWhere('access_level', 'admin')
            ->pluck('id');

        foreach ($adminRoles as $roleId) {
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
        // Clean up permissions
        $permIds = DB::table('permissions')->where('page', 'holiday')->pluck('id');
        DB::table('permission_roles')->whereIn('permission_id', $permIds)->delete();
        DB::table('permissions')->whereIn('id', $permIds)->delete();

        // Drop table
        Schema::dropIfExists('holidays');
    }
};
