<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('revenues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->unique()->constrained()->restrictOnDelete();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->string('invoice_number')->unique();
            $table->date('invoice_date');
            $table->string('universe_number')->nullable();
            $table->string('client_name');
            $table->text('client_address')->nullable();
            $table->string('client_gst_number')->nullable();
            $table->decimal('offered_ctc', 14, 2);
            $table->decimal('billing_percentage', 8, 2);
            $table->decimal('service_amount', 14, 2);
            $table->decimal('gst_percentage', 5, 2)->default(18);
            $table->decimal('gst_amount', 14, 2);
            $table->decimal('total_amount', 14, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('revenues');
    }
};
