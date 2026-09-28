<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addMissingColumns('lead_generations', [
            'account_id' => fn (Blueprint $table) => $table->string('account_id')->nullable(),
            'account_source' => fn (Blueprint $table) => $table->string('account_source')->nullable(),
            'account_name' => fn (Blueprint $table) => $table->string('account_name')->nullable(),
            'industry' => fn (Blueprint $table) => $table->string('industry')->nullable(),
            'sub_industry' => fn (Blueprint $table) => $table->string('sub_industry')->nullable(),
            'website_url' => fn (Blueprint $table) => $table->string('website_url')->nullable(),
            'country_of_origin' => fn (Blueprint $table) => $table->string('country_of_origin')->nullable(),
            'region' => fn (Blueprint $table) => $table->string('region')->nullable(),
            'state' => fn (Blueprint $table) => $table->string('state')->nullable(),
            'city' => fn (Blueprint $table) => $table->string('city')->nullable(),
            'registered_address' => fn (Blueprint $table) => $table->text('registered_address')->nullable(),
            'pin_code' => fn (Blueprint $table) => $table->string('pin_code')->nullable(),
            'ownership_type' => fn (Blueprint $table) => $table->string('ownership_type')->nullable(),
            'registration_id' => fn (Blueprint $table) => $table->string('registration_id')->nullable(),
            'gstin' => fn (Blueprint $table) => $table->string('gstin')->nullable(),
            'account_owner' => fn (Blueprint $table) => $table->string('account_owner')->nullable(),
            'customer_since' => fn (Blueprint $table) => $table->string('customer_since')->nullable(),
            'account_created_date' => fn (Blueprint $table) => $table->date('account_created_date')->nullable(),
            'last_updated_date' => fn (Blueprint $table) => $table->date('last_updated_date')->nullable(),
            'contact_person' => fn (Blueprint $table) => $table->string('contact_person')->nullable(),
            'mobile' => fn (Blueprint $table) => $table->string('mobile', 50)->nullable(),
            'email' => fn (Blueprint $table) => $table->string('email')->nullable(),
            'address' => fn (Blueprint $table) => $table->text('address')->nullable(),
            'interested_service' => fn (Blueprint $table) => $table->string('interested_service')->nullable(),
            'priority' => fn (Blueprint $table) => $table->string('priority')->default('medium'),
            'created_by_user_id' => fn (Blueprint $table) => $table->unsignedBigInteger('created_by_user_id')->nullable(),
            'next_follow_up_type' => fn (Blueprint $table) => $table->string('next_follow_up_type')->nullable(),
            'follow_up_status' => fn (Blueprint $table) => $table->string('follow_up_status')->nullable(),
            'next_follow_up_user_id' => fn (Blueprint $table) => $table->unsignedBigInteger('next_follow_up_user_id')->nullable(),
            'follow_up_reminder_at' => fn (Blueprint $table) => $table->dateTime('follow_up_reminder_at')->nullable(),
            'next_action' => fn (Blueprint $table) => $table->string('next_action')->nullable(),
            'follow_up_reason' => fn (Blueprint $table) => $table->text('follow_up_reason')->nullable(),
            'proposal_sent_at' => fn (Blueprint $table) => $table->dateTime('proposal_sent_at')->nullable(),
            'agreement_status' => fn (Blueprint $table) => $table->string('agreement_status')->nullable(),
            'agreement_signed_at' => fn (Blueprint $table) => $table->dateTime('agreement_signed_at')->nullable(),
            'lost_reason' => fn (Blueprint $table) => $table->string('lost_reason')->nullable(),
            'competitor' => fn (Blueprint $table) => $table->string('competitor')->nullable(),
            'nurture_at' => fn (Blueprint $table) => $table->dateTime('nurture_at')->nullable(),
        ]);

        // Keep old lead records visible after upgrading the earlier legacy schema.
        if (Schema::hasColumn('lead_generations', 'title')) {
            DB::table('lead_generations')->whereNull('account_name')->update(['account_name' => DB::raw('title')]);
        }
        if (Schema::hasColumn('lead_generations', 'lead_source')) {
            DB::table('lead_generations')->whereNull('account_source')->update(['account_source' => DB::raw('lead_source')]);
        }
        if (Schema::hasColumn('lead_generations', 'lead_owner')) {
            DB::table('lead_generations')->whereNull('account_owner')->update(['account_owner' => DB::raw('lead_owner')]);
        }
        if (Schema::hasColumn('lead_generations', 'contact_info')) {
            DB::table('lead_generations')->whereNull('mobile')->update(['mobile' => DB::raw('contact_info')]);
        }
        DB::table('lead_generations')->whereNull('pipeline_stage')->update(['pipeline_stage' => 'new']);
        DB::table('lead_generations')->whereNull('priority')->update(['priority' => 'medium']);

        $this->addMissingColumns('client_profiles', [
            'account_id' => fn (Blueprint $table) => $table->string('account_id')->nullable(),
            'client_id' => fn (Blueprint $table) => $table->string('client_id')->nullable(),
            'service_id' => fn (Blueprint $table) => $table->string('service_id')->nullable(),
            'legal_entity_name' => fn (Blueprint $table) => $table->string('legal_entity_name')->nullable(),
            'sub_industry' => fn (Blueprint $table) => $table->string('sub_industry')->nullable(),
            'website_url' => fn (Blueprint $table) => $table->string('website_url')->nullable(),
            'country_of_origin' => fn (Blueprint $table) => $table->string('country_of_origin')->nullable(),
            'region' => fn (Blueprint $table) => $table->string('region')->nullable(),
            'state' => fn (Blueprint $table) => $table->string('state')->nullable(),
            'city' => fn (Blueprint $table) => $table->string('city')->nullable(),
            'registered_address' => fn (Blueprint $table) => $table->text('registered_address')->nullable(),
            'pin_code' => fn (Blueprint $table) => $table->string('pin_code')->nullable(),
            'ownership_type' => fn (Blueprint $table) => $table->string('ownership_type')->nullable(),
            'registration_id' => fn (Blueprint $table) => $table->string('registration_id')->nullable(),
            'gstin' => fn (Blueprint $table) => $table->string('gstin')->nullable(),
            'customer_domain' => fn (Blueprint $table) => $table->string('customer_domain')->nullable(),
            'customer_since' => fn (Blueprint $table) => $table->string('customer_since')->nullable(),
            'account_created_date' => fn (Blueprint $table) => $table->date('account_created_date')->nullable(),
            'last_updated_date' => fn (Blueprint $table) => $table->date('last_updated_date')->nullable(),
            'relationship_status' => fn (Blueprint $table) => $table->string('relationship_status')->nullable(),
            'primary_contact_name_designation' => fn (Blueprint $table) => $table->string('primary_contact_name_designation')->nullable(),
            'primary_email' => fn (Blueprint $table) => $table->string('primary_email')->nullable(),
            'primary_contact_number' => fn (Blueprint $table) => $table->string('primary_contact_number')->nullable(),
            'is_converted_to_client' => fn (Blueprint $table) => $table->boolean('is_converted_to_client')->default(false),
        ]);

        if (!Schema::hasTable('lead_activities')) {
            Schema::create('lead_activities', function (Blueprint $table) {
                $table->id();
                $table->foreignId('lead_generation_id')->constrained('lead_generations')->cascadeOnDelete();
                $table->string('activity_type', 50);
                $table->dateTime('occurred_at');
                $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->string('outcome')->nullable();
                $table->text('notes')->nullable();
                $table->unsignedInteger('duration_seconds')->nullable();
                $table->string('interest_level')->nullable();
                $table->string('attachment_path')->nullable();
                $table->dateTime('next_follow_up_at')->nullable();
                $table->string('next_follow_up_type', 50)->nullable();
                $table->foreignId('responsible_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->dateTime('reminder_at')->nullable();
                $table->string('next_action')->nullable();
                $table->string('follow_up_status', 30)->nullable();
                $table->text('reschedule_reason')->nullable();
                $table->string('location')->nullable();
                $table->string('meeting_link')->nullable();
                $table->text('attendees')->nullable();
                $table->timestamps();
                $table->index(['follow_up_status', 'next_follow_up_at']);
            });
        }

        if (!Schema::hasTable('lead_assignment_histories')) {
            Schema::create('lead_assignment_histories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('lead_generation_id')->constrained('lead_generations')->cascadeOnDelete();
                $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('previous_assigned_to')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
                $table->text('reason')->nullable();
                $table->timestamp('assigned_at');
                $table->timestamps();
            });
        }

        foreach ([
            ['table' => 'lead_generations', 'column' => 'assigned_to', 'ref' => 'users'],
            ['table' => 'lead_generations', 'column' => 'created_by_user_id', 'ref' => 'users'],
            ['table' => 'lead_generations', 'column' => 'next_follow_up_user_id', 'ref' => 'users'],
            ['table' => 'lead_generations', 'column' => 'client_profile_id', 'ref' => 'client_profiles'],
            ['table' => 'client_profiles', 'column' => 'lead_generation_id', 'ref' => 'lead_generations'],
            ['table' => 'client_profiles', 'column' => 'assigned_to', 'ref' => 'users'],
        ] as $constraint) {
            if (Schema::hasColumn($constraint['table'], $constraint['column']) && !$this->hasForeignKey($constraint['table'], $constraint['column']) && !$this->hasOrphanReferences($constraint['table'], $constraint['column'], $constraint['ref'])) {
                Schema::table($constraint['table'], function (Blueprint $table) use ($constraint) {
                    $table->foreign($constraint['column'])->references('id')->on($constraint['ref'])->nullOnDelete();
                });
            }
        }
    }

    private function addMissingColumns(string $tableName, array $columns): void
    {
        foreach ($columns as $column => $definition) {
            if (!Schema::hasColumn($tableName, $column)) {
                Schema::table($tableName, function (Blueprint $table) use ($definition) {
                    $definition($table);
                });
            }
        }
    }

    private function hasOrphanReferences(string $table, string $column, string $parentTable): bool
    {
        return DB::table($table)->whereNotNull($column)->whereNotExists(function ($query) use ($table, $column, $parentTable) {
            $query->selectRaw('1')->from($parentTable)->whereColumn($parentTable.'.id', $table.'.'.$column);
        })->exists();
    }

    private function hasForeignKey(string $table, string $column): bool
    {
        return DB::table('information_schema.KEY_COLUMN_USAGE')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $table)
            ->where('COLUMN_NAME', $column)
            ->whereNotNull('REFERENCED_TABLE_NAME')
            ->exists();
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_assignment_histories');
        Schema::dropIfExists('lead_activities');
        // Keep compatibility columns and backfilled lead/client data when rolling back.
    }
};
