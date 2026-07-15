<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('interview_schedules', function (Blueprint $table) {
            $table->foreignId('interview_mode_id')
                ->nullable()
                ->after('job_role_id')
                ->constrained('interview_modes')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('interview_schedules', function (Blueprint $table) {
            $table->dropForeign(['interview_mode_id']);
            $table->dropColumn('interview_mode_id');
        });
    }
};
