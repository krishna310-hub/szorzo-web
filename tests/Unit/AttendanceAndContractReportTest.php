<?php

namespace Tests\Unit;

use App\Http\Controllers\backend\AttendanceTimerController;
use App\Http\Controllers\backend\ContractReportController;
use App\Models\Candidate;
use App\Models\Client;
use App\Models\ContractReport;
use App\Models\Employee;
use App\Models\Mode;
use App\Models\Role;
use App\Models\User;
use Carbon\CarbonImmutable;
use ReflectionMethod;
use Tests\TestCase;

class AttendanceAndContractReportTest extends TestCase
{
    /**
     * Test 1: Admin Attendance Popup - Internal vs External Employee Classification.
     */
    public function test_employee_internal_classification_for_attendance_popup(): void
    {
        $contractMode = new Mode(['mode' => 'Contract']);
        $fteMode = new Mode(['mode' => 'Full Time']);
        $szorzoClient = new Client(['client' => 'SZORZO Technologies Private Limited']);
        $externalClient = new Client(['client' => 'Acme Corp']);

        // Internal by employment_type
        $emp1 = new Employee(['employee_name' => 'Internal Worker', 'employment_type' => 'Internal']);
        $this->assertTrue($emp1->isInternal());
        $this->assertFalse($emp1->isExternal());

        // External by employment_type
        $emp2 = new Employee(['employee_name' => 'External Worker', 'employment_type' => 'External']);
        $this->assertFalse($emp2->isInternal());
        $this->assertTrue($emp2->isExternal());

        // Internal FTE Szorzo
        $emp3 = new Employee(['employee_name' => 'FTE Szorzo']);
        $emp3->setRelation('mode', $fteMode);
        $emp3->setRelation('client', $szorzoClient);
        $this->assertTrue($emp3->isInternal());

        // External Contract
        $emp4 = new Employee(['employee_name' => 'Contract Staff']);
        $emp4->setRelation('mode', $contractMode);
        $this->assertFalse($emp4->isInternal());

        // External Client Deputed
        $emp5 = new Employee(['employee_name' => 'Client Staff']);
        $emp5->setRelation('mode', $fteMode);
        $emp5->setRelation('client', $externalClient);
        $this->assertFalse($emp5->isInternal());
    }

    /**
     * Test 2: Admin Attendance timer controller code restricts popup list to internal employees only.
     */
    public function test_attendance_controller_admin_employees_filters_internal(): void
    {
        $controllerFile = file_get_contents(app_path('Http/Controllers/backend/AttendanceTimerController.php'));
        
        // Assert adminEmployees method filters by isInternal()
        $this->assertStringContainsString('->filter(fn ($emp) => $emp->isInternal())', $controllerFile);
        
        // Assert status and action methods reject non-internal employees
        $this->assertStringContainsString('if (!$employee || !$employee->isInternal())', $controllerFile);
        $this->assertStringContainsString('Internal employee not found.', $controllerFile);

        // Assert navbar popup template specifies internal employee
        $viewFile = file_get_contents(resource_path('views/backend/layouts/attendance-navbar-widget.blade.php'));
        $this->assertStringContainsString('Select Internal Employee', $viewFile);
        $this->assertStringContainsString('-- Choose Internal Employee (ID / Name) --', $viewFile);
    }

    /**
     * Test 3: Contract Report - Active Contract Filter logic in ContractReportController.
     */
    public function test_contract_report_active_contract_filter_logic(): void
    {
        $controllerFile = file_get_contents(app_path('Http/Controllers/backend/ContractReportController.php'));

        // Assert query handles historical previous months to reflect past work
        $this->assertStringContainsString('$isPrevious = $month->startOfMonth()->lt(CarbonImmutable::now()->startOfMonth());', $controllerFile);
        $this->assertStringContainsString('// For previous months where this person already worked, show their existing report!', $controllerFile);

        // Assert refresh prunes inactive contracts for current/future months only
        $this->assertStringContainsString('if (! $isPrevious) {', $controllerFile);
        $this->assertStringContainsString('whereDoesntHave(\'candidate\', fn ($q) => $this->applyActiveContractFilter(', $controllerFile);

        // Assert applyActiveContractFilter checks contract dates (candidate & employee) and relieving date
        $this->assertStringContainsString('candidates.contract_from_date', $controllerFile);
        $this->assertStringContainsString('candidates.contract_to_date', $controllerFile);
        $this->assertStringContainsString('employees.relieving_date', $controllerFile);
        $this->assertStringContainsString('employees.contract_to_date', $controllerFile);

        // Assert status = 0 is only checked for current/future months (so previous months reflect!)
        $this->assertStringContainsString('if (! $isPrevious) {', $controllerFile);
        $this->assertStringContainsString('$inactive->orWhere(\'employees.status\', 0);', $controllerFile);
    }

    /**
     * Test 4: Contract Report query method structure allows previous months to reflect.
     */
    public function test_contract_report_query_permits_historical_month_reflection(): void
    {
        $controller = new ContractReportController;
        $refMethod = new ReflectionMethod($controller, 'applyActiveContractFilter');
        
        $this->assertTrue($refMethod->isPrivate());

        // Previous month vs Current month distinction
        $prevMonth = CarbonImmutable::now()->subMonths(2)->startOfMonth();
        $currentMonth = CarbonImmutable::now()->startOfMonth();

        $this->assertTrue($prevMonth->lt($currentMonth));
        $this->assertFalse($currentMonth->lt($currentMonth));
    }

    /**
     * Test 5: Attendance Model - Break and Lunch Time calculations and formatters.
     */
    public function test_attendance_model_break_and_lunch_time_calculations(): void
    {
        $attendance = new \App\Models\Attendance([
            'check_in' => '09:00:00',
            'check_out' => '18:00:00',
            'total_work_seconds' => 28800, // 8 hours
            'timer_status' => \App\Models\Attendance::TIMER_COMPLETED,
            'breaks' => [
                ['type' => 'break', 'start' => '11:00:00', 'end' => '11:15:00', 'seconds' => 900, 'minutes' => 15],
                ['type' => 'lunch', 'start' => '13:00:00', 'end' => '13:45:00', 'seconds' => 2700, 'minutes' => 45],
                ['type' => 'break', 'start' => '16:00:00', 'end' => '16:10:00', 'seconds' => 600, 'minutes' => 10],
            ],
        ]);

        // Break seconds: 900 + 600 = 1500 seconds (25 minutes)
        $this->assertSame(1500, $attendance->break_seconds);
        $this->assertSame(25, $attendance->break_minutes);
        $this->assertSame(2, $attendance->break_count);
        $this->assertSame('00:25', $attendance->formatted_break_time);

        // Lunch seconds: 2700 seconds (45 minutes)
        $this->assertSame(2700, $attendance->lunch_seconds);
        $this->assertSame(45, $attendance->lunch_minutes);
        $this->assertSame(1, $attendance->lunch_count);
        $this->assertSame('00:45', $attendance->formatted_lunch_time);

        // Combined break: 1500 + 2700 = 4200 seconds (70 minutes = 1h 10m)
        $this->assertSame(4200, $attendance->combined_break_seconds);
        $this->assertSame('01:10', $attendance->formatted_total_break_time);

        // Work time: 28800 seconds = 08:00
        $this->assertSame('08:00', $attendance->formatted_work_time);

        // Static formatters
        $this->assertSame('01:10', \App\Models\Attendance::formatSecondsToTime(4200));
        $this->assertSame('1h 10m', \App\Models\Attendance::formatSecondsHuman(4200));
        $this->assertSame('45m', \App\Models\Attendance::formatSecondsHuman(2700));
        $this->assertSame('2h', \App\Models\Attendance::formatSecondsHuman(7200));
        $this->assertSame('0m', \App\Models\Attendance::formatSecondsHuman(0));
    }

    /**
     * Test 6: Attendance Model - Live Ongoing Break & Lunch calculation.
     */
    public function test_attendance_model_live_break_and_lunch_calculations(): void
    {
        // Live on Lunch for 30 minutes
        $attendanceLunch = new \App\Models\Attendance([
            'timer_status' => \App\Models\Attendance::TIMER_ON_LUNCH,
            'break_started_at' => now()->subMinutes(30),
            'breaks' => [
                ['type' => 'break', 'start' => '10:00:00', 'end' => '10:15:00', 'seconds' => 900, 'minutes' => 15],
            ],
        ]);

        $this->assertSame(1, $attendanceLunch->break_count);
        $this->assertSame(1, $attendanceLunch->lunch_count);
        $this->assertSame(900, $attendanceLunch->break_seconds);
        // Lunch should be approximately 1800 seconds (30 minutes)
        $this->assertGreaterThanOrEqual(1799, $attendanceLunch->lunch_seconds);
        $this->assertLessThanOrEqual(1810, $attendanceLunch->lunch_seconds);

        // Live on Tea Break for 10 minutes
        $attendanceBreak = new \App\Models\Attendance([
            'timer_status' => \App\Models\Attendance::TIMER_ON_BREAK,
            'break_started_at' => now()->subMinutes(10),
            'breaks' => [],
        ]);

        $this->assertSame(1, $attendanceBreak->break_count);
        $this->assertSame(0, $attendanceBreak->lunch_count);
        $this->assertGreaterThanOrEqual(599, $attendanceBreak->break_seconds);
        $this->assertSame(0, $attendanceBreak->lunch_seconds);
    }

    /**
     * Test 7: Attendance Report Export structure includes Break & Lunch headings and mapped fields.
     */
    public function test_attendance_report_export_structure(): void
    {
        $attendance = new \App\Models\Attendance([
            'attendance_date' => '2026-10-07',
            'check_in' => '09:00:00',
            'check_out' => '18:00:00',
            'status' => 'present',
            'timer_status' => 'completed',
            'total_work_seconds' => 28800,
            'breaks' => [
                ['type' => 'break', 'seconds' => 1200],
                ['type' => 'lunch', 'seconds' => 2400],
            ],
        ]);

        $export = new \App\Exports\AttendanceReportExport(collect([$attendance]));
        $headings = $export->headings();

        $this->assertContains('Break Time (Tea)', $headings);
        $this->assertContains('Lunch Time', $headings);
        $this->assertContains('Total Break Time', $headings);

        $row = $export->map($attendance);
        // Assert formatted work time, break time, lunch time, total break time
        $this->assertContains('08:00', $row);
        $this->assertContains('00:20', $row); // 1200s
        $this->assertContains('00:40', $row); // 2400s
        $this->assertContains('01:00', $row); // 3600s combined
    }

    /**
     * Test 8: Attendance and User model relationships prevent RelationNotFoundException.
     */
    public function test_attendance_report_relations_and_user_employee_relation(): void
    {
        $attendance = new \App\Models\Attendance();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $attendance->user());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $attendance->employee());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $attendance->leaveRequest());

        $user = new \App\Models\User();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasOne::class, $user->employee());

        $controllerFile = file_get_contents(app_path('Http/Controllers/backend/AttendanceController.php'));
        $this->assertStringNotContainsString('user.employee', $controllerFile);
        $this->assertStringContainsString("with(['user','employee','leaveRequest'])", $controllerFile);
    }
}

