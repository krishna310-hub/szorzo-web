<?php

namespace Tests\Unit;

use App\Models\Invester;
use App\Models\InvesterFinancialDetail;
use App\Models\InvesterLegalCompliance;
use App\Models\InvesterTeamManagement;
use App\Policies\InvesterFinancialDetailPolicy;
use App\Policies\InvesterLegalCompliancePolicy;
use App\Policies\InvesterPolicy;
use App\Policies\InvesterTeamManagementPolicy;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\TestCase;

class InvesterModuleTest extends TestCase
{
    public function test_invester_id_generation_format(): void
    {
        // Test prefix and format matching
        $format = Invester::nextInvesterId();
        $this->assertStringStartsWith('SZ INV ', $format);
        $this->assertSame('SZ INV 0001', $format);
    }

    public function test_invester_models_fillable_and_relationships(): void
    {
        $invester = new Invester([
            'invester_id' => 'SZ INV 0001',
            'name' => 'Acme Capital',
            'investor_type' => 'Venture Capitalist',
            'contact' => '+91 9876543210',
            'primary_email' => 'contact@acmecapital.com',
            'status' => 1,
        ]);

        $this->assertSame('SZ INV 0001', $invester->invester_id);
        $this->assertSame('Acme Capital', $invester->name);
        $this->assertTrue(method_exists($invester, 'accountManager'));
        $this->assertTrue(method_exists($invester, 'teamManagements'));
        $this->assertTrue(method_exists($invester, 'financialDetails'));
        $this->assertTrue(method_exists($invester, 'legalCompliances'));

        $team = new InvesterTeamManagement([
            'team_name' => 'Core Investment Team',
            'managed_employee_count' => '11-50',
            'governance_role' => 'Managing Partner',
        ]);
        $this->assertSame('Core Investment Team', $team->team_name);
        $this->assertTrue(method_exists($team, 'invester'));

        $financial = new InvesterFinancialDetail([
            'investment_amount' => 5000000.00,
            'equity_percentage' => 15.50,
            'shares_units_held' => '50000',
        ]);
        $this->assertSame('50000', $financial->shares_units_held);
        $this->assertTrue(method_exists($financial, 'invester'));

        $legal = new InvesterLegalCompliance([
            'tax_id_number' => 'ABCDE1234F',
            'kyc_aml_status' => 'Verified',
        ]);
        $this->assertSame('Verified', $legal->kyc_aml_status);
        $this->assertTrue(method_exists($legal, 'invester'));
    }

    public function test_invester_policies_structure(): void
    {
        $policies = [
            InvesterPolicy::class,
            InvesterTeamManagementPolicy::class,
            InvesterFinancialDetailPolicy::class,
            InvesterLegalCompliancePolicy::class,
        ];

        foreach ($policies as $policyClass) {
            $policy = new $policyClass();
            $this->assertTrue(method_exists($policy, 'read'));
            $this->assertTrue(method_exists($policy, 'create'));
            $this->assertTrue(method_exists($policy, 'edit'));
            $this->assertTrue(method_exists($policy, 'delete'));
            $this->assertTrue(method_exists($policy, 'before'));
        }
    }

    public function test_sidebar_and_navigation_buttons(): void
    {
        $baseDir = dirname(__DIR__, 2);
        $sidebarContent = file_get_contents($baseDir . '/resources/views/backend/layouts/sidebar.blade.php');
        $this->assertStringNotContainsString('#sidebarInvesters', $sidebarContent, 'Sidebar should not contain collapsible investor submenus');
        $this->assertStringContainsString('ri-funds-line', $sidebarContent);
        $this->assertStringContainsString('admin/investers*', $sidebarContent);

        $navButtonsPath = $baseDir . '/resources/views/backend/investers/partials/nav-buttons.blade.php';
        $this->assertFileExists($navButtonsPath);
        $navButtonsContent = file_get_contents($navButtonsPath);

        $this->assertStringContainsString("can('read', \\App\\Models\\Invester::class)", $navButtonsContent);
        $this->assertStringContainsString("can('read', \\App\\Models\\InvesterTeamManagement::class)", $navButtonsContent);
        $this->assertStringContainsString("can('read', \\App\\Models\\InvesterFinancialDetail::class)", $navButtonsContent);
        $this->assertStringContainsString("can('read', \\App\\Models\\InvesterLegalCompliance::class)", $navButtonsContent);

        $this->assertStringContainsString("route('admin.investers.index')", $navButtonsContent);
        $this->assertStringContainsString("route('admin.invester-team-managements.index')", $navButtonsContent);
        $this->assertStringContainsString("route('admin.invester-financial-details.index')", $navButtonsContent);
        $this->assertStringContainsString("route('admin.invester-legal-compliances.index')", $navButtonsContent);
    }

    public function test_invester_account_manager_relationship_and_employee_accessors(): void
    {
        $reflection = new \ReflectionMethod(Invester::class, 'accountManager');
        $this->assertTrue($reflection->isPublic());

        $employee = new \App\Models\Employee([
            'employee_name' => 'John External',
            'official_mail' => 'john@client.com',
        ]);
        $this->assertSame('John External', $employee->name);
        $this->assertSame('john@client.com', $employee->email);
    }

    public function test_invester_multiselect_account_managers(): void
    {
        $invester = new Invester([
            'account_manager_ids' => [4, 5],
        ]);
        $this->assertEquals([4, 5], $invester->account_manager_ids);
        $this->assertTrue(method_exists($invester, 'getAssignedAccountManagersAttribute'));
        $this->assertTrue(method_exists($invester, 'getAccountManagerNamesAttribute'));
    }

    public function test_invester_manual_account_manager_and_portal_fields(): void
    {
        $invester = new Invester([
            'account_manager_name' => 'Sarah Connor',
            'account_manager_email' => 'sarah@example.com',
            'account_manager_contact' => '+91 9988776655',
            'account_manager_role' => 'Key Account Director',
            'portal_username' => 'investor_alpha',
            'portal_password' => 'AlphaSecure@2026',
        ]);

        $this->assertSame('Sarah Connor', $invester->account_manager_name);
        $this->assertSame('sarah@example.com', $invester->account_manager_email);
        $this->assertSame('+91 9988776655', $invester->account_manager_contact);
        $this->assertSame('Key Account Director', $invester->account_manager_role);
        $this->assertSame('investor_alpha', $invester->portal_username);
        $this->assertSame('AlphaSecure@2026', $invester->portal_password);
        $this->assertSame('Sarah Connor', $invester->account_manager_names);
    }

    public function test_invester_form_view_fields(): void
    {
        $baseDir = dirname(__DIR__, 2);
        $formContent = file_get_contents($baseDir . '/resources/views/backend/investers/form.blade.php');
        
        // 1. Account manager text fields instead of dropdown
        $this->assertStringContainsString('name="account_manager_name"', $formContent);
        $this->assertStringContainsString('name="account_manager_role"', $formContent);
        $this->assertStringContainsString('name="account_manager_email"', $formContent);
        $this->assertStringContainsString('name="account_manager_contact"', $formContent);
        $this->assertStringNotContainsString('id="account_manager_ids"', $formContent);
        $this->assertStringNotContainsString('Select External Employees', $formContent);

        // 2. Status radio buttons in Basic Information
        $basicInfoPos = strpos($formContent, 'Basic Information');
        $statusPos = strpos($formContent, 'name="status"');
        $relationshipPos = strpos($formContent, 'Relationship Management & Notes');
        $this->assertTrue($statusPos > $basicInfoPos && $statusPos < $relationshipPos, 'Status should be located inside Basic Information section');

        // 3. Username and password
        $this->assertStringContainsString('name="user_name"', $formContent);
        $this->assertStringContainsString('name="user_password"', $formContent);
        $this->assertStringContainsString('portal_password', $formContent);

        // 4. Number of Shares / Units Held removed
        $this->assertStringNotContainsString('name="shares_units_held"', $formContent);
        $this->assertStringNotContainsString('Number of Shares / Units Held', $formContent);
    }

    public function test_invester_show_view_fields(): void
    {
        $baseDir = dirname(__DIR__, 2);
        $showContent = file_get_contents($baseDir . '/resources/views/backend/investers/show.blade.php');
        
        $this->assertStringContainsString('account_manager_name', $showContent);
        $this->assertStringContainsString('portal_password', $showContent);
        $this->assertStringNotContainsString('Shares / Units Held', $showContent);
    }
}

