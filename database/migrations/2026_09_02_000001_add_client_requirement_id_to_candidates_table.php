<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->foreignId('client_requirement_id')
                ->nullable()
                ->after('client_id')
                ->constrained('client_requirements')
                ->nullOnDelete();
        });

        // Existing rows can be mapped safely only when the client/role pair has
        // exactly one requirement. Ambiguous rows must be selected by a user.
        $uniqueRequirements = DB::table('client_requirements')
            ->select('client_id', 'job_role_id', DB::raw('MIN(id) as requirement_id'))
            ->whereNull('deleted_at')
            ->groupBy('client_id', 'job_role_id')
            ->havingRaw('COUNT(*) = 1')
            ->get();

        foreach ($uniqueRequirements as $requirement) {
            DB::table('candidates')
                ->where('client_id', $requirement->client_id)
                ->where('job_role_id', $requirement->job_role_id)
                ->whereNull('client_requirement_id')
                ->update(['client_requirement_id' => $requirement->requirement_id]);
        }
    }

    public function down(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->dropConstrainedForeignId('client_requirement_id');
        });
    }
};
