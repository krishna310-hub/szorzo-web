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
        $now = now();

        // 1. Ensure Role with ID 6 exists as default Invester role
        if (Schema::hasTable('roles')) {
            $existingRole6 = DB::table('roles')->where('id', 6)->first();
            if ($existingRole6) {
                DB::table('roles')->where('id', 6)->update([
                    'name' => 'Invester',
                    'access_level' => 'invester',
                    'status' => 1,
                    'its_default' => 1,
                    'updated_at' => $now,
                ]);
            } else {
                DB::table('roles')->insert([
                    'id' => 6,
                    'name' => 'Invester',
                    'access_level' => 'invester',
                    'status' => 1,
                    'its_default' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        // 2. Add user_id to investers table for login credentials
        if (Schema::hasTable('investers') && !Schema::hasColumn('investers', 'user_id')) {
            Schema::table('investers', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->after('invester_id')->constrained('users')->nullOnDelete();
            });
        }

        // 3. Create invester_deployments table (Invest Modules: Employment Details & Profit Sharing)
        if (!Schema::hasTable('invester_deployments')) {
            Schema::create('invester_deployments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('invester_id')->constrained('investers')->cascadeOnDelete();
                $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete();
                $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
                $table->string('mode_of_engagement')->default('Contract'); // e.g. Contract, Contract to Hire, Permanent
                $table->string('contract_duration')->nullable(); // e.g. 6 Months, 12 Months
                $table->decimal('approved_rate_card', 15, 2)->default(0); // Client billing rate card (e.g. 200000)
                $table->decimal('employee_ctc', 15, 2)->default(0); // Employee CTC (e.g. 1790000)
                $table->decimal('margin_percentage', 5, 2)->default(15.00); // Margin % (e.g. 15%)
                $table->decimal('tds_percentage', 5, 2)->default(10.00); // TDS % (e.g. 10%)
                $table->decimal('margin_amount', 15, 2)->default(0); // Margin in Rupees (yellow highlighted in photo)
                $table->decimal('szorzo_share_percentage', 5, 2)->default(70.00); // SZ Profit share % (70.00%)
                $table->decimal('investor_share_percentage', 5, 2)->default(30.00); // Investor Profit share % (30.00%)
                $table->decimal('szorzo_commission_amount', 15, 2)->default(0); // SZ share in Rs
                $table->decimal('investor_commission_amount', 15, 2)->default(0); // Investor share in Rs
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->text('notes')->nullable();
                $table->boolean('status')->default(true); // Active / Inactive
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // 4. Permissions for invester_deployment module
        if (Schema::hasTable('permissions')) {
            $abilities = ['Read', 'Create', 'Edit', 'Delete'];
            $permIds = [];
            foreach ($abilities as $ability) {
                $existing = DB::table('permissions')
                    ->where('name', $ability)
                    ->where('page', 'invester_deployment')
                    ->first();

                if ($existing) {
                    $permIds[] = $existing->id;
                } else {
                    $newId = DB::table('permissions')->insertGetId([
                        'name' => $ability,
                        'page' => 'invester_deployment',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                    $permIds[] = $newId;
                }
            }

            // Assign to Super Admin (Role 1)
            foreach ($permIds as $pid) {
                DB::table('permission_roles')->updateOrInsert(
                    ['role_id' => 1, 'permission_id' => $pid],
                    ['created_at' => $now, 'updated_at' => $now]
                );
            }

            // Assign Read permissions to Invester Role (Role 6)
            $investerReadablePages = [
                'dashboard' => ['Read'],
                'invester' => ['Read'],
                'invester_financial_detail' => ['Read'],
                'invester_team_management' => ['Read'],
                'invester_legal_compliance' => ['Read'],
                'invester_deployment' => ['Read'],
                'client' => ['Read'],
                'employee' => ['Read'],
            ];

            foreach ($investerReadablePages as $page => $names) {
                foreach ($names as $name) {
                    $perm = DB::table('permissions')->where('name', $name)->where('page', $page)->first();
                    if ($perm) {
                        DB::table('permission_roles')->updateOrInsert(
                            ['role_id' => 6, 'permission_id' => $perm->id],
                            ['created_at' => $now, 'updated_at' => $now]
                        );
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invester_deployments');

        if (Schema::hasTable('investers') && Schema::hasColumn('investers', 'user_id')) {
            Schema::table('investers', function (Blueprint $table) {
                $table->dropConstrainedForeignId('user_id');
            });
        }

        if (Schema::hasTable('permissions')) {
            $permIds = DB::table('permissions')->where('page', 'invester_deployment')->pluck('id');
            DB::table('permission_roles')->whereIn('permission_id', $permIds)->delete();
            DB::table('permissions')->whereIn('id', $permIds)->delete();
        }
    }
};

