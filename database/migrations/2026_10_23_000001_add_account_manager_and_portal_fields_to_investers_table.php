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
        Schema::table('investers', function (Blueprint $table) {
            $table->string('account_manager_name')->nullable()->after('account_manager_ids');
            $table->string('account_manager_email')->nullable()->after('account_manager_name');
            $table->string('account_manager_contact')->nullable()->after('account_manager_email');
            $table->string('account_manager_role')->nullable()->after('account_manager_contact');
            $table->string('portal_username')->nullable()->after('user_id');
            $table->string('portal_password')->nullable()->after('portal_username');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('investers', function (Blueprint $table) {
            $table->dropColumn([
                'account_manager_name',
                'account_manager_email',
                'account_manager_contact',
                'account_manager_role',
                'portal_username',
                'portal_password',
            ]);
        });
    }
};

