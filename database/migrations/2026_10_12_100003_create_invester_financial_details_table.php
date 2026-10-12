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
        Schema::create('invester_financial_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invester_id')->constrained('investers')->cascadeOnDelete();
            $table->decimal('investment_amount', 15, 2)->nullable(); // Total Investment Amount / Committed Capital
            $table->decimal('equity_percentage', 5, 2)->nullable(); // Equity Ownership Percentage (%)
            $table->string('shares_units_held')->nullable(); // Number of Shares / Units Held
            $table->date('investment_date')->nullable(); // Date of Investment
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
        Schema::dropIfExists('invester_financial_details');
    }
};

