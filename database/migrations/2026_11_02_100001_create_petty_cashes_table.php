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
        Schema::create('petty_cashes', function (Blueprint $table) {
            $table->id();
            $table->enum('transaction_type', ['credit', 'debit'])->comment('credit = Cash In / Top-up, debit = Cash Out / Expense');
            $table->decimal('amount', 12, 2);
            $table->date('transaction_date');
            $table->time('transaction_time')->nullable();
            $table->string('category')->default('Miscellaneous');
            $table->text('title')->comment('Reason or description of the expense or credit');
            $table->string('spent_by')->nullable()->comment('Person who spent or payee or source');
            $table->string('payment_mode')->default('Cash')->comment('Cash, UPI, Bank Transfer, Card, Cheque');
            $table->string('reference_no')->nullable()->comment('Bill no, receipt no, UTR, cheque no');
            $table->string('receipt_file')->nullable()->comment('Attachment path for receipt/bill');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('transaction_type');
            $table->index('transaction_date');
            $table->index('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('petty_cashes');
    }
};

