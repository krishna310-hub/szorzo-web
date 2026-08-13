<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->json('previous_company_offer_letters')->nullable()->after('intent_letter');
            $table->json('relieving_letters')->nullable()->after('previous_company_offer_letters');
            $table->json('pay_slips')->nullable()->after('relieving_letters');
            $table->json('bank_statements')->nullable()->after('pay_slips');
            $table->json('passbook_cheques')->nullable()->after('bank_statements');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'previous_company_offer_letters',
                'relieving_letters',
                'pay_slips',
                'bank_statements',
                'passbook_cheques',
            ]);
        });
    }
};
