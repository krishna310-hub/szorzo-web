<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('client');
            $table->decimal('billing', 8, 2)->nullable();
            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->string('poc_name')->nullable();
            $table->date('signed_date')->nullable();
            $table->date('renewal_date')->nullable();
            $table->foreignId('division_id')->nullable()->constrained('divisions')->nullOnDelete();
            $table->string('contact_number')->nullable();
            $table->string('email')->nullable();
            $table->string('mobile_number')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
