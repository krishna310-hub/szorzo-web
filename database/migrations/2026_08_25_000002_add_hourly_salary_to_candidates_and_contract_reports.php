<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->boolean('is_hourly')->default(false)->after('contract_to_date');
            $table->decimal('hourly_salary', 12, 2)->nullable()->after('is_hourly');
        });

        Schema::table('contract_reports', function (Blueprint $table) {
            $table->boolean('is_hourly')->default(false)->after('monthly_take_home');
            $table->decimal('hourly_salary', 12, 2)->nullable()->after('is_hourly');
        });
    }

    public function down(): void
    {
        Schema::table('contract_reports', function (Blueprint $table) {
            $table->dropColumn(['is_hourly', 'hourly_salary']);
        });

        Schema::table('candidates', function (Blueprint $table) {
            $table->dropColumn(['is_hourly', 'hourly_salary']);
        });
    }
};
