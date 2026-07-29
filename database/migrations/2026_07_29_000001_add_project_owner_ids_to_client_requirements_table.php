<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('client_requirements', function (Blueprint $table) {
            $table->json('project_owner_ids')->nullable()->after('project_owner');
        });

        DB::table('client_requirements')
            ->whereNotNull('project_owner')
            ->orderBy('id')
            ->eachById(function ($requirement) {
                DB::table('client_requirements')
                    ->where('id', $requirement->id)
                    ->update(['project_owner_ids' => json_encode([(int) $requirement->project_owner])]);
            });
    }

    public function down(): void
    {
        Schema::table('client_requirements', function (Blueprint $table) {
            $table->dropColumn('project_owner_ids');
        });
    }
};
