<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('client_requirements', function (Blueprint $table) {
            $table->unsignedInteger('payment_cycle')->nullable()->default(0)->after('revenue_amount');
        });
    }

    public function down(): void
    {
        Schema::table('client_requirements', function (Blueprint $table) {
            $table->dropColumn('payment_cycle');
        });
    }
};
