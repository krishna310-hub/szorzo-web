<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->foreignId('billing_id')
                ->nullable()
                ->after('client')
                ->constrained('billings')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropForeign(['billing_id']);
            $table->dropColumn('billing_id');

            $table->decimal('billing', 8, 2)
                ->nullable()
                ->after('client');
        });
    }
};
