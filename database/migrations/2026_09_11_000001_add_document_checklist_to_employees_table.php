<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (!Schema::hasColumn('employees', 'document_checklist')) {
                $table->json('document_checklist')->nullable()->after('status');
            }
            if (!Schema::hasColumn('employees', 'educational_certificates')) {
                $table->json('educational_certificates')->nullable()->after('degree_certificate');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (Schema::hasColumn('employees', 'document_checklist')) {
                $table->dropColumn('document_checklist');
            }
            if (Schema::hasColumn('employees', 'educational_certificates')) {
                $table->dropColumn('educational_certificates');
            }
        });
    }
};

