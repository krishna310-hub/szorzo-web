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
            $table->decimal('billing_percentage', 8, 2)->nullable();
            $table->string('job_description_id')->nullable();
            $table->foreignId('mode_id')->nullable()->constrained('modes')->nullOnDelete();
            $table->date('open_date')->nullable();
            $table->foreignId('job_role_id')->nullable()->constrained('job_roles')->nullOnDelete();
            $table->decimal('ctc', 12, 2)->nullable();
            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->unsignedInteger('no_of_positions')->default(0);
            $table->date('closure_target_date')->nullable();
            $table->unsignedInteger('cvs_required')->default(0);
            $table->unsignedInteger('cvs_uploaded')->default(0);
            $table->foreignId('project_owner_id')->nullable()->constrained('recruiters')->nullOnDelete();
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
