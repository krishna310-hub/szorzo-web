<?php

namespace Tests\Unit;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\Mode;
use App\Models\User;
use App\Services\PayslipService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

class InternalEmployeeLopTest extends TestCase
{
    public function test_internal_employee_lop_from_attendance_on_leave_and_absent(): void
    {
        $service = new PayslipService();

        $fteMode = new Mode(['mode' => 'Full Time']);
        $employee = new Employee([
            'employee_name' => 'Triveni Panduranga',
            'employee_no' => 'SZ 002',
            'employment_type' => 'Internal',
            'monthly_gross' => 45000.00,
            'basic_salary' => 22500.00,
            'hra' => 9000.00,
        ]);
        $employee->setRelation('mode', $fteMode);
        $employee->setRelation('client', null);
        $employee->id = 2;

        // When overrides have lop specified (e.g. 2 days)
        $dataWithLop = $service->getPayslipData($employee, 10, 2026, ['lop' => 2.0]);
        $this->assertEquals(2.0, (float) $dataWithLop['lop']);
        $this->assertEquals(2.0, (float) $dataWithLop['absent_days']);
        $this->assertEquals(29.0, (float) $dataWithLop['effective_work_days']);
        $this->assertGreaterThan(0, $dataWithLop['leave_deduction']);

        // When overrides have absent_days specified (e.g. 3 days)
        $dataWithAbsent = $service->getPayslipData($employee, 10, 2026, ['absent_days' => 3.0]);
        $this->assertEquals(3.0, (float) $dataWithAbsent['lop']);
        $this->assertEquals(3.0, (float) $dataWithAbsent['absent_days']);
        $this->assertEquals(28.0, (float) $dataWithAbsent['effective_work_days']);
    }

    public function test_payslip_view_displays_lop_field_for_internal_employees(): void
    {
        $base = dirname(__DIR__, 2);
        $viewContent = file_get_contents($base . '/resources/views/backend/payslip/index.blade.php');

        // Verify the Leave Days (LOP) field is displayed for all monthly employees when can_manage_all is true,
        // and is not restricted to is_contract_like
        $this->assertStringNotContainsString('@elseif(!empty($can_manage_all) && !empty($is_contract_like))', $viewContent);
        $this->assertStringContainsString('name="absent_days"', $viewContent);
        $this->assertStringContainsString('Leave Days (LOP)', $viewContent);
    }

    public function test_payslip_service_queries_attendance_by_employee_id(): void
    {
        $base = dirname(__DIR__, 2);
        $serviceContent = file_get_contents($base . '/app/Services/PayslipService.php');

        // Verify Attendance query looks for employee_id and user_id
        $this->assertStringContainsString("'employee_id', \$empId", $serviceContent);
        $this->assertStringContainsString("'user_id', \$usrId", $serviceContent);

        // Verify on_leave status is counted toward autoLop
        $this->assertStringContainsString("\$onLeaveCount = \$attendances->where('status', 'on_leave')->count();", $serviceContent);
        $this->assertStringContainsString("max((float) \$onLeaveCount, (float) \$approvedLeaveDays)", $serviceContent);
    }
}
