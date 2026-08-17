<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profile_sourced', function (Blueprint $table) {
            $table->foreignId('job_role_id')->nullable()->after('recruiter_id')->constrained('job_roles')->nullOnDelete();
            $table->string('need')->nullable()->after('candidate_name');
        });
    }

    public function down(): void
    {
        Schema::table('profile_sourced', function (Blueprint $table) {
            $table->dropConstrainedForeignId('job_role_id');
            $table->dropColumn('need');
        });
    }
};
