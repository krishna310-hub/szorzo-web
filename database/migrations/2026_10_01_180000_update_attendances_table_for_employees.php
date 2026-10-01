<?php

use App\Models\Employee;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Backfill employment_type for existing employees if empty
        foreach (Employee::all() as $emp) {
            if (empty($emp->employment_type)) {
                $emp->update([
                    'employment_type' => $emp->isInternal() ? 'Internal' : 'External',
                ]);
            }
        }

        // 2. Add standalone index for user_id so unique constraint can be dropped
        Schema::table('attendances', function (Blueprint $table) {
            $table->index('user_id', 'attendances_user_id_index');
        });

        // 3. Modify attendances table
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropUnique('attendances_user_id_attendance_date_unique');
            $table->foreignId('user_id')->nullable()->change();
            $table->foreignId('employee_id')->nullable()->after('id')->constrained('employees')->cascadeOnDelete();
            $table->unique(['employee_id', 'attendance_date']);
            $table->index(['status', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropUnique(['employee_id', 'attendance_date']);
            $table->dropIndex(['status', 'employee_id']);
            $table->dropForeign(['employee_id']);
            $table->dropColumn('employee_id');
            $table->unique(['user_id', 'attendance_date']);
            $table->foreignId('user_id')->nullable(false)->change();
            $table->dropIndex('attendances_user_id_index');
        });
    }
};
