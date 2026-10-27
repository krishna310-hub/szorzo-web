<?php

namespace Tests\Unit;

use App\Exports\AttendanceMonthlyExport;
use App\Exports\EmployeeExport;
use App\Models\Employee;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Tests\TestCase;

class BugsAndUpdatesTest extends TestCase
{
    public function test_candidate_filter_has_mode_contract_fte_and_reset_fix(): void
    {
        $baseDir = dirname(__DIR__, 2);
        $candidateIndexContent = file_get_contents($baseDir . '/resources/views/backend/candidates/index.blade.php');

        // Check Mode (Contract / FTE) dropdown exists in filter drawer
        $this->assertStringContainsString('name="mode_id"', $candidateIndexContent);
        $this->assertStringContainsString('Mode (Contract / FTE)', $candidateIndexContent);

        // Check reset button properly cleans form and reloads
        $this->assertStringContainsString('#candidate-filter-reset', $candidateIndexContent);
        $this->assertStringContainsString('filterJobRolesByClient()', $candidateIndexContent);
        $this->assertStringContainsString('interviewLevelFilter.removeActiveItems()', $candidateIndexContent);
        $this->assertStringContainsString('currentFilters = collectFilters()', $candidateIndexContent);

        // Check CandidateController query has mode_id filter
        $controllerContent = file_get_contents($baseDir . '/app/Http/Controllers/backend/CandidateController.php');
        $this->assertStringContainsString("->when(\$request->filled('mode_id'), fn (\$query) => \$query->where('mode_id', \$request->mode_id))", $controllerContent);
    }

    public function test_profiles_module_index_pagination_and_datatables(): void
    {
        $baseDir = dirname(__DIR__, 2);
        $profileControllerContent = file_get_contents($baseDir . '/app/Http/Controllers/backend/ProfileSourcedController.php');
        $profileIndexContent = file_get_contents($baseDir . '/resources/views/backend/profile-sourced/index.blade.php');

        // Verify filter does not pass $override = true which breaks Yajra's pagination/counting
        $this->assertStringNotContainsString('}, true)', $profileControllerContent);

        // Verify DataTables options include scrollX and pageLength options
        $this->assertStringContainsString('scrollX: true', $profileIndexContent);
        $this->assertStringContainsString('pageLength: 10', $profileIndexContent);
        $this->assertStringContainsString('lengthMenu: [10, 25, 50, 100]', $profileIndexContent);

        // Also verify ClientProfileController has safe filterColumn for assignee_name
        $clientProfileController = file_get_contents($baseDir . '/app/Http/Controllers/backend/ClientProfileController.php');
        $this->assertStringContainsString("filterColumn('assignee_name'", $clientProfileController);
    }

    public function test_dashboard_chart_yearwise_filter_enabled(): void
    {
        $baseDir = dirname(__DIR__, 2);
        $dashboardContent = file_get_contents($baseDir . '/resources/views/backend/index.blade.php');

        // Verify dashboardChartYear is not commented out
        $this->assertStringContainsString('id="dashboardChartYear"', $dashboardContent);
        $this->assertStringContainsString('Financial Year:', $dashboardContent);
        $this->assertStringContainsString("route('admin.dashboard.year-charts')", $dashboardContent);
        $this->assertStringNotContainsString('{{-- <div class="d-flex flex-wrap justify-content-end align-items-center gap-2 mb-3">', $dashboardContent);

        // Verify AdminController has yearCharts method
        $adminControllerContent = file_get_contents($baseDir . '/app/Http/Controllers/backend/AdminController.php');
        $this->assertStringContainsString('public function yearCharts(Request $request)', $adminControllerContent);
    }

    public function test_attendance_monthly_view_export(): void
    {
        $baseDir = dirname(__DIR__, 2);
        $monthlyBladeContent = file_get_contents($baseDir . '/resources/views/backend/attendance/monthly.blade.php');
        $routesContent = file_get_contents($baseDir . '/routes/web.php');
        $controllerContent = file_get_contents($baseDir . '/app/Http/Controllers/backend/AttendanceController.php');

        // Verify export button in monthly view
        $this->assertStringContainsString("route('admin.attendance.monthly.export'", $monthlyBladeContent);
        $this->assertStringContainsString('Export Excel', $monthlyBladeContent);

        // Verify route and controller method
        $this->assertStringContainsString("Route::get('/monthly/export/{format?}', 'monthlyExport')->name('monthly.export');", $routesContent);
        $this->assertStringContainsString('public function monthlyExport(Request $request', $controllerContent);

        // Verify AttendanceMonthlyExport class structure
        $export = new AttendanceMonthlyExport(collect(), range(1, 30), 4, 2026);
        $headings = $export->headings();
        $this->assertContains('Employee Name', $headings);
        $this->assertContains('Present (P)', $headings);
        $this->assertContains('Absent (A)', $headings);
        $this->assertContains('Total Working Days', $headings);
    }

    public function test_employee_export_contains_all_requested_columns(): void
    {
        $baseDir = dirname(__DIR__, 2);
        $routesContent = file_get_contents($baseDir . '/routes/web.php');
        $controllerContent = file_get_contents($baseDir . '/app/Http/Controllers/backend/EmployeeController.php');
        $employeeIndexContent = file_get_contents($baseDir . '/resources/views/backend/employees/index.blade.php');

        // Verify export route and controller method
        $this->assertStringContainsString("Route::get('/export', 'export')->name('export');", $routesContent);
        $this->assertStringContainsString('public function export(Request $request)', $controllerContent);

        // Verify export button in employees index view
        $this->assertStringContainsString("route('admin.employees.export')", $employeeIndexContent);
        $this->assertStringContainsString('id="employee-export-btn"', $employeeIndexContent);

        // Verify headings in EmployeeExport
        $export = new EmployeeExport(collect());
        $headings = $export->headings();

        // Requested: name, client, job role, doj, dor, tds or pf, contact or fTE, salary per month, ctc
        $this->assertContains('Employee Name', $headings);
        $this->assertContains('Client', $headings);
        $this->assertContains('Job Role', $headings);
        $this->assertContains('Date of Joining (DOJ)', $headings);
        $this->assertContains('Date of Relieving (DOR)', $headings);
        $this->assertContains('TDS (₹)', $headings);
        $this->assertContains('PF (₹)', $headings);
        $this->assertContains('TDS or PF Summary', $headings);
        $this->assertContains('Mode / Employment Type (Contract or FTE)', $headings);
        $this->assertContains('Salary Per Month (₹)', $headings);
        $this->assertContains('Annual CTC (₹)', $headings);

        // Test mapping of an employee
        $emp = new Employee([
            'employee_name' => 'Rahul Sharma',
            'employee_no' => 'SZ099',
            'designation' => 'Software Engineer',
            'date_of_joining' => '2026-02-01',
            'relieving_date' => null,
            'employment_type' => 'Contract',
            'income_tax' => 5000.00,
            'pf_deduction' => 1800.00,
            'monthly_gross' => 50000.00,
        ]);
        $emp->id = 99;

        $mapped = $export->map($emp);
        $this->assertSame(1, $mapped[0]); // S.No
        $this->assertSame('SZ099', $mapped[1]); // Employee ID
        $this->assertSame('Rahul Sharma', $mapped[2]); // Name
        $this->assertSame('Software Engineer', $mapped[4]); // Job Role
        $this->assertSame('01-02-2026', $mapped[5]); // DOJ
        $this->assertSame('Active / Ongoing', $mapped[6]); // DOR
        $this->assertSame('5000.00', $mapped[7]); // TDS
        $this->assertSame('1800.00', $mapped[8]); // PF
        $this->assertSame('Contract', $mapped[10]); // Mode / Employment Type
        $this->assertSame('50000.00', $mapped[11]); // Salary Per Month
        $this->assertSame('600000.00', $mapped[12]); // Annual CTC
    }

    public function test_investor_users_excluded_from_attendance(): void
    {
        // 1. Investor User model verification
        $userInvestor = new User([
            'name' => 'Test Investor',
            'email' => 'investor@test.com',
            'role_id' => 6,
            'is_active' => 1,
        ]);
        $this->assertTrue($userInvestor->isInvestor());

        // 2. User::scopeEligibleForAttendance SQL check
        $sql = User::eligibleForAttendance()->toSql();
        $this->assertStringContainsString('role_id', $sql);
        $this->assertStringContainsString('"role_id" != ?', $sql);

        // 3. User::scopeEmployedOn SQL check
        $employedSql = User::employedOn('2026-10-27')->toSql();
        $this->assertStringContainsString('role_id', $employedSql);
        $this->assertStringContainsString('"role_id" != ?', $employedSql);
    }
}
