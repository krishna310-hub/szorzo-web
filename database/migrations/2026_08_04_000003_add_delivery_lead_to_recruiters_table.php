<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recruiters', function (Blueprint $table) {
            $table->foreignId('delivery_lead_user_id')->nullable()->after('performance_rating')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('recruiters', function (Blueprint $table) {
            $table->dropConstrainedForeignId('delivery_lead_user_id');
        });
    }
};
