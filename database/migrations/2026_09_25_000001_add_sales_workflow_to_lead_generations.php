<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // This migration may have been partially applied before a database error.
        // Inspect each table so rerunning it only adds missing schema elements.
        $leadColumns = [
            'assigned_to' => fn (Blueprint $table) => $table->foreignId('assigned_to')->nullable(),
            'pipeline_stage' => fn (Blueprint $table) => $table->string('pipeline_stage')->default('new'),
            'opportunity_status' => fn (Blueprint $table) => $table->string('opportunity_status')->nullable(),
            'next_follow_up_at' => fn (Blueprint $table) => $table->dateTime('next_follow_up_at')->nullable(),
            'follow_up_notes' => fn (Blueprint $table) => $table->text('follow_up_notes')->nullable(),
            'first_contact_at' => fn (Blueprint $table) => $table->dateTime('first_contact_at')->nullable(),
            'client_profile_id' => fn (Blueprint $table) => $table->foreignId('client_profile_id')->nullable(),
        ];

        foreach ($leadColumns as $column => $definition) {
            if (!Schema::hasColumn('lead_generations', $column)) {
                Schema::table('lead_generations', function (Blueprint $table) use ($definition) {
                    $definition($table);
                });
            }
        }

        $profileColumns = [
            'lead_generation_id' => fn (Blueprint $table) => $table->foreignId('lead_generation_id')->nullable(),
            'assigned_to' => fn (Blueprint $table) => $table->foreignId('assigned_to')->nullable(),
        ];

        foreach ($profileColumns as $column => $definition) {
            if (!Schema::hasColumn('client_profiles', $column)) {
                Schema::table('client_profiles', function (Blueprint $table) use ($definition) {
                    $definition($table);
                });
            }
        }

        // Add constraints separately and only when not already present.
        $constraints = [
            ['table' => 'lead_generations', 'column' => 'assigned_to', 'ref' => 'users'],
            ['table' => 'lead_generations', 'column' => 'client_profile_id', 'ref' => 'client_profiles'],
            ['table' => 'client_profiles', 'column' => 'lead_generation_id', 'ref' => 'lead_generations'],
            ['table' => 'client_profiles', 'column' => 'assigned_to', 'ref' => 'users'],
        ];

        foreach ($constraints as $constraint) {
            if (!$this->hasForeignKey($constraint['table'], $constraint['column'])) {
                Schema::table($constraint['table'], function (Blueprint $table) use ($constraint) {
                    $table->foreign($constraint['column'])
                        ->references('id')->on($constraint['ref'])->nullOnDelete();
                });
            }
        }
    }

    public function down(): void
    {
        foreach ([
            'lead_generations' => ['assigned_to', 'client_profile_id', 'pipeline_stage', 'opportunity_status', 'next_follow_up_at', 'follow_up_notes', 'first_contact_at'],
            'client_profiles' => ['lead_generation_id', 'assigned_to'],
        ] as $tableName => $columns) {
            foreach ($columns as $column) {
                if (Schema::hasColumn($tableName, $column)) {
                    if ($this->hasForeignKey($tableName, $column)) {
                        Schema::table($tableName, function (Blueprint $table) use ($column) {
                            $table->dropForeign([$column]);
                        });
                    }
                    Schema::table($tableName, function (Blueprint $table) use ($column) {
                        $table->dropColumn($column);
                    });
                }
            }
        }
    }

    private function hasForeignKey(string $table, string $column): bool
    {
        $database = DB::getDatabaseName();

        return DB::table('information_schema.KEY_COLUMN_USAGE')
            ->where('TABLE_SCHEMA', $database)
            ->where('TABLE_NAME', $table)
            ->where('COLUMN_NAME', $column)
            ->whereNotNull('REFERENCED_TABLE_NAME')
            ->exists();
    }
};
