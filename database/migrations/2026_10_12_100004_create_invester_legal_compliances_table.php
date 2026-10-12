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
        Schema::create('invester_legal_compliances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invester_id')->constrained('investers')->cascadeOnDelete();
            $table->string('tax_id_number')->nullable(); // Tax Identification Number (TIN) / PAN / SSN
            $table->string('kyc_aml_status')->default('Pending'); // Verified, Pending, Rejected
            $table->string('nda_signed_doc')->nullable(); // document attachment link for IRA, SHA, NDA
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
        Schema::dropIfExists('invester_legal_compliances');
    }
};

