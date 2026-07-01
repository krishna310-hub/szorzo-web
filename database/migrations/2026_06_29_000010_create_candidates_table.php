<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recruiter_id')->nullable()->constrained('recruiters')->nullOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('job_role_id')->nullable()->constrained('job_roles')->nullOnDelete();
            $table->string('candidate_name');
            $table->string('mobile_no')->nullable();
            $table->string('email')->nullable();
            $table->string('qualification')->nullable();
            $table->decimal('total_experience', 5, 2)->nullable();
            $table->decimal('relevant_experience', 5, 2)->nullable();
            $table->decimal('take_home', 12, 2)->nullable();
            $table->decimal('variable', 12, 2)->nullable();
            $table->decimal('current_ctc', 12, 2)->nullable();
            $table->decimal('expected_ctc', 12, 2)->nullable();
            $table->string('notice_period')->nullable();
            $table->string('current_company')->nullable();
            $table->string('current_location')->nullable();
            $table->string('preferred_location')->nullable();
            $table->text('reason_for_change')->nullable();
            $table->foreignId('level_of_interview_id')->nullable()->constrained('level_of_interviews')->nullOnDelete();
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidates');
    }
};
