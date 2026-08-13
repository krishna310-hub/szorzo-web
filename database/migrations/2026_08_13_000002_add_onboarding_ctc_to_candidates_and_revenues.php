<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->decimal('onboarding_ctc', 14, 2)->nullable()->after('expected_ctc');
        });

        Schema::table('revenues', function (Blueprint $table) {
            $table->decimal('onboarding_ctc', 14, 2)->nullable()->after('offered_ctc');
        });
    }

    public function down(): void
    {
        Schema::table('revenues', fn (Blueprint $table) => $table->dropColumn('onboarding_ctc'));
        Schema::table('candidates', fn (Blueprint $table) => $table->dropColumn('onboarding_ctc'));
    }
};
