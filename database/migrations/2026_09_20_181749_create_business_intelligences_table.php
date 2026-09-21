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
        Schema::create('business_intelligences', function (Blueprint $table) {
            $table->id();
            $table->string('contact_id')->nullable();
            $table->string('account_id')->nullable();
            $table->string('account_name');
            $table->string('contact_name');
            $table->string('designation')->nullable();
            $table->string('department')->nullable();
            $table->string('mobile_number')->nullable();
            $table->string('alternate_contact')->nullable();
            $table->string('email_id')->nullable();
            $table->string('contact_type')->nullable();
            $table->date('last_contacted_date')->nullable();
            $table->date('next_follow_up_date')->nullable();
            $table->text('contact_notes')->nullable();
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
        Schema::dropIfExists('business_intelligences');
    }
};
