<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            // Personal Information
            $table->string('employee_name');
            $table->date('dob')->nullable();
            $table->string('gender')->nullable();
            $table->string('marital_status')->nullable();
            $table->string('nationality')->nullable();
            $table->string('blood_group')->nullable();

            // Employee Details
            $table->string('employee_no')->nullable();
            $table->string('designation')->nullable();
            $table->string('employee_uan_pf_number')->nullable();
            $table->string('employee_esi_number')->nullable();

            // Contact Information
            $table->string('mobile_number')->nullable();
            $table->string('alternate_mobile_number')->nullable();
            $table->string('official_mail')->nullable();
            $table->string('personal_mail')->nullable();

            // Address Details
            $table->text('permanent_address')->nullable();
            $table->text('current_residential_address')->nullable();

            // Emergency Contact
            $table->string('emergency_contact_name')->nullable();
            $table->string('relationship')->nullable();
            $table->string('emergency_contact_number')->nullable();
            $table->string('emergency_contact_mail')->nullable();
            $table->text('emergency_contact_address')->nullable();

            // Identity Documents
            $table->string('pan_card_number')->nullable();
            $table->string('aadhaar_card_number')->nullable();
            $table->string('passport_number')->nullable();
            $table->date('passport_validity_date')->nullable();

            // Family Information
            $table->string('fathers_name')->nullable();
            $table->string('fathers_mobile_number')->nullable();
            $table->string('mothers_name')->nullable();
            $table->string('siblings_name')->nullable();
            $table->string('husband_wife_name')->nullable();
            $table->date('husband_wife_dob')->nullable();
            $table->string('spouse_mobile_number')->nullable();
            $table->text('childrens_name_dob')->nullable();

            // Bank Details
            $table->string('bank_name')->nullable();
            $table->string('account_holders_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('branch_ifsc_code')->nullable();
            $table->string('mode_of_salary')->nullable();
            $table->string('bank_uan_pf_number')->nullable();
            $table->string('bank_esi_number')->nullable();

            // Health Information
            $table->text('any_health_issue')->nullable();

            // Additional Information
            $table->text('passion')->nullable();
            $table->text('awards_appreciation')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
