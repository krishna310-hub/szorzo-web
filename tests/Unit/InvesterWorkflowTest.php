<?php

namespace Tests\Unit;

use App\Models\Invester;
use App\Models\InvesterDeployment;
use App\Models\Role;
use App\Models\User;
use App\Policies\InvesterDeploymentPolicy;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class InvesterWorkflowTest extends TestCase
{
    public function test_invester_role_id_6_and_detection(): void
    {
        $role = new Role([
            'name' => 'Invester',
            'access_level' => 'invester',
            'status' => 1,
            'its_default' => 1,
        ]);
        $role->id = 6;

        $user = new User([
            'name' => 'John Investor',
            'email' => 'john@investor.com',
            'role_id' => 6,
        ]);
        $user->setRelation('role', $role);

        $this->assertSame(6, $user->role_id);
        $this->assertTrue($user->isInvestor());
        $this->assertFalse($user->isManagement());
    }

    public function test_invester_user_linking_and_credentials(): void
    {
        $invester = new Invester([
            'invester_id' => 'SZ INV 0001',
            'name' => 'Apex Ventures',
            'primary_email' => 'apex@ventures.com',
            'status' => 1,
        ]);
        $invester->id = 10;

        $user = new User([
            'name' => 'Apex Ventures',
            'email' => 'apex@ventures.com',
            'role_id' => 6,
            'ref' => 'SZ INV 0001',
        ]);
        $user->id = 20;

        $invester->user_id = $user->id;
        $invester->setRelation('user', $user);
        $user->setRelation('invester', $invester);

        $this->assertSame($user->id, $invester->user->id);
        $this->assertSame($invester->id, $user->invester->id);
        $this->assertSame('SZ INV 0001', $user->linkedInvester()->invester_id);
        $this->assertSame('apex@ventures.com', $invester->linkedUser()->email);
    }

    public function test_invester_deployment_profit_sharing_calculations(): void
    {
        // Testing exact numbers from uploaded photo:
        // Approved Rate Card: 200000
        // Employee CTC: 1790000
        // Margin %: 15%
        // TDS %: 10%
        // Margin In Rupees: 30000
        // Profit Sharing Model: SZ 70%, Investor 30%
        $deployment = new InvesterDeployment([
            'invester_id' => 1,
            'mode_of_engagement' => 'Contract',
            'contract_duration' => '6 Months',
            'approved_rate_card' => 200000,
            'employee_ctc' => 1790000,
            'margin_percentage' => 15.00,
            'tds_percentage' => 10.00,
            'szorzo_share_percentage' => 70.00,
            'investor_share_percentage' => 30.00,
            'status' => 1,
        ]);

        $deployment->computeCommissions();

        // Margin in Rupees = 200000 * 15% = 30000
        $this->assertEquals(30000.00, (float) $deployment->margin_amount);

        // SZ Share (70%) = 30000 * 70% = 21000
        $this->assertEquals(21000.00, (float) $deployment->szorzo_commission_amount);

        // Investor Share (30%) = 30000 * 30% = 9000
        $this->assertEquals(9000.00, (float) $deployment->investor_commission_amount);
    }

    public function test_invester_deployment_policy_structure(): void
    {
        $policy = new InvesterDeploymentPolicy();
        $this->assertTrue(method_exists($policy, 'read'));
        $this->assertTrue(method_exists($policy, 'create'));
        $this->assertTrue(method_exists($policy, 'edit'));
        $this->assertTrue(method_exists($policy, 'delete'));
        $this->assertTrue(method_exists($policy, 'before'));
    }

    public function test_invester_dashboard_and_deployment_routes_exist(): void
    {
        $routesContent = file_get_contents(dirname(__DIR__, 2) . '/routes/web.php');
        $this->assertStringContainsString("InvesterDashboardController::class", $routesContent);
        $this->assertStringContainsString("investers.dashboard", $routesContent);
        $this->assertStringContainsString("invester-deployments.", $routesContent);
    }

    public function test_investor_portal_login_and_routing(): void
    {
        $role = new Role([
            'name' => 'Invester',
            'access_level' => 'invester',
            'status' => 1,
            'its_default' => 1,
        ]);
        $role->id = 6;

        $user = new User([
            'name' => 'Investor User',
            'email' => 'investor@szorzo.com',
            'role_id' => 6,
        ]);
        $user->setRelation('role', $role);

        // Verify designated portal is investor
        $this->assertSame('investor', $user->getDesignatedPortal());
        $this->assertTrue($user->isAllowedForPortal('investor'));
        $this->assertFalse($user->isAllowedForPortal('mgmt'));
        $this->assertFalse($user->isAllowedForPortal('rinos'));
        $this->assertFalse($user->isAllowedForPortal('sales'));

        // Verify portal configuration
        $config = \App\Http\Controllers\backend\LoginController::getPortalConfig('investor');
        $this->assertSame('investor', $config['portal']);
        $this->assertSame('Investor Portal', $config['portalTitle']);
        $this->assertSame('Investor Portal', $config['portalBadge']);
        $this->assertStringContainsString('investor', $config['portalAction']);

        // Verify portal resolution from request
        $controller = new \App\Http\Controllers\backend\LoginController();
        $req1 = \Illuminate\Http\Request::create('/investor/login', 'GET');
        $this->assertSame('investor', $controller->resolveRequestedPortal($req1));

        $req2 = \Illuminate\Http\Request::create('/check-login', 'POST', ['portal' => 'investor']);
        $this->assertSame('investor', $controller->resolveRequestedPortal($req2));

        $req3 = \Illuminate\Http\Request::create('/check-login', 'POST', ['portal' => 'invester']);
        $this->assertSame('investor', $controller->resolveRequestedPortal($req3));

        // Verify routes in web.php
        $routesContent = file_get_contents(dirname(__DIR__, 2) . '/routes/web.php');
        $this->assertStringContainsString("login.investor", $routesContent);
        $this->assertStringContainsString("indexInvestor", $routesContent);
        $this->assertStringContainsString("/invester/login", $routesContent);
    }

    public function test_invester_deployment_multiselect_and_tds_calculation(): void
    {
        $deployment = new InvesterDeployment([
            'invester_id' => 1,
            'client_ids' => [10, 12],
            'employee_ids' => [101, 102, 103],
            'mode_of_engagement' => 'Contract',
            'contract_duration' => '12 Months',
            'approved_rate_card' => 250000.00,
            'employee_ctc' => 2200000.00,
            'billing_percentage' => 8.33,
            'margin_percentage' => 8.33,
            'tds_percentage' => 10.00,
            'szorzo_share_percentage' => 70.00,
            'investor_share_percentage' => 30.00,
            'status' => 1,
        ]);

        $deployment->computeCommissions();

        // Rate card: 250000
        // TDS amount = 250000 * 10% = 25000
        $this->assertEquals(25000.00, (float) $deployment->tds_amount);

        // Margin In Rupees = 250000 * 8.33% = 20825.00
        $this->assertEquals(20825.00, (float) $deployment->margin_amount);

        // SZ Share (70%) = 20825 * 0.70 = 14577.50
        $this->assertEquals(14577.50, (float) $deployment->szorzo_commission_amount);

        // Investor Share (30%) = 20825 * 0.30 = 6247.50
        $this->assertEquals(6247.50, (float) $deployment->investor_commission_amount);

        // JSON array serialization
        $this->assertSame([10, 12], $deployment->client_ids);
        $this->assertSame([101, 102, 103], $deployment->employee_ids);
        $this->assertEquals(8.33, (float) $deployment->billing_percentage);
    }

    public function test_sidebar_includes_investor_dashboard_and_modules(): void
    {
        $sidebarContent = file_get_contents(dirname(__DIR__, 2) . '/resources/views/backend/layouts/sidebar.blade.php');
        $this->assertStringContainsString("route('admin.investers.dashboard')", $sidebarContent);
        $this->assertStringContainsString("route('admin.invester-deployments.index')", $sidebarContent);
    }

    public function test_form_removes_default_staffing_8_33_and_hardcoded_defaults(): void
    {
        $formContent = file_get_contents(dirname(__DIR__, 2) . '/resources/views/backend/invester-deployments/form.blade.php');

        // 1. "Default Staffing: 8.33%" badge must be completely removed
        $this->assertStringNotContainsString('Default Staffing: 8.33%', $formContent);

        // 2. No hardcoded '8.33' default on billing_percentage
        $this->assertStringNotContainsString("?? '8.33'", $formContent);

        // 3. No hardcoded '200000' default on approved_rate_card
        $this->assertStringNotContainsString("?? '200000'", $formContent);

        // 4. No hardcoded '1790000' default on employee_ctc
        $this->assertStringNotContainsString("?? '1790000'", $formContent);

        // 5. Contract Report hints and calculations present
        $this->assertStringContainsString('Contract Report', $formContent);
        $this->assertStringContainsString('getContractReportSalary', $formContent);
    }

    public function test_get_deployed_employee_ids_helper_structure(): void
    {
        $this->assertTrue(method_exists(InvesterDeployment::class, 'getDeployedEmployeeIds'));

        $reflection = new \ReflectionMethod(InvesterDeployment::class, 'getDeployedEmployeeIds');
        $this->assertTrue($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
    }

    public function test_employee_contract_report_salary_method_structure(): void
    {
        $this->assertTrue(method_exists(\App\Models\Employee::class, 'getContractReportSalary'));

        $employee = new \App\Models\Employee([
            'employee_name' => 'Demo External',
            'monthly_gross' => 45000.00,
        ]);

        $salaryData = $employee->getContractReportSalary();
        $this->assertIsArray($salaryData);
        $this->assertArrayHasKey('salary', $salaryData);
        $this->assertArrayHasKey('present_days', $salaryData);
        $this->assertArrayHasKey('absent_days', $salaryData);
        $this->assertArrayHasKey('days_in_month', $salaryData);
        $this->assertArrayHasKey('source', $salaryData);
        $this->assertEquals(45000.00, (float) $salaryData['salary']);
    }

    public function test_form_view_compilation(): void
    {
        $investers = collect([
            new Invester(['id' => 1, 'name' => 'Alpha Capital', 'invester_id' => 'SZ INV 0001', 'status' => 1]),
        ]);
        $client = new \App\Models\Client(['id' => 1, 'client' => 'Client One', 'status' => 1]);
        $clients = collect([$client]);
        $employee = new \App\Models\Employee([
            'id' => 1,
            'employee_name' => 'Test Employee',
            'employee_no' => 'SZ001',
            'client_id' => 1,
            'monthly_gross' => 50000,
            'designation' => 'Developer',
            'employment_type' => \App\Models\Employee::EMPLOYMENT_TYPE_EXTERNAL,
        ]);
        $employee->setRelation('client', $client);
        $employees = collect([$employee]);

        $viewHtml = view('backend.invester-deployments.form', [
            'investers' => $investers,
            'clients' => $clients,
            'employees' => $employees,
            'preselectedInvesterId' => 1,
            'errors' => new \Illuminate\Support\ViewErrorBag(),
        ])->render();

        $this->assertNotEmpty($viewHtml);
        $this->assertStringContainsString('Alpha Capital', $viewHtml);
        $this->assertStringContainsString('Client One', $viewHtml);
        $this->assertStringNotContainsString('Default Staffing: 8.33%', $viewHtml);
        $this->assertStringContainsString('Contract Report', $viewHtml);

        // Also test edit mode rendering with existing deployment
        $deployment = new InvesterDeployment([
            'id' => 99,
            'invester_id' => 1,
            'client_ids' => [1],
            'employee_ids' => [1],
            'mode_of_engagement' => 'Contract',
            'contract_duration' => '6 Months',
            'approved_rate_card' => 200000,
            'employee_ctc' => 50000,
            'billing_percentage' => 12.50,
            'margin_percentage' => 12.50,
            'status' => 1,
        ]);
        $deployment->setRelation('invester', $investers->first());

        $editViewHtml = view('backend.invester-deployments.form', [
            'deployment' => $deployment,
            'investers' => $investers,
            'clients' => $clients,
            'employees' => $employees,
            'errors' => new \Illuminate\Support\ViewErrorBag(),
        ])->render();

        $this->assertNotEmpty($editViewHtml);
        $this->assertStringContainsString('12.5', $editViewHtml);
        $this->assertStringNotContainsString('Default Staffing: 8.33%', $editViewHtml);
    }

    public function test_mode_of_engagement_removed_from_views_and_filters(): void
    {
        $baseDir = dirname(__DIR__, 2);

        // 1. Form view has no Mode of Engagement
        $formContent = file_get_contents($baseDir . '/resources/views/backend/invester-deployments/form.blade.php');
        $this->assertStringNotContainsString('Mode of Engagement', $formContent);
        $this->assertStringNotContainsString('name="mode_of_engagement"', $formContent);

        // 2. Deployment index view has no Mode of Engagement in filter or table
        $indexContent = file_get_contents($baseDir . '/resources/views/backend/invester-deployments/index.blade.php');
        $this->assertStringNotContainsString('Mode of Engagement', $indexContent);
        $this->assertStringNotContainsString('Engagement Mode', $indexContent);
        $this->assertStringNotContainsString('filter_mode', $indexContent);

        // 3. Investor show view has no Mode of Engagement
        $showContent = file_get_contents($baseDir . '/resources/views/backend/investers/show.blade.php');
        $this->assertStringNotContainsString('Mode of Engagement', $showContent);

        // 4. Investor Dashboard has no Mode of Engagement filter or table column
        $dashboardContent = file_get_contents($baseDir . '/resources/views/backend/invester-dashboard/index.blade.php');
        $this->assertStringNotContainsString('name="mode_of_engagement"', $dashboardContent);
        $this->assertStringNotContainsString('<th>Engagement Mode</th>', $dashboardContent);
    }
}

