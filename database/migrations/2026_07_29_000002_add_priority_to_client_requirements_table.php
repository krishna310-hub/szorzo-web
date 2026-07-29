<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('client_requirements', function (Blueprint $table) {
            $table->boolean('is_priority')->default(false)->after('project_owner_ids');
        });
    }

    public function down(): void
    {
        Schema::table('client_requirements', function (Blueprint $table) {
            $table->dropColumn('is_priority');
        });
    }
};
