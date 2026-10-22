<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('invester_deployments')) {
            Schema::table('invester_deployments', function (Blueprint $table) {
                if (!Schema::hasColumn('invester_deployments', 'client_ids')) {
                    $table->json('client_ids')->nullable()->after('client_id');
                }
                if (!Schema::hasColumn('invester_deployments', 'employee_ids')) {
                    $table->json('employee_ids')->nullable()->after('employee_id');
                }
                if (!Schema::hasColumn('invester_deployments', 'billing_percentage')) {
                    $table->decimal('billing_percentage', 8, 2)->nullable()->after('employee_ctc');
                }
                if (!Schema::hasColumn('invester_deployments', 'tds_amount')) {
                    $table->decimal('tds_amount', 15, 2)->default(0)->after('tds_percentage');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('invester_deployments')) {
            Schema::table('invester_deployments', function (Blueprint $table) {
                $columns = ['client_ids', 'employee_ids', 'billing_percentage', 'tds_amount'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('invester_deployments', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};

