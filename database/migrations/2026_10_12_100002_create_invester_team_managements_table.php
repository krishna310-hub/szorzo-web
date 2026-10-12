<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invester_team_managements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invester_id')->constrained('investers')->cascadeOnDelete();
            $table->string('team_name'); // Team / Organization Name
            $table->string('managed_employee_count')->nullable(); // e.g. 1-10, 11-50, 50+
            $table->text('operational_contacts')->nullable(); // chiefs of staff, administrative assistants
            $table->string('governance_role')->nullable(); // e.g. Managing Partner, Lead Operating Officer, Principal
            $table->text('notes')->nullable();
            $table->boolean('status')->default(true); // Active / Inactive
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invester_team_managements');
    }
};

