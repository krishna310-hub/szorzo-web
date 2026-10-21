<?php

namespace Tests\Unit;

use App\Models\Invester;
use App\Models\InvesterDeployment;
use App\Models\Role;
use App\Models\User;
use App\Policies\InvesterDeploymentPolicy;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\TestCase;

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

    public function test_sidebar_includes_investor_dashboard_and_modules(): void
    {
        $sidebarContent = file_get_contents(dirname(__DIR__, 2) . '/resources/views/backend/layouts/sidebar.blade.php');
        $this->assertStringContainsString("route('admin.investers.dashboard')", $sidebarContent);
        $this->assertStringContainsString("route('admin.invester-deployments.index')", $sidebarContent);
    }
}

