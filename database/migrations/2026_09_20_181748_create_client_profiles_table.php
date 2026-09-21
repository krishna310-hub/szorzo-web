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
        Schema::create('client_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('account_id')->nullable();
            $table->string('client_id')->nullable();
            $table->string('service_id')->nullable();
            $table->string('legal_entity_name')->nullable();
            $table->string('account_name');
            $table->string('industry')->nullable();
            $table->string('sub_industry')->nullable();
            $table->string('website_url')->nullable();
            $table->string('country_of_origin')->nullable();
            $table->string('region')->nullable();
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->text('registered_address')->nullable();
            $table->string('pin_code')->nullable();
            $table->string('ownership_type')->nullable();
            $table->string('registration_id')->nullable();
            $table->string('gstin')->nullable();
            $table->string('account_source')->nullable();
            $table->string('customer_domain')->nullable();
            $table->string('account_owner')->nullable();
            $table->string('relationship_manager')->nullable();
            $table->string('customer_since')->nullable();
            $table->date('account_created_date')->nullable();
            $table->date('last_updated_date')->nullable();
            $table->string('relationship_status')->nullable();
            $table->string('primary_contact_name_designation')->nullable();
            $table->string('primary_email')->nullable();
            $table->string('primary_contact_number')->nullable();
            $table->boolean('status')->default(true);
            $table->boolean('is_converted_to_client')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_profiles');
    }
};
