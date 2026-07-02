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
        Schema::table('client_job_roles', function (Blueprint $table) {
            $table->string('poc_name')->nullable()->after('job_role_id');
            $table->string('contact_number')->nullable()->after('poc_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('client_job_roles', function (Blueprint $table) {
            $table->dropColumn(['poc_name', 'contact_number']);
        });
    }
};
