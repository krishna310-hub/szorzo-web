<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('client_requirements', function (Blueprint $table) {
            $table->json('mode_ids')->nullable()->after('mode_id');
            $table->json('location_ids')->nullable()->after('location_id');
        });

        Schema::table('billings', function (Blueprint $table) {
            $table->string('invoice_number')->nullable()->unique()->after('id');
            $table->string('title')->nullable()->after('invoice_number');
            $table->date('invoice_date')->nullable()->after('value');
            $table->decimal('amount', 12, 2)->nullable()->after('invoice_date');
            $table->text('notes')->nullable()->after('amount');
        });
    }

    public function down(): void
    {
        Schema::table('client_requirements', function (Blueprint $table) {
            $table->dropColumn(['mode_ids', 'location_ids']);
        });

        Schema::table('billings', function (Blueprint $table) {
            $table->dropUnique(['invoice_number']);
            $table->dropColumn(['invoice_number', 'title', 'invoice_date', 'amount', 'notes']);
        });
    }
};
