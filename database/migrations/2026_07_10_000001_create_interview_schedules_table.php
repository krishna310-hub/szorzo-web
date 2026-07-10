<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interview_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained('candidates')->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('job_role_id')->nullable()->constrained('job_roles')->nullOnDelete();
            $table->foreignId('level_of_interview_id')->nullable()->constrained('level_of_interviews')->nullOnDelete();
            $table->dateTime('schedule_date');
            $table->string('status')->default('scheduled');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interview_schedules');
    }
};
