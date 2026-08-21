<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->date('contract_from_date')->nullable()->after('mode_id');
            $table->date('contract_to_date')->nullable()->after('contract_from_date');
        });
    }

    public function down(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->dropColumn(['contract_from_date', 'contract_to_date']);
        });
    }
};
