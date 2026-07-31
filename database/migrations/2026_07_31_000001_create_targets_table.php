<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void { Schema::create('targets', function (Blueprint $table) { $table->id(); $table->string('target_name')->unique(); $table->unsignedInteger('monthly_target'); $table->boolean('status')->default(true); $table->timestamps(); $table->softDeletes(); }); }
    public function down(): void { Schema::dropIfExists('targets'); }
};
