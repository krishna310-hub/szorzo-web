<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->boolean('its_default')->default(false)->after('status')->index();
        });

        // Preserve the protection previously given to the built-in role (ID 1).
        DB::table('roles')->where('id', 1)->update(['its_default' => true]);
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropIndex(['its_default']);
            $table->dropColumn('its_default');
        });
    }
};
