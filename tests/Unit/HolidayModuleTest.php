<?php

namespace Tests\Unit;

use App\Exports\AttendanceMonthlyExport;
use App\Http\Controllers\backend\AttendanceController;
use App\Http\Controllers\backend\HolidayController;
use App\Http\Controllers\backend\LeaveController;
use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\Role;
use App\Models\User;
use App\Policies\HolidayPolicy;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

class HolidayModuleTest extends TestCase
{
    public function test_holiday_constants_and_fillables(): void
    {
        $this->assertContains('Company', Holiday::TYPES);
        $this->assertContains('National', Holiday::TYPES);
        $this->assertContains('Festival', Holiday::TYPES);
        $this->assertContains('Public', Holiday::TYPES);
        $this->assertContains('Restricted', Holiday::TYPES);

        $holiday = new Holiday();
        $fillables = $holiday->getFillable();
        $this->assertContains('title', $fillables);
        $this->assertContains('date', $fillables);
        $this->assertContains('year', $fillables);
        $this->assertContains('type', $fillables);
        $this->assertContains('description', $fillables);
        $this->assertContains('created_by', $fillables);
    }

    public function test_holiday_attributes_and_helpers(): void
    {
        $futureDate = now()->addDays(5)->format('Y-m-d');
        $h1 = new Holiday([
            'title' => 'New Year Day',
            'date' => $futureDate,
            'type' => 'National',
        ]);

        $this->assertTrue($h1->is_upcoming);
        $this->assertFalse($h1->is_past);
        $this->assertEquals(Carbon::parse($futureDate)->format('d M Y'), $h1->formatted_date);
        $this->assertEquals(Carbon::parse($futureDate)->format('l'), $h1->day_name);

        // Past holiday
        $pastDate = now()->subDays(10)->format('Y-m-d');
        $h2 = new Holiday([
            'title' => 'Past Event',
            'date' => $pastDate,
            'type' => 'Company',
        ]);
        $this->assertTrue($h2->is_past);
        $this->assertFalse($h2->is_upcoming);

        // Today holiday
        $todayDate = now()->format('Y-m-d');
        $h3 = new Holiday([
            'title' => 'Today Event',
            'date' => $todayDate,
        ]);
        $this->assertTrue($h3->is_today);
    }

    public function test_holiday_policy_enforcement(): void
    {
        $policy = new HolidayPolicy();

        // 1. Super Admin role
        $adminRole = new Role(['access_level' => 'super_admin', 'name' => 'Super Admin']);
        $adminUser = new User(['role_id' => 1, 'is_active' => 1]);
        $adminUser->setRelation('role', $adminRole);

        $this->assertTrue($policy->before($adminUser));
        $this->assertTrue($policy->read($adminUser));
        $this->assertTrue($policy->create($adminUser));
        $this->assertTrue($policy->edit($adminUser));
        $this->assertTrue($policy->delete($adminUser));

        // 2. Regular employee without admin role
        $staffRole = new Role(['access_level' => 'staff', 'name' => 'Recruiter']);
        $staffUser = new User(['role_id' => 2, 'is_active' => 1]);
        $staffUser->setRelation('role', $staffRole);

        $this->assertNull($policy->before($staffUser));
        $this->assertFalse($policy->read($staffUser));
        $this->assertFalse($policy->create($staffUser));
        $this->assertFalse($policy->edit($staffUser));
        $this->assertFalse($policy->delete($staffUser));
    }

    public function test_holiday_controller_methods_exist(): void
    {
        $controller = new HolidayController();
        $this->assertTrue(method_exists($controller, 'index'));
        $this->assertTrue(method_exists($controller, 'store'));
        $this->assertTrue(method_exists($controller, 'update'));
        $this->assertTrue(method_exists($controller, 'destroy'));
        $this->assertTrue(method_exists($controller, 'listJson'));
    }

    public function test_monthly_export_with_holidays_map(): void
    {
        $user = new User([
            'name' => 'Test Employee',
            'email' => 'emp@example.com',
            'is_active' => 1,
        ]);
        $user->setRelation('attendances', new Collection());
        $user->setRelation('employee', null);

        $days = [1, 2, 3];
        $month = 1;
        $year = 2026;

        // Day 2 is a holiday in holidaysMap
        $holidaysMap = [
            2 => (object) ['title' => 'Holiday Day', 'type' => 'National'],
        ];

        $export = new AttendanceMonthlyExport(new Collection([$user]), $days, $month, $year, $holidaysMap);
        $mapped = $export->map($user);

        // Columns: 0: s.no, 1: emp id, 2: name, 3: email, 4: day1, 5: day2, 6: day3, 7: P, 8: A, 9: HD, 10: L, 11: H, 12: WO, 13: UM
        $this->assertEquals('Test Employee', $mapped[2]);
        $this->assertEquals('—', $mapped[4]); // day 1 unmarked
        $this->assertEquals('H', $mapped[5]); // day 2 holiday
        $this->assertEquals('—', $mapped[6]); // day 3 unmarked
        $this->assertEquals(1, $mapped[11]);  // 1 holiday total
        $this->assertEquals(2, $mapped[13]);  // 2 unmarked days
    }

    public function test_views_contain_holiday_markup_and_buttons(): void
    {
        $base = dirname(__DIR__, 2);

        // 1. Sidebar contains Holiday menu item protected with policy
        $sidebarView = file_get_contents($base . '/resources/views/backend/layouts/sidebar.blade.php');
        $this->assertStringContainsString("can('read', \\App\Models\\Holiday::class)", $sidebarView);
        $this->assertStringContainsString("route('admin.holidays.index')", $sidebarView);

        // 2. Leave Request view contains Holidays button and modal
        $leaveIndexView = file_get_contents($base . '/resources/views/backend/leaves/index.blade.php');
        $this->assertStringContainsString('data-bs-target="#employeeHolidaysModal"', $leaveIndexView);
        $this->assertStringContainsString('Holidays', $leaveIndexView);
        $this->assertStringContainsString("backend.leaves._holidays_modal", $leaveIndexView);

        // 3. Holidays modal view exists and has year selector and search
        $modalView = file_get_contents($base . '/resources/views/backend/leaves/_holidays_modal.blade.php');
        $this->assertStringContainsString('modalHolidaysYearSelect', $modalView);
        $this->assertStringContainsString('modalHolidaysSearchInput', $modalView);
        $this->assertStringContainsString('modalHolidaysContainer', $modalView);

        // 4. Daily attendance view includes holiday alert banner and auto-mark
        $attendanceIndexView = file_get_contents($base . '/resources/views/backend/attendance/index.blade.php');
        $this->assertStringContainsString('Holiday Notice', $attendanceIndexView);
        $this->assertStringContainsString('holiday', $attendanceIndexView);

        // 5. Monthly attendance view includes holidays map and holiday table cells
        $monthlyView = file_get_contents($base . '/resources/views/backend/attendance/monthly.blade.php');
        $this->assertStringContainsString('holidaysMap', $monthlyView);
        $this->assertStringContainsString('s-{{ $status }}', $monthlyView);

        // 6. Holiday CRUD view exists and has all modals
        $holidayIndexView = file_get_contents($base . '/resources/views/backend/holidays/index.blade.php');
        $this->assertStringContainsString('Holiday Management', $holidayIndexView);
        $this->assertStringContainsString('addHolidayModal', $holidayIndexView);
        $this->assertStringContainsString('editHolidayModal', $holidayIndexView);
        $this->assertStringContainsString('deleteHolidayModal', $holidayIndexView);

        // 7. Navbar timer widget contains holiday detection
        $navbarView = file_get_contents($base . '/resources/views/backend/layouts/attendance-navbar-widget.blade.php');
        $this->assertStringContainsString('todayHoliday', $navbarView);
        $this->assertStringContainsString('Holiday:', $navbarView);
    }

    public function test_auth_service_provider_and_migration_integrity(): void
    {
        $base = dirname(__DIR__, 2);

        // AuthServiceProvider policy registration
        $authProvider = file_get_contents($base . '/app/Providers/AuthServiceProvider.php');
        $this->assertStringContainsString('\App\Models\Holiday::class => \App\Policies\HolidayPolicy::class', $authProvider);

        // Migration file exists
        $migrations = glob($base . '/database/migrations/*_create_holidays_table_and_permissions.php');
        $this->assertNotEmpty($migrations, 'Holiday migration must exist');
        $migrationContent = file_get_contents($migrations[0]);
        $this->assertStringContainsString('holidays', $migrationContent);
        $this->assertStringContainsString('permission_roles', $migrationContent);
        $this->assertStringContainsString('holiday', $migrationContent);

        // Holiday model has sync methods
        $this->assertTrue(method_exists(Holiday::class, 'syncAttendanceForDate'));
        $this->assertTrue(method_exists(Holiday::class, 'removeHolidayAttendanceForDate'));
    }
}
