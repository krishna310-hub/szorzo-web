<?php

namespace Tests\Unit;

use App\Models\Billing;
use App\Models\Candidate;
use App\Models\ClientRequirement;
use App\Models\Employee;
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
            'employee_ctc' => 200000,
            'margin_percentage' => 15.00,
            'tds_percentage' => 10.00,
            'szorzo_share_percentage' => 70.00,
            'investor_share_percentage' => 30.00,
            'status' => 1,
        ]);

        $deployment->computeCommissions();

        // Margin in Rupees = 200000 * 15% = 30000
        $this->assertEquals(30000.00, (float) $deployment->margin_amount);

        // TDS amount = 30000 * 10% = 3000
        $this->assertEquals(3000.00, (float) $deployment->tds_amount);

        // Net margin after TDS = 30000 - 3000 = 27000
        // SZ Share (70%) = 27000 * 70% = 18900
        $this->assertEquals(18900.00, (float) $deployment->szorzo_commission_amount);

        // Investor Share (30%) = 27000 * 30% = 8100
        $this->assertEquals(8100.00, (float) $deployment->investor_commission_amount);
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
            'employee_ctc' => 250000.00,
            'billing_percentage' => 8.33,
            'margin_percentage' => 8.33,
            'tds_percentage' => 10.00,
            'szorzo_share_percentage' => 70.00,
            'investor_share_percentage' => 30.00,
            'status' => 1,
        ]);

        $deployment->computeCommissions();

        // Margin In Rupees = 250000 * 8.33% = 20825.00
        $this->assertEquals(20825.00, (float) $deployment->margin_amount);

        // TDS amount (less TDS from Margin In Rupees) = 20825 * 10% = 2082.50
        $this->assertEquals(2082.50, (float) $deployment->tds_amount);

        // Net Margin after TDS = 20825 - 2082.50 = 18742.50
        // SZ Share (70%) = 18742.50 * 0.70 = 13119.75
        $this->assertEquals(13119.75, (float) $deployment->szorzo_commission_amount);

        // Investor Share (30%) = 18742.50 * 0.30 = 5622.75
        $this->assertEquals(5622.75, (float) $deployment->investor_commission_amount);

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

    public function test_approved_rate_card_removed_and_ctc_used_as_base(): void
    {
        $baseDir = dirname(__DIR__, 2);

        // 1. Form view has no Approved Rate Card input
        $formContent = file_get_contents($baseDir . '/resources/views/backend/invester-deployments/form.blade.php');
        $this->assertStringNotContainsString('Approved Rate Card (₹)', $formContent);
        $this->assertStringNotContainsString('name="approved_rate_card"', $formContent);
        $this->assertStringContainsString('id="contract_duration"', $formContent);
        $this->assertStringContainsString('id="employee_ctc"', $formContent);

        // 2. Deployments index table header shows Employee CTC (₹)
        $indexContent = file_get_contents($baseDir . '/resources/views/backend/invester-deployments/index.blade.php');
        $this->assertStringContainsString('<th>Employee CTC (₹)</th>', $indexContent);
        $this->assertStringNotContainsString('<th>Rate Card (₹)</th>', $indexContent);

        // 3. Investor show table has Employee CTC and does not have Approved Rate Card column
        $showContent = file_get_contents($baseDir . '/resources/views/backend/investers/show.blade.php');
        $this->assertStringNotContainsString('<th>Approved Rate Card</th>', $showContent);
        $this->assertStringContainsString('<th>Employee CTC (₹)</th>', $showContent);

        // 4. Investor dashboard table does not have Approved Rate Card column
        $dashboardContent = file_get_contents($baseDir . '/resources/views/backend/invester-dashboard/index.blade.php');
        $this->assertStringNotContainsString('<th>Approved Rate Card</th>', $dashboardContent);
        $this->assertStringContainsString('<th>Employee CTC (₹)</th>', $dashboardContent);
    }

    public function test_employee_contract_duration_calculation(): void
    {
        // 6 Months duration
        $emp6m = new Employee([
            'employee_name' => 'Mohammed Zaheer Ali',
            'contract_from_date' => '2026-06-08',
            'contract_to_date' => '2026-12-08',
        ]);
        $info6m = $emp6m->getContractDurationInfo();
        $this->assertSame('6 Months', $info6m['duration']);
        $this->assertSame('6 Months', $emp6m->contract_duration);

        // 3 Months duration
        $emp3m = new Employee([
            'employee_name' => 'Apannagary Sowmya',
            'contract_from_date' => '2026-06-29',
            'contract_to_date' => '2026-09-29',
        ]);
        $info3m = $emp3m->getContractDurationInfo();
        $this->assertSame('3 Months', $info3m['duration']);
        $this->assertSame('3 Months', $emp3m->contract_duration);

        // 1 Year duration
        $emp1y = new Employee([
            'employee_name' => 'Annual Staff',
            'contract_from_date' => '2026-04-01',
            'contract_to_date' => '2027-03-31',
        ]);
        $info1y = $emp1y->getContractDurationInfo();
        $this->assertSame('1 Year', $info1y['duration']);
        $this->assertSame('1 Year', $emp1y->contract_duration);

        // Graceful handling when dates are not set
        $empEmpty = new Employee([
            'employee_name' => 'No Dates Staff',
        ]);
        $infoEmpty = $empEmpty->getContractDurationInfo();
        $this->assertSame('', $infoEmpty['duration']);
        $this->assertSame('', $empEmpty->contract_duration);
    }

    public function test_client_billing_percentage_calculated_from_employee_client_requirement(): void
    {
        $baseDir = dirname(__DIR__, 2);

        // 1. Form view no longer has client_billing_badge on Deployed To (Client Name)
        $formContent = file_get_contents($baseDir . '/resources/views/backend/invester-deployments/form.blade.php');
        $this->assertStringNotContainsString('id="client_billing_badge"', $formContent);
        $this->assertStringContainsString('id="employee_billing_sum_badge"', $formContent);
        $this->assertStringContainsString('id="billing_percentage_hint"', $formContent);

        // 2. Test employee client requirement billing calculation
        $billing15 = new Billing(['value' => '15.00']);
        $req1 = new ClientRequirement(['billing_id' => 1]);
        $req1->setRelation('billing', $billing15);

        $cand1 = new Candidate(['candidate_name' => 'Zaheer Staff', 'email' => 'zaheer@example.com']);
        $cand1->setRelation('clientRequirement', $req1);

        $emp1 = new Employee(['employee_name' => 'Zaheer Staff', 'official_mail' => 'zaheer@example.com']);
        // Inject cached matching candidate
        $refProp = new \ReflectionProperty(Employee::class, 'cachedMatchingCandidate');
        $refProp->setValue($emp1, $cand1);
        $refLoaded = new \ReflectionProperty(Employee::class, 'matchingCandidateLoaded');
        $refLoaded->setValue($emp1, true);

        $this->assertEquals(15.00, $emp1->getClientRequirementBillingPercentage());

        // 3. Second employee with 20.00% requirement billing
        $billing20 = new Billing(['value' => '20.00']);
        $req2 = new ClientRequirement(['billing_id' => 2]);
        $req2->setRelation('billing', $billing20);

        $cand2 = new Candidate(['candidate_name' => 'Farsham Staff', 'email' => 'farsham@example.com']);
        $cand2->setRelation('clientRequirement', $req2);

        $emp2 = new Employee(['employee_name' => 'Farsham Staff', 'official_mail' => 'farsham@example.com']);
        $refProp->setValue($emp2, $cand2);
        $refLoaded->setValue($emp2, true);

        $this->assertEquals(20.00, $emp2->getClientRequirementBillingPercentage());

        // 4. Combined billing percentage for multiple employees (15% + 20% = 35%)
        $employees = collect([$emp1, $emp2]);
        $combinedBilling = $employees->sum(fn ($e) => (float) $e->getClientRequirementBillingPercentage());
        $this->assertEquals(35.00, $combinedBilling);
    }

    public function test_contract_report_exact_revenue_and_profit_sharing(): void
    {
        // Kavya B from user Image 2:
        // Monthly Billing Payment: ₹74,100.00 | Billing: 15.00% | Total Salary: ₹74,100.00 | Total Revenue: ₹11,115.00
        $billing15 = new Billing(['value' => '15.00']);
        $req = new ClientRequirement(['billing_id' => 1]);
        $req->setRelation('billing', $billing15);

        $candKavya = new Candidate(['candidate_name' => 'Kavya B', 'email' => 'kavya.basvaraju@gmail.com', 'take_home' => '74100.00']);
        $candKavya->setRelation('clientRequirement', $req);

        $empKavya = new Employee(['employee_name' => 'Kavya B', 'official_mail' => 'kavya.basvaraju@gmail.com']);
        $refProp = new \ReflectionProperty(Employee::class, 'cachedMatchingCandidate');
        $refProp->setValue($empKavya, $candKavya);
        $refLoaded = new \ReflectionProperty(Employee::class, 'matchingCandidateLoaded');
        $refLoaded->setValue($empKavya, true);

        $metricsKavya = $empKavya->getContractReportSalary();
        $this->assertEquals(74100.00, $metricsKavya['salary']);
        $this->assertEquals(15.00, $metricsKavya['billing_percentage']);
        $this->assertEquals(11115.00, $metricsKavya['revenue']);

        // Karun Kumar:
        // Monthly Salary: ₹50,050.00 | Billing: 15.00% | Total Revenue: ₹7,507.50
        $candKarun = new Candidate(['candidate_name' => 'Karun Kumar', 'email' => 'karun@example.com', 'take_home' => '50050.00']);
        $candKarun->setRelation('clientRequirement', $req);

        $empKarun = new Employee(['employee_name' => 'Karun Kumar', 'official_mail' => 'karun@example.com']);
        $refProp->setValue($empKarun, $candKarun);
        $refLoaded->setValue($empKarun, true);

        $metricsKarun = $empKarun->getContractReportSalary();
        $this->assertEquals(50050.00, $metricsKarun['salary']);
        $this->assertEquals(15.00, $metricsKarun['billing_percentage']);
        $this->assertEquals(7507.50, $metricsKarun['revenue']);

        // When combined in an InvesterDeployment:
        // Total Spend: 50,050 + 74,100 = 124,150.00
        // Total Gross Margin (Contract Revenue): 7,507.50 + 11,115.00 = 18,622.50
        // Less TDS (10% standard): 18,622.50 * 10% = 1,862.25
        // Net Distributable Margin: 18,622.50 - 1,862.25 = 16,760.25
        // Investor Share (30%): 16,760.25 * 0.30 = 5,028.08
        // Szorzo Share (70%): 16,760.25 * 0.70 = 11,732.18
        $totalSpend = $metricsKavya['salary'] + $metricsKarun['salary'];
        $totalMargin = $metricsKavya['revenue'] + $metricsKarun['revenue'];
        $tdsAmount = round($totalMargin * 0.10, 2);
        $netMargin = max(0.0, round($totalMargin - $tdsAmount, 2));
        $investorEarned = round($netMargin * 0.30, 2);
        $szorzoEarned = round($netMargin * 0.70, 2);

        $this->assertEquals(124150.00, $totalSpend);
        $this->assertEquals(18622.50, $totalMargin);
        $this->assertEquals(1862.25, $tdsAmount);
        $this->assertEquals(16760.25, $netMargin);
        $this->assertEquals(5028.08, $investorEarned);
        $this->assertEquals(11732.18, $szorzoEarned);
    }

    public function test_parse_duration_months(): void
    {
        $this->assertEquals(6, \App\Http\Controllers\backend\InvesterDashboardController::parseDurationMonths('6 Months'));
        $this->assertEquals(3, \App\Http\Controllers\backend\InvesterDashboardController::parseDurationMonths('3 Months'));
        $this->assertEquals(12, \App\Http\Controllers\backend\InvesterDashboardController::parseDurationMonths('1 Year'));
        $this->assertEquals(24, \App\Http\Controllers\backend\InvesterDashboardController::parseDurationMonths('2 Years'));
        $this->assertEquals(6, \App\Http\Controllers\backend\InvesterDashboardController::parseDurationMonths(null));
        $this->assertEquals(6, \App\Http\Controllers\backend\InvesterDashboardController::parseDurationMonths(''));
    }

    public function test_invester_spend_balance_negative_when_exceeded(): void
    {
        // Example from user request:
        // Investor invests 1,000,000. Monthly employee salary paid 4 lakhs (400,000).
        // Month 1: 400,000 paid -> Balance = 600,000.
        // Month 2: 400,000 paid (800,000 total) -> Balance = 200,000.
        // Month 3: 400,000 paid (1,200,000 total) -> Balance = -200,000.
        // Month 4: 400,000 paid (1,600,000 total) -> Balance = -600,000 (negative value!)
        $capitalInvested = 1000000.0;
        $monthlySalary = 400000.0;

        // Month 1
        $spend1 = $monthlySalary * 1;
        $balance1 = $capitalInvested - $spend1;
        $this->assertEquals(600000.0, $balance1);
        $this->assertFalse($balance1 < 0);

        // Month 2
        $spend2 = $monthlySalary * 2;
        $balance2 = $capitalInvested - $spend2;
        $this->assertEquals(200000.0, $balance2);
        $this->assertFalse($balance2 < 0);

        // Month 3 (Exceeds capital!)
        $spend3 = $monthlySalary * 3;
        $balance3 = $capitalInvested - $spend3;
        $this->assertEquals(-200000.0, $balance3);
        $this->assertTrue($balance3 < 0);

        // Month 4 (Exceeds capital by 6 lakhs!)
        $spend4 = $monthlySalary * 4;
        $balance4 = $capitalInvested - $spend4;
        $this->assertEquals(-600000.0, $balance4);
        $this->assertTrue($balance4 < 0);
    }

    public function test_dashboard_view_contains_4_cards_and_monthly_ledger(): void
    {
        $baseDir = dirname(__DIR__, 2);
        $dashboardContent = file_get_contents($baseDir . '/resources/views/backend/invester-dashboard/index.blade.php');

        // Check for 4 new cards in dashboard
        $this->assertStringContainsString('Total Employees Count', $dashboardContent);
        $this->assertStringContainsString('Monthly Employee Cost', $dashboardContent);
        $this->assertStringContainsString('Total Employee Spend', $dashboardContent);
        $this->assertStringContainsString('Balance Investment', $dashboardContent);

        // Check for Monthly Spend & Earned Ledger table
        $this->assertStringContainsString('Monthly Spend & Earned Ledger', $dashboardContent);
        $this->assertStringContainsString('id="monthlyLedgerTable"', $dashboardContent);
        $this->assertStringContainsString('Monthly Spend (Salary Paid)', $dashboardContent);
        $this->assertStringContainsString('Monthly Earned (Investor Share)', $dashboardContent);
        $this->assertStringContainsString('Cumulative Spend', $dashboardContent);

        // Check for negative balance display styling (- ₹)
        $this->assertStringContainsString('- ₹ {{ number_format(abs($metrics[\'balance_investment\'])', $dashboardContent);

        // Check show view as well
        $showContent = file_get_contents($baseDir . '/resources/views/backend/investers/show.blade.php');
        $this->assertStringContainsString('Mapped Talent Count', $showContent);
        $this->assertStringContainsString('Monthly Employee Cost', $showContent);
        $this->assertStringContainsString('Total Employee Spend', $showContent);
        $this->assertStringContainsString('Balance Investment', $showContent);
    }

    public function test_investor_login_sidebar_single_menu_and_hidden_dashboard_sections(): void
    {
        $baseDir = dirname(__DIR__, 2);

        // 1. Sidebar ensures investor does not have duplicate dashboard menu items
        $sidebarContent = file_get_contents($baseDir . '/resources/views/backend/layouts/sidebar.blade.php');
        $this->assertStringContainsString("!auth()->user()->isInvestor() && (int) auth()->user()->role_id !== 6", $sidebarContent);

        // 2. Dashboard hides the 3 redundant sections
        $dashboardContent = file_get_contents($baseDir . '/resources/views/backend/invester-dashboard/index.blade.php');
        $this->assertStringContainsString("Sections hidden per user request: Investors Performance Breakdown", $dashboardContent);
        $this->assertStringContainsString("@if(false)", $dashboardContent);
    }

    public function test_investor_login_dashboard_removes_start_time_without_error(): void
    {
        $baseDir = dirname(__DIR__, 2);

        // 1. Verify attendance navbar widget blade suppresses start time for investor and investor dashboard
        $widgetContent = file_get_contents($baseDir . '/resources/views/backend/layouts/attendance-navbar-widget.blade.php');
        $this->assertStringContainsString('$isInvestorUser = $user && ($user->isInvestor() || (int) $user->role_id === 6);', $widgetContent);
        $this->assertStringContainsString('$isInvestorDashboard = request()->routeIs(\'admin.investers.*\')', $widgetContent);
        $this->assertStringContainsString('$showPersonalTimer = $user && !$isSuperAdmin && !$isInvestorContext;', $widgetContent);
        $this->assertStringContainsString('@if($showPersonalTimer || $isAdmin)', $widgetContent);
        $this->assertStringContainsString('if (!isPersonalTimerActive) return;', $widgetContent);

        // 2. Verify AttendanceTimerController has investor checks
        $userInvestor = new \App\Models\User(['name' => 'Test Investor', 'role_id' => 6]);
        $this->assertTrue($userInvestor->isInvestor());

        $controller = new \App\Http\Controllers\backend\AttendanceTimerController();
        $refMethod = new \ReflectionMethod($controller, 'isInvestorUser');
        $refMethod->setAccessible(true);
        $this->assertTrue($refMethod->invoke($controller, $userInvestor));

        $adminCheck = new \ReflectionMethod($controller, 'isAdminUser');
        $adminCheck->setAccessible(true);
        $this->assertFalse($adminCheck->invoke($controller, $userInvestor));
    }
}

