<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->string('timer_status', 30)->default('not_started')->after('status');
            $table->timestamp('timer_started_at')->nullable()->after('timer_status');
            $table->timestamp('break_started_at')->nullable()->after('timer_started_at');
            $table->unsignedInteger('total_work_seconds')->default(0)->after('break_started_at');
            $table->unsignedInteger('total_break_seconds')->default(0)->after('total_work_seconds');
            $table->json('breaks')->nullable()->after('total_break_seconds');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn([
                'timer_status',
                'timer_started_at',
                'break_started_at',
                'total_work_seconds',
                'total_break_seconds',
                'breaks',
            ]);
        });
    }
};

