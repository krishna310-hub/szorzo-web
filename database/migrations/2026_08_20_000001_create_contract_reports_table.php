<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained('candidates')->cascadeOnDelete();
            $table->date('salary_month');
            $table->decimal('monthly_take_home', 12, 2)->default(0);
            $table->unsignedTinyInteger('present_days')->default(0);
            $table->unsignedTinyInteger('absent_days')->default(0);
            $table->decimal('payable_salary', 12, 2)->default(0);
            $table->timestamps();

            $table->unique(['candidate_id', 'salary_month']);
            $table->index('salary_month');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_reports');
    }
};
