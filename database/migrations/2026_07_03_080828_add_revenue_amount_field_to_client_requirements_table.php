<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('client_requirements', function (Blueprint $table) {
            $table->decimal('revenue_amount', 12, 2)->nullable()->after('billing_id');
        });
    }

    public function down(): void
    {
        Schema::table('client_requirements', function (Blueprint $table) {
            $table->dropcolumn('revenue_amount', 12, 2);
        });
    }
};
