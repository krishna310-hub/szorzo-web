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
        Schema::create('investers', function (Blueprint $table) {
            $table->id();
            $table->string('invester_id')->unique(); // e.g. SZ INV 0001
            $table->string('name'); // Invester Name
            $table->string('profile_picture')->nullable(); // Profile Picture
            $table->string('investor_type')->nullable(); // e.g. Angel Investor, Venture Capitalist, Institutional, Strategic Partner, Family Office
            $table->string('contact')->nullable(); // Contact
            $table->string('primary_email')->nullable(); // Primary Email
            $table->string('alternate_email')->nullable(); // Alternate Email
            $table->text('address')->nullable(); // Residential / Office Address
            $table->foreignId('account_manager_id')->nullable()->constrained('users')->nullOnDelete(); // Account Manager / Internal Point of Contact
            $table->text('internal_notes')->nullable(); // Internal Notes
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
        Schema::dropIfExists('investers');
    }
};

