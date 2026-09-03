<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Relax candidate_id on revenues (drop unique index and make nullable)
        Schema::table('revenues', function (Blueprint $table) {
            // Drop foreign key before altering unique index if needed
            try {
                $table->dropForeign(['candidate_id']);
            } catch (\Throwable) {}

            try {
                $table->dropUnique(['candidate_id']);
            } catch (\Throwable) {}

            $table->foreignId('candidate_id')->nullable()->change()->constrained('candidates')->nullOnDelete();
        });

        // 2. Create candidate_revenue pivot table for multi-candidate invoicing
        Schema::create('candidate_revenue', function (Blueprint $table) {
            $table->id();
            $table->foreignId('revenue_id')->constrained('revenues')->cascadeOnDelete();
            $table->foreignId('candidate_id')->constrained('candidates')->cascadeOnDelete();
            $table->string('contract_month', 7)->nullable(); // e.g. '2026-09'
            $table->decimal('payable_salary', 14, 2)->nullable();
            $table->decimal('service_amount', 14, 2)->nullable();
            $table->timestamps();

            $table->unique(['revenue_id', 'candidate_id']);
        });

        // 3. Backfill existing revenues into candidate_revenue pivot table
        $existingRevenues = DB::table('revenues')->whereNotNull('candidate_id')->get();
        foreach ($existingRevenues as $rev) {
            DB::table('candidate_revenue')->insertOrIgnore([
                'revenue_id' => $rev->id,
                'candidate_id' => $rev->candidate_id,
                'payable_salary' => $rev->onboarding_ctc ?? $rev->offered_ctc,
                'service_amount' => $rev->service_amount,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('candidate_revenue');

        Schema::table('revenues', function (Blueprint $table) {
            try {
                $table->unique('candidate_id');
            } catch (\Throwable) {}
        });
    }
};

