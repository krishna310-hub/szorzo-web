<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contract_reports', function (Blueprint $table) {
            $table->decimal('worked_hours', 8, 2)->nullable()->after('absent_days');
        });
    }

    public function down(): void
    {
        Schema::table('contract_reports', function (Blueprint $table) {
            $table->dropColumn('worked_hours');
        });
    }
};
