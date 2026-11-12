<?php

namespace Tests\Unit;

use App\Exports\AttendanceMonthlyExport;
use App\Exports\AttendanceReportExport;
use App\Http\Controllers\backend\PayslipController;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Mode;
use App\Models\User;
use App\Services\PayslipService;
use Carbon\CarbonImmutable;
use Tests\TestCase;

class AttendanceWorkHoursAndPayslipCompletionTest extends TestCase
{
    /**
     * Test 1: Verify fix for Triveni Panduranga's row from the user's screenshot.
     * Check In: 09:26:00, Check Out: 18:35:00, Lunch: 46 mins (2760s).
     * Total span = 9h 9m (32,940s). Net work = 8h 23m (30,180s).
     * Previously showed 04:20 (15,600s) because morning session was frozen when paused for lunch.
     * With our fix: correctly calculates 08:23 (8h 23m).
     */
    public function test_triveni_screenshot_work_hours_calculation_fixed(): void
    {
        $attendance = new Attendance([
            'attendance_date' => '2026-11-09',
            'check_in' => '09:26:00',
            'check_out' => '18:35:00',
            'timer_status' => Attendance::TIMER_COMPLETED,
            'total_work_seconds' => 15600, // 4 hours 20 minutes from morning session before lunch
            'breaks' => [
                [
                    'type' => 'lunch',
                    'start' => '13:46:00',
                    'end' => '14:32:00',
                    'seconds' => 2760,
                    'minutes' => 46,
                ],
            ],
        ]);

        // Break seconds: 46 minutes = 2760s
        $this->assertSame(2760, $attendance->lunch_seconds);
        $this->assertSame(2760, $attendance->combined_break_seconds);
        $this->assertSame('00:46', $attendance->formatted_lunch_time);

        // Expected work time: 32,940s gross - 2,760s lunch = 30,180s = 8 hours 23 minutes
        $this->assertSame(30180, $attendance->current_work_seconds);
        $this->assertSame('08:23', $attendance->formatted_work_time);
        $this->assertSame(503, $attendance->working_minutes);
    }

    /**
     * Test 2: Normal completed timer with accurate recorded work seconds is preserved.
     */
    public function test_normal_recorded_work_hours_preserved(): void
    {
        // Shenbagaseetharaman M on 10 Nov: 09:26 to 18:52, 22m lunch, recorded 32580 (09:03)
        $attendance = new Attendance([
            'attendance_date' => '2026-11-10',
            'check_in' => '09:26:00',
            'check_out' => '18:52:00',
            'timer_status' => Attendance::TIMER_COMPLETED,
            'total_work_seconds' => 32580, // 09:03:00 (within 1 min of 9h 26m - 22m)
            'breaks' => [
                ['type' => 'lunch', 'seconds' => 1320, 'minutes' => 22],
            ],
        ]);

        $this->assertSame(32580, $attendance->current_work_seconds);
        $this->assertSame('09:03', $attendance->formatted_work_time);
    }

    /**
     * Test 3: Manual check in and check out without timer calculates work hours correctly.
     */
    public function test_manual_check_in_and_check_out_calculates_work_hours(): void
    {
        $attendance = new Attendance([
            'attendance_date' => '2026-11-12',
            'check_in' => '09:30:00',
            'check_out' => '18:30:00',
            'timer_status' => Attendance::TIMER_COMPLETED,
            'total_work_seconds' => 0, // No timer recorded
            'breaks' => [
                ['type' => 'lunch', 'seconds' => 3600, 'minutes' => 60],
            ],
        ]);

        // 9 hours gross - 1 hour lunch = 8 hours net = 28,800 seconds
        $this->assertSame(28800, $attendance->current_work_seconds);
        $this->assertSame('08:00', $attendance->formatted_work_time);
        $this->assertSame(480, $attendance->working_minutes);
    }

    /**
     * Test 4: Attendance Report Export includes Employee Filtered Total Hours (Worked / Required) column.
     */
    public function test_attendance_report_export_includes_employee_filtered_total_hours_column(): void
    {
        $emp = new Employee(['id' => 10, 'employee_no' => 'SZ 010', 'employee_name' => 'Triveni P']);
        $user = new User(['id' => 10, 'name' => 'Triveni P', 'email' => 'triveni@example.com']);
        $user->setRelation('employee', $emp);

        $attendance = new Attendance([
            'attendance_date' => '2026-11-09',
            'check_in' => '09:00:00',
            'check_out' => '18:00:00',
            'user_id' => 10,
            'status' => 'present',
            'timer_status' => 'completed',
            'total_work_seconds' => 28800,
        ]);
        $attendance->setRelation('user', $user);
        $attendance->setRelation('employee', $emp);
        $attendance->setRelation('leaveRequest', null);

        $employeeTotals = [
            'u_10' => [
                'formatted_work_time' => '45:30',
                'formatted_work_human' => '45h 30m',
                'formatted_required_time' => '40:00',
                'formatted_required_human' => '40h',
                'worked_vs_required_time' => '45:30 / 40:00',
                'worked_vs_required_human' => '45h 30m / 40h',
            ],
        ];

        $export = new AttendanceReportExport(collect([$attendance]), $employeeTotals);
        $headings = $export->headings();
        $this->assertContains('Employee Filtered Total Hours (Worked / Required)', $headings);

        $row = $export->map($attendance);
        $this->assertContains('45:30 / 40:00', $row);
    }

    /**
     * Test 5: Monthly Export includes Total Work Hours (Worked / Required) column.
     */
    public function test_monthly_export_includes_total_work_hours_column(): void
    {
        $emp = new Employee(['id' => 5, 'employee_no' => 'SZ 005', 'employee_name' => 'Test Employee']);
        $att = new Attendance([
            'attendance_date' => CarbonImmutable::parse('2026-10-05'),
            'check_in' => '09:00:00',
            'check_out' => '17:00:00',
            'status' => 'present',
            'total_work_seconds' => 28800,
        ]);

        $user = new User(['id' => 5, 'name' => 'Test Employee', 'email' => 'test@example.com']);
        $user->setRelation('attendances', collect([$att]));
        $user->setRelation('employee', $emp);

        $export = new AttendanceMonthlyExport(collect([$user]), [1, 2, 3, 4, 5], 10, 2026);
        $headings = $export->headings();
        $this->assertContains('Total Work Hours (Worked / Required)', $headings);

        $row = $export->map($user);
        $this->assertContains('8h / 8h', $row);
    }

    /**
     * Test 6: Payslip is disallowed if employee joined in the current ongoing month.
     * "eg : if employee join this month dont generate payslip without complte this month okay fix this correctly"
     */
    public function test_payslip_disallows_employee_who_joined_in_current_month(): void
    {
        $controller = new PayslipController(new PayslipService());

        // Employee joined in the current ongoing month
        $newEmployee = new Employee();
        $newEmployee->setRawAttributes([
            'id' => 888,
            'employee_name' => 'New Joiner This Month',
            'date_of_joining' => now()->startOfMonth()->toDateString(), // Joined 1st of current month
            'relieving_date' => null,
        ]);

        $tenure = $controller->resolveTenure($newEmployee);

        // Employee joined this month: month is NOT completed!
        $this->assertFalse($tenure['has_completed_months']);
        $this->assertEmpty($tenure['available_years']);
        $this->assertEmpty($tenure['available_months_by_year']);
        $this->assertNull($tenure['default_month']);
        $this->assertNull($tenure['default_year']);

        // Current month and year is NOT within tenure
        $this->assertFalse($controller->isWithinTenure((int) now()->month, (int) now()->year, $tenure));
        // Previous month is NOT within tenure (they hadn't joined yet)
        $this->assertFalse($controller->isWithinTenure((int) now()->subMonth()->month, (int) now()->subMonth()->year, $tenure));
    }

    /**
     * Test 7: Payslip allows any completed past month without enforcing 28 to 30 days.
     * "if show every month completed to generate payslip dont fix 28 to 30 days mothly generate payslip"
     */
    public function test_payslip_allows_any_completed_month_without_28_days_restriction(): void
    {
        $controller = new PayslipController(new PayslipService());

        // Joined in previous month on 25th (worked partial days before that month completed)
        $lateJoiner = new Employee();
        $lateJoiner->setRawAttributes([
            'id' => 889,
            'employee_name' => 'Mid Month Joiner',
            'date_of_joining' => now()->subMonth()->startOfMonth()->addDays(24)->toDateString(),
            'relieving_date' => null,
        ]);

        $tenure = $controller->resolveTenure($lateJoiner);

        // Month is completed: payslip CAN be generated for that completed month!
        $this->assertTrue($tenure['has_completed_months']);
        $this->assertNotEmpty($tenure['available_years']);
        $prevMonth = now()->subMonth();
        $this->assertTrue($controller->isWithinTenure((int) $prevMonth->month, (int) $prevMonth->year, $tenure));

        // But current ongoing month is NOT within tenure
        $this->assertFalse($controller->isWithinTenure((int) now()->month, (int) now()->year, $tenure));
    }

    /**
     * Test 8: Payslip is allowed for employees who have completed at least one full working month.
     */
    public function test_payslip_allows_employee_with_completed_month(): void
    {
        $controller = new PayslipController(new PayslipService());

        // Joined 3 months ago: has completed months
        $veteran = new Employee();
        $veteran->setRawAttributes([
            'id' => 890,
            'employee_name' => 'Senior Developer',
            'date_of_joining' => now()->subMonths(3)->startOfMonth()->toDateString(),
            'relieving_date' => null,
        ]);

        $tenure = $controller->resolveTenure($veteran);

        $this->assertTrue($tenure['has_completed_months']);
        $this->assertNotEmpty($tenure['available_years']);
        $this->assertNotNull($tenure['default_month']);
        $this->assertNotNull($tenure['default_year']);

        // Previous completed month is within tenure
        $prevMonth = now()->subMonth();
        $this->assertTrue($controller->isWithinTenure((int) $prevMonth->month, (int) $prevMonth->year, $tenure));

        // Current ongoing month is NOT within tenure (must complete first)
        $this->assertFalse($controller->isWithinTenure((int) now()->month, (int) now()->year, $tenure));
    }

    /**
     * Test 9: PayslipService getEmptyPayslipData returns ineligible structure.
     */
    public function test_payslip_service_empty_data_for_ineligible_employee(): void
    {
        $service = new PayslipService();
        $emp = new Employee([
            'id' => 101,
            'employee_name' => 'Ineligible Worker',
            'employee_no' => 'SZ 101',
            'employment_mode' => 'FTE',
        ]);

        $emptyData = $service->getEmptyPayslipData($emp, 'Month incomplete');

        $this->assertFalse($emptyData['is_eligible']);
        $this->assertSame(0.0, $emptyData['net_pay']);
        $this->assertSame(0.0, $emptyData['payable_salary']);
        $this->assertSame('Month incomplete', $emptyData['ineligible_reason']);
    }

    /**
     * Test 10: Blade view templates contain the required markup for the features.
     */
    public function test_blade_view_templates_markup(): void
    {
        // 1. Detailed Attendance Report view has Employees Total Hours Summary and Emp Total column
        $reportView = file_get_contents(resource_path('views/backend/attendance/report.blade.php'));
        $this->assertStringContainsString('Employees Total Hours Summary', $reportView);
        $this->assertStringContainsString('Emp Total (Filtered)', $reportView);
        $this->assertStringContainsString('Filtered Dates Total:', $reportView);
        $this->assertStringContainsString('Total Work Hours (Worked / Required)', $reportView);

        // 2. Monthly Attendance view has Total Hours (Worked / Req) column
        $monthlyView = file_get_contents(resource_path('views/backend/attendance/monthly.blade.php'));
        $this->assertStringContainsString('<th>Total Hours (Worked / Req)</th>', $monthlyView);
        $this->assertStringContainsString('formatSecondsHuman', $monthlyView);

        // 3. Payslip index view has Locked and Ineligible states
        $payslipView = file_get_contents(resource_path('views/backend/payslip/index.blade.php'));
        $this->assertStringContainsString('Payslip Generation Ineligible', $payslipView);
        $this->assertStringContainsString('Payslip Locked', $payslipView);
        $this->assertStringContainsString('Payslip Not Available Yet', $payslipView);
    }

    /**
     * Test 11: Employee with unupdated/unconfigured salary shows only 0 on payslip without error.
     * "- if the employee salary not update to show only 0 on payslip okay make without error"
     */
    public function test_employee_without_updated_salary_shows_zero_amounts_cleanly(): void
    {
        $service = new PayslipService();

        $unconfiguredEmp = new Employee([
            'id' => 777,
            'employee_name' => 'Pending Salary Employee',
            'employee_no' => 'SZ 777',
            'employment_mode' => 'FTE',
            'monthly_gross' => null,
            'basic_salary' => null,
            'hra' => null,
        ]);
        $unconfiguredEmp->setRelation('mode', new Mode(['mode' => 'Full Time']));
        $unconfiguredEmp->setRelation('client', null);

        $data = $service->getPayslipData($unconfiguredEmp, 10, 2026);

        $this->assertTrue($data['is_eligible']);
        $this->assertSame(0.0, (float) $data['net_pay']);
        $this->assertSame(0.0, (float) $data['payable_salary']);
        $this->assertSame(0.0, (float) $data['total_earnings_full']);
        $this->assertSame(0.0, (float) $data['total_earnings_actual']);
        $this->assertSame(0.0, (float) $data['total_deductions_actual']);
        $this->assertSame(0.0, (float) $data['earnings']['basic']['actual']);
        $this->assertSame(0.0, (float) $data['earnings']['hra']['actual']);
        $this->assertSame(0.0, (float) $data['deductions']['pf']['actual']);
        $this->assertStringContainsString('Salary details not updated', $data['remarks']);
    }

    /**
     * Test 12: Leave dates (status = on_leave or with leave_request_id) do not calculate required hours.
     * "and dont calculate leave date give correctly check once calculation"
     */
    public function test_leave_dates_do_not_calculate_required_hours(): void
    {
        // 1 present day (8h), 1 half day (4h), 1 leave day (0h), 1 week off (0h)
        $emp = new Employee(['id' => 20, 'employee_no' => 'SZ 020', 'employee_name' => 'Leave Staff', 'date_of_joining' => '2026-11-01']);
        $user = new User(['id' => 20, 'name' => 'Leave Staff', 'email' => 'leave@example.com']);
        $user->setRelation('employee', $emp);

        $attPresent = new Attendance([
            'attendance_date' => CarbonImmutable::parse('2026-11-02'),
            'status' => 'present',
            'check_in' => '09:00:00',
            'check_out' => '17:00:00',
            'total_work_seconds' => 28800,
        ]);
        $attLeave = new Attendance([
            'attendance_date' => CarbonImmutable::parse('2026-11-03'),
            'status' => 'on_leave',
            'leave_request_id' => 99,
            'total_work_seconds' => 0,
        ]);
        $attHalfDay = new Attendance([
            'attendance_date' => CarbonImmutable::parse('2026-11-04'),
            'status' => 'half_day',
            'check_in' => '09:00:00',
            'check_out' => '13:00:00',
            'total_work_seconds' => 14400,
        ]);
        $attWeekOff = new Attendance([
            'attendance_date' => CarbonImmutable::parse('2026-11-08'),
            'status' => 'week_off',
            'total_work_seconds' => 0,
        ]);

        $user->setRelation('attendances', collect([$attPresent, $attLeave, $attHalfDay, $attWeekOff]));
        $user->setRelation('employee', $emp);

        $export = new AttendanceMonthlyExport(collect([$user]), [2, 3, 4, 8], 11, 2026);
        $row = $export->map($user);

        // Worked: 8h + 4h = 12h
        // Required: 8h (present) + 4h (half day) + 0h (leave) + 0h (week off) = 12h
        // Leave date is NOT calculated!
        $this->assertContains('12h / 12h', $row);
    }

    /**
     * Test 13: Dates before the employee's first attendance date do not calculate required hours.
     * "attendance only get start first attendance date to calculate hours"
     */
    public function test_dates_before_first_attendance_date_do_not_calculate_required_hours(): void
    {
        // Employee joined / first attended on 2026-11-09
        $emp = new Employee(['id' => 30, 'employee_no' => 'SZ 030', 'employee_name' => 'Late Joiner', 'date_of_joining' => '2026-11-09']);
        $user = new User(['id' => 30, 'name' => 'Late Joiner', 'email' => 'late@example.com']);
        $user->setRelation('employee', $emp);

        // Pre-joining uncommenced date marked absent/unmarked before first attendance date
        $attBefore = new Attendance([
            'attendance_date' => CarbonImmutable::parse('2026-11-02'),
            'status' => 'absent',
            'total_work_seconds' => 0,
        ]);
        // First attendance date: Nov 09 (Present, 8h)
        $attFirst = new Attendance([
            'attendance_date' => CarbonImmutable::parse('2026-11-09'),
            'status' => 'present',
            'check_in' => '09:00:00',
            'check_out' => '17:00:00',
            'total_work_seconds' => 28800,
        ]);
        // Nov 10 (Present, 8h)
        $attSecond = new Attendance([
            'attendance_date' => CarbonImmutable::parse('2026-11-10'),
            'status' => 'present',
            'check_in' => '09:00:00',
            'check_out' => '17:00:00',
            'total_work_seconds' => 28800,
        ]);

        $user->setRelation('attendances', collect([$attBefore, $attFirst, $attSecond]));
        $user->setRelation('employee', $emp);

        $export = new AttendanceMonthlyExport(collect([$user]), [2, 9, 10], 11, 2026);
        $row = $export->map($user);

        // Worked: 8h + 8h = 16h
        // Required: 0h (Nov 2 before first attendance) + 8h (Nov 9) + 8h (Nov 10) = 16h
        // Pre-start date is NOT calculated!
        $this->assertContains('16h / 16h', $row);
    }
}
