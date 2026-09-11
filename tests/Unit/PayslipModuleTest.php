<?php

namespace Tests\Unit;

use App\Http\Controllers\backend\PayslipController;
use App\Models\Employee;
use App\Models\Mode;
use App\Models\Role;
use App\Models\User;
use App\Services\PayslipService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PayslipModuleTest extends TestCase
{
    public function test_user_is_super_admin_detection(): void
    {
        $superAdminRole = new Role(['access_level' => 'super_admin']);
        $adminRole = new Role(['access_level' => 'admin']);
        $hyphenSuperAdminRole = new Role(['access_level' => 'super-admin']);
        $recruiterRole = new Role(['access_level' => 'recruiter']);

        $superUser = new User();
        $superUser->setRelation('role', $superAdminRole);
        $this->assertTrue($superUser->isSuperAdmin());
        $this->assertTrue($superUser->isAdmin());

        $adminUser = new User();
        $adminUser->setRelation('role', $adminRole);
        $this->assertTrue($adminUser->isSuperAdmin());

        $hyphenUser = new User();
        $hyphenUser->setRelation('role', $hyphenSuperAdminRole);
        $this->assertTrue($hyphenUser->isSuperAdmin());

        $recruiterUser = new User();
        $recruiterUser->setRelation('role', $recruiterRole);
        $this->assertFalse($recruiterUser->isSuperAdmin());
        $this->assertFalse($recruiterUser->isAdmin());
    }

    public function test_employee_employment_modes(): void
    {
        $contractMode = new Mode(['mode' => 'Contract']);
        $c2hMode = new Mode(['mode' => 'C2H']);
        $fteMode = new Mode(['mode' => 'Full Time']);

        $emp1 = new Employee();
        $emp1->setRelation('mode', $contractMode);
        $this->assertSame('Contract', $emp1->employment_mode);
        $this->assertTrue($emp1->isContract());
        $this->assertFalse($emp1->isFTE());

        $emp2 = new Employee();
        $emp2->setRelation('mode', $c2hMode);
        $this->assertSame('C2H', $emp2->employment_mode);
        $this->assertTrue($emp2->isC2H());
        $this->assertFalse($emp2->isContract());

        $emp3 = new Employee();
        $emp3->setRelation('mode', $fteMode);
        $this->assertSame('FTE', $emp3->employment_mode);
        $this->assertTrue($emp3->isFTE());
        $this->assertFalse($emp3->isContract());

        // Default when no mode assigned
        $empDefault = new Employee();
        $this->assertSame('FTE', $empDefault->employment_mode);
        $this->assertTrue($empDefault->isFTE());
    }

    public function test_payslip_service_for_fte_monthly(): void
    {
        $service = new PayslipService();

        $fteMode = new Mode(['mode' => 'Full Time']);
        $employee = new Employee([
            'employee_name' => 'Alice Johnson',
            'employee_no' => 'SZ101',
            'monthly_gross' => 50000.00,
            'basic_salary' => 25000.00,
            'hra' => 10000.00,
            'pf_deduction' => 1800.00,
            'pt_deduction' => 200.00,
        ]);
        $employee->setRelation('mode', $fteMode);
        $employee->setRelation('client', null);
        $employee->id = 101;

        // Full month August 2026 (31 days)
        $data = $service->getPayslipData($employee, 8, 2026, ['lop' => 0]);

        $this->assertSame('employee', $data['target_type']);
        $this->assertSame('FTE', $data['mode']);
        $this->assertTrue($data['is_full_calendar_month']);
        $this->assertSame(31, $data['period_days']);
        $this->assertSame(31.0, (float) $data['effective_work_days']);
        $this->assertSame(25000.0, (float) $data['earnings']['basic']['actual']);
        $this->assertSame(10000.0, (float) $data['earnings']['hra']['actual']);
        $this->assertSame(1800.0, (float) $data['deductions']['pf']['actual']);
        $this->assertSame(200.0, (float) $data['deductions']['pt']['actual']);
        $this->assertGreaterThan(0, $data['net_pay']);
    }

    public function test_payslip_service_for_contract_date_to_date(): void
    {
        $service = new PayslipService();

        $contractMode = new Mode(['mode' => 'Contract']);
        $employee = new Employee([
            'employee_name' => 'Bob Smith',
            'employee_no' => 'SZ202',
            'monthly_gross' => 60000.00,
            'basic_salary' => 30000.00,
            'hra' => 12000.00,
            'pf_deduction' => 1800.00,
            'pt_deduction' => 200.00,
        ]);
        $employee->setRelation('mode', $contractMode);
        $employee->setRelation('client', null);
        $employee->id = 202;

        // Date-to-Date for 15 days in August (31 days in month)
        $data = $service->getPayslipData($employee, null, null, [
            'from_date' => '2026-08-01',
            'to_date' => '2026-08-15',
            'lop' => 0,
        ]);

        $this->assertSame('Contract', $data['mode']);
        $this->assertFalse($data['is_full_calendar_month']);
        $this->assertSame(15, $data['period_days']);
        $this->assertSame(15.0, (float) $data['effective_work_days']);
        $this->assertStringContainsString('01-Aug-2026', $data['period_title']);
        $this->assertStringContainsString('15-Aug-2026', $data['period_title']);

        // Prorated: 15 / 31 * 30,000 = 14,516
        $expectedBasic = round(30000.00 * (15 / 31));
        $this->assertSame($expectedBasic, (float) $data['earnings']['basic']['actual']);
    }

    public function test_payslip_service_for_c2h_with_lop(): void
    {
        $service = new PayslipService();

        $c2hMode = new Mode(['mode' => 'C2H']);
        $employee = new Employee([
            'employee_name' => 'Charlie Brown',
            'employee_no' => 'SZ303',
            'monthly_gross' => 40000.00,
            'basic_salary' => 20000.00,
            'hra' => 8000.00,
            'pf_deduction' => 1800.00,
            'pt_deduction' => 200.00,
        ]);
        $employee->setRelation('mode', $c2hMode);
        $employee->setRelation('client', null);
        $employee->id = 303;

        // Period 20 days with 2 days LOP -> effective work days = 18
        $data = $service->getPayslipData($employee, null, null, [
            'from_date' => '2026-09-01',
            'to_date' => '2026-09-20',
            'lop' => 2.0,
        ]);

        $this->assertSame('C2H', $data['mode']);
        $this->assertSame(20, $data['period_days']);
        $this->assertSame(2.0, (float) $data['lop']);
        $this->assertSame(18.0, (float) $data['effective_work_days']);

        // 18 days out of 30 days in September: ratio = 18 / 30 = 0.6
        $expectedBasic = round(20000.00 * (18 / 30));
        $this->assertSame($expectedBasic, (float) $data['earnings']['basic']['actual']);
    }

    public function test_inverted_dates_auto_corrected(): void
    {
        $service = new PayslipService();

        $emp = new Employee(['employee_name' => 'Test', 'monthly_gross' => 30000]);
        $emp->setRelation('mode', new Mode(['mode' => 'Contract']));
        $emp->setRelation('client', null);

        // Pass to_date earlier than from_date
        $data = $service->getPayslipData($emp, null, null, [
            'from_date' => '2026-08-20',
            'to_date' => '2026-08-01',
        ]);

        $this->assertSame('2026-08-01', $data['from_date']);
        $this->assertSame('2026-08-20', $data['to_date']);
        $this->assertSame(20, $data['period_days']);
    }

    public function test_payslip_service_for_system_user(): void
    {
        $service = new PayslipService();

        $role = new Role(['name' => 'HR Manager', 'access_level' => 'admin']);
        $user = new User([
            'name' => 'System Admin',
            'email' => 'admin@szorzo.com',
        ]);
        $user->setRelation('role', $role);
        $user->id = 1;

        $data = $service->getPayslipData($user, 7, 2026, ['lop' => 0]);

        $this->assertSame('user', $data['target_type']);
        $this->assertSame('System Admin', $data['name']);
        $this->assertSame('HR Manager', $data['designation']);
        $this->assertSame('SZ001', $data['employee_no']);
        $this->assertGreaterThan(0, $data['net_pay']);
    }

    public function test_helpers_format_amount_and_days(): void
    {
        $this->assertSame('00', PayslipService::formatAmount(0));
        $this->assertSame('00', PayslipService::formatAmount(null));
        $this->assertSame('5000', PayslipService::formatAmount(5000.0));
        $this->assertSame('5000.50', PayslipService::formatAmount(5000.50));

        $this->assertSame('0', PayslipService::formatDays(null));
        $this->assertSame('0', PayslipService::formatDays(0));
        $this->assertSame('15', PayslipService::formatDays(15.0));
        $this->assertSame('15.5', PayslipService::formatDays(15.5));
    }

    public function test_controller_filename_resolution(): void
    {
        $controller = new PayslipController(new PayslipService());
        $method = new ReflectionMethod($controller, 'resolveFilename');

        $monthlyData = [
            'is_full_calendar_month' => true,
            'employee_no' => 'SZ042',
            'month_name' => 'Aug',
            'year' => 2026,
            'from_date' => '2026-08-01',
            'to_date' => '2026-08-31',
        ];
        $this->assertSame('Payslip_SZ042_Aug_2026.pdf', $method->invoke($controller, $monthlyData));

        $rangeData = [
            'is_full_calendar_month' => false,
            'employee_no' => 'SZ042',
            'month_name' => 'Aug',
            'year' => 2026,
            'from_date' => '2026-08-01',
            'to_date' => '2026-08-15',
        ];
        $this->assertSame('Payslip_SZ042_20260801_to_20260815.pdf', $method->invoke($controller, $rangeData));
    }
}
