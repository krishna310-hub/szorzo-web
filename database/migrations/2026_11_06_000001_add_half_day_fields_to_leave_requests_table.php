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
        Schema::table('leave_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('leave_requests', 'day_type')) {
                $table->string('day_type', 20)->default('full_day')->after('number_of_days');
            }
            if (!Schema::hasColumn('leave_requests', 'half_day_type')) {
                $table->string('half_day_type', 20)->nullable()->after('day_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            if (Schema::hasColumn('leave_requests', 'half_day_type')) {
                $table->dropColumn('half_day_type');
            }
            if (Schema::hasColumn('leave_requests', 'day_type')) {
                $table->dropColumn('day_type');
            }
        });
    }
};
