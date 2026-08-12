<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->foreignId('client_id')->nullable()->after('date_of_joining')->constrained('clients')->nullOnDelete();
            $table->foreignId('mode_id')->nullable()->after('client_id')->constrained('modes')->nullOnDelete();
            $table->date('contract_from_date')->nullable()->after('mode_id');
            $table->date('contract_to_date')->nullable()->after('contract_from_date');
            $table->string('offer_letter')->nullable()->after('contract_to_date');
            $table->string('intent_letter')->nullable()->after('offer_letter');
            $table->string('pan_card_file')->nullable()->after('intent_letter');
            $table->string('aadhaar_file')->nullable()->after('pan_card_file');
            $table->string('twelfth_marksheet')->nullable()->after('aadhaar_file');
            $table->string('tenth_marksheet')->nullable()->after('twelfth_marksheet');
            $table->string('degree_certificate')->nullable()->after('tenth_marksheet');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropConstrainedForeignId('client_id');
            $table->dropConstrainedForeignId('mode_id');
            $table->dropColumn([
                'contract_from_date',
                'contract_to_date',
                'offer_letter',
                'intent_letter',
                'pan_card_file',
                'aadhaar_file',
                'twelfth_marksheet',
                'tenth_marksheet',
                'degree_certificate',
            ]);
        });
    }
};
