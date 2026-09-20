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
        Schema::create('lead_generations', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('lead_source')->nullable();
            $table->string('lead_owner')->nullable();
            $table->string('relationship_manager')->nullable();
            $table->string('contact_info')->nullable();
            $table->unsignedBigInteger('client_profile_id')->nullable();
            $table->unsignedBigInteger('service_id')->nullable();
            $table->date('lead_date')->nullable();
            $table->date('follow_up_date')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_generations');
    }
};
