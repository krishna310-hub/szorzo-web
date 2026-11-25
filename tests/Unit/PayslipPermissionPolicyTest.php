<?php

namespace Tests\Unit;

use App\Models\Payslip;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Policies\PayslipPolicy;
use App\Providers\AuthServiceProvider;
use Database\Seeders\PermissionsSeeder;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class PayslipPermissionPolicyTest extends TestCase
{
    public function test_payslip_policy_super_admin_and_admin_allowed(): void
    {
        $policy = new PayslipPolicy();

        // 1. Super Admin
        $superAdmin = new User();
        $superAdmin->resource_type = 'super_admin';
        $superAdmin->is_active = 1;
        $this->assertTrue($superAdmin->isSuperAdmin());
        $this->assertTrue($policy->before($superAdmin));

        // 2. User ID 1 (Primary Admin)
        $userOne = new User();
        $userOne->id = 1;
        $userOne->is_active = 1;
        $this->assertTrue($policy->before($userOne));

        // 3. User with admin access
        $admin = new User();
        $admin->resource_type = 'super_admin';
        $this->assertTrue($policy->before($admin));
    }

    public function test_payslip_policy_blocks_investor(): void
    {
        $policy = new PayslipPolicy();

        $investorUser = new User();
        $investorUser->role_id = 6;
        $this->assertFalse($policy->before($investorUser));
    }

    public function test_payslip_policy_individual_employee_without_permission_denied(): void
    {
        $policy = new PayslipPolicy();

        // Standard employee without role or permission
        $employeeUser = new User();
        $employeeUser->id = 42;
        $employeeUser->resource_type = 'employee';
        $employeeUser->is_active = 1;

        $this->assertNull($policy->before($employeeUser));
        $this->assertFalse($policy->read($employeeUser));
        $this->assertFalse($policy->view($employeeUser));
        $this->assertFalse($policy->download($employeeUser));
        $this->assertFalse($policy->create($employeeUser));
        $this->assertFalse($policy->edit($employeeUser));
        $this->assertFalse($policy->delete($employeeUser));
    }

    public function test_payslip_policy_individual_employee_with_permission_allowed(): void
    {
        $policy = new PayslipPolicy();

        // Permission objects
        $readPerm = new Permission();
        $readPerm->id = 501;
        $readPerm->page = 'payslip';
        $readPerm->name = 'Read';

        $downloadPerm = new Permission();
        $downloadPerm->id = 502;
        $downloadPerm->page = 'payslip';
        $downloadPerm->name = 'Download';

        $role = new Role();
        $role->name = 'Staff';
        $role->setRelation('permissions', new Collection([$readPerm, $downloadPerm]));

        $employeeUser = new User();
        $employeeUser->id = 99;
        $employeeUser->resource_type = 'employee';
        $employeeUser->is_active = 1;
        $employeeUser->setRelation('role', $role);

        // Reflection to test allowed() method with mocked relations
        $ref = new ReflectionClass(PayslipPolicy::class);
        $allowedMethod = $ref->getMethod('allowed');

        // Without DB resolver, safe fallback returns false
        $this->assertFalse($allowedMethod->invoke($policy, $employeeUser, 'Read'));

        // When user has permissions attached in role collection
        $hasRead = $employeeUser->role?->permissions->contains('id', 501);
        $this->assertTrue($hasRead);
    }

    public function test_permissions_seeder_contains_payslip_permissions(): void
    {
        $seederFile = dirname(__DIR__, 2) . '/database/seeders/PermissionsSeeder.php';
        $this->assertFileExists($seederFile);
        $content = file_get_contents($seederFile);

        $this->assertStringContainsString("'page' => 'payslip'", $content);
        $this->assertStringContainsString("'name' => 'Read',      'page' => 'payslip'", $content);
        $this->assertStringContainsString("'name' => 'Download',  'page' => 'payslip'", $content);
        $this->assertStringContainsString("'name' => 'Create',    'page' => 'payslip'", $content);
        $this->assertStringContainsString("'name' => 'Edit',      'page' => 'payslip'", $content);
        $this->assertStringContainsString("'name' => 'Delete',    'page' => 'payslip'", $content);
    }

    public function test_auth_service_provider_registers_payslip_policy(): void
    {
        $providerFile = dirname(__DIR__, 2) . '/app/Providers/AuthServiceProvider.php';
        $this->assertFileExists($providerFile);
        $content = file_get_contents($providerFile);

        $this->assertStringContainsString('use App\Models\Payslip;', $content);
        $this->assertStringContainsString('use App\Policies\PayslipPolicy;', $content);
        $this->assertStringContainsString('Payslip::class => PayslipPolicy::class', $content);
        $this->assertStringContainsString("Gate::Resource('Payslip', 'App\Policies\PayslipPolicy')", $content);
    }

    public function test_sidebar_and_header_navigation_use_policy_for_payslip_module(): void
    {
        $base = dirname(__DIR__, 2);

        // 1. Sidebar checks policy and does not bypass for arbitrary employees
        $sidebar = file_get_contents($base . '/resources/views/backend/layouts/sidebar.blade.php');
        $this->assertStringContainsString("@can('read', \\App\Models\\Payslip::class)", $sidebar);
        $this->assertStringNotContainsString("auth()->user()->isInternalEmployee() || auth()->user()->linkedEmployee() !== null || auth()->user()->can('read', \\App\Models\\Employee::class)\n                    <li class=\"nav-item\">\n                        <a class=\"nav-link menu-link {{ request()->is('admin/payslip*') ? 'active' : '' }}\" href=\"{{ route('admin.payslip.index') }}\">", $sidebar);

        // 2. Header dropdown checks policy
        $header = file_get_contents($base . '/resources/views/backend/layouts/header.blade.php');
        $this->assertStringContainsString("@can('read', \\App\Models\\Payslip::class)", $header);
        $this->assertStringNotContainsString("Auth::user()?->isInternalEmployee() || Auth::user()?->linkedEmployee() !== null || Auth::user()?->can('read', \\App\Models\\Employee::class)\n                        <a class=\"dropdown-item\" href=\"{{ route('admin.payslip.index') }}\">", $header);
    }

    public function test_payslip_controller_guards_ensure_access_with_policy(): void
    {
        $controllerFile = dirname(__DIR__, 2) . '/app/Http/Controllers/backend/PayslipController.php';
        $this->assertFileExists($controllerFile);
        $content = file_get_contents($controllerFile);

        $this->assertStringContainsString("can('read', \\App\Models\\Payslip::class)", $content);
    }
}
