<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->decimal('monthly_gross', 12, 2)->nullable()->after('status');
            $table->decimal('basic_salary', 12, 2)->nullable()->after('monthly_gross');
            $table->decimal('hra', 12, 2)->nullable()->after('basic_salary');
            $table->decimal('conveyance', 12, 2)->nullable()->after('hra');
            $table->decimal('medical_allowance', 12, 2)->nullable()->after('conveyance');
            $table->decimal('special_allowance', 12, 2)->nullable()->after('medical_allowance');
            $table->decimal('overtime_amount', 12, 2)->nullable()->after('special_allowance');
            $table->decimal('lta', 12, 2)->nullable()->after('overtime_amount');
            $table->decimal('arrears', 12, 2)->nullable()->after('lta');
            $table->decimal('pf_deduction', 12, 2)->nullable()->after('arrears');
            $table->decimal('esi_deduction', 12, 2)->nullable()->after('pf_deduction');
            $table->decimal('pt_deduction', 12, 2)->nullable()->after('esi_deduction');
            $table->decimal('income_tax', 12, 2)->nullable()->after('pt_deduction');
            $table->decimal('salary_advance', 12, 2)->nullable()->after('income_tax');
            $table->decimal('fines', 12, 2)->nullable()->after('salary_advance');
            $table->decimal('labour_welfare_fund', 12, 2)->nullable()->after('fines');
            $table->decimal('other_deductions', 12, 2)->nullable()->after('labour_welfare_fund');
            $table->string('salary_remarks', 255)->nullable()->after('other_deductions');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'monthly_gross',
                'basic_salary',
                'hra',
                'conveyance',
                'medical_allowance',
                'special_allowance',
                'overtime_amount',
                'lta',
                'arrears',
                'pf_deduction',
                'esi_deduction',
                'pt_deduction',
                'income_tax',
                'salary_advance',
                'fines',
                'labour_welfare_fund',
                'other_deductions',
                'salary_remarks',
            ]);
        });
    }
};

