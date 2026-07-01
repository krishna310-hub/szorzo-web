<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->decimal('billing', 8, 2)->nullable();
            $table->foreignId('job_description_id')->nullable()->constrained('client_job_roles')->nullOnDelete();
            $table->foreignId('mode_id')->nullable()->constrained('modes')->nullOnDelete();
            $table->date('requirement_open_date')->nullable();
            $table->foreignId('job_role_id')->nullable()->constrained('job_roles')->nullOnDelete();
            $table->unsignedInteger('number_of_position')->default(0);
            $table->date('closure_target_date')->nullable();
            $table->unsignedInteger('cv_required')->default(0);
            $table->unsignedInteger('cv_uploaded')->default(0);
            $table->foreignId('project_owner')->nullable()->constrained('recruiters')->nullOnDelete();
            $table->decimal('ctc', 12, 2)->nullable();
            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_requirements');
    }
};
