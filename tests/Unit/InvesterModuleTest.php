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
}

