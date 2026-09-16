<?php

namespace Tests\Unit;

use App\Http\Controllers\backend\LoginController;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;

class RolePortalLoginTest extends TestCase
{
    public function test_super_admin_and_admin_assigned_to_mgmt_portal(): void
    {
        $superAdminRole = new Role(['name' => 'Super Admin', 'access_level' => 'super_admin']);
        $user1 = new User();
        $user1->id = 2;
        $user1->setRelation('role', $superAdminRole);

        $this->assertSame('mgmt', $user1->getDesignatedPortal());
        $this->assertTrue($user1->isAllowedForPortal('mgmt'));
        $this->assertFalse($user1->isAllowedForPortal('rinos'));
        $this->assertFalse($user1->isAllowedForPortal('sales'));
        $this->assertTrue($user1->isManagement());
        $this->assertFalse($user1->isRecruiter());
        $this->assertFalse($user1->isSales());

        $adminRole = new Role(['name' => 'Admin', 'access_level' => 'admin']);
        $user2 = new User();
        $user2->id = 3;
        $user2->setRelation('role', $adminRole);

        $this->assertSame('mgmt', $user2->getDesignatedPortal());
        $this->assertTrue($user2->isAllowedForPortal('mgmt'));
        $this->assertFalse($user2->isAllowedForPortal('rinos'));
        $this->assertFalse($user2->isAllowedForPortal('sales'));

        $subadminRole = new Role(['name' => 'Sub Admin', 'access_level' => 'sub_admin']);
        $user3 = new User();
        $user3->id = 4;
        $user3->setRelation('role', $subadminRole);

        $this->assertSame('mgmt', $user3->getDesignatedPortal());
        $this->assertTrue($user3->isAllowedForPortal('mgmt'));
        $this->assertFalse($user3->isAllowedForPortal('rinos'));
        $this->assertFalse($user3->isAllowedForPortal('sales'));

        $managementRole = new Role(['name' => 'Management', 'access_level' => 'management']);
        $user4 = new User();
        $user4->id = 5;
        $user4->setRelation('role', $managementRole);

        $this->assertSame('mgmt', $user4->getDesignatedPortal());
        $this->assertTrue($user4->isAllowedForPortal('mgmt'));
        $this->assertFalse($user4->isAllowedForPortal('rinos'));
        $this->assertFalse($user4->isAllowedForPortal('sales'));

        // User ID 1 is always management
        $rootUser = new User();
        $rootUser->id = 1;
        $this->assertSame('mgmt', $rootUser->getDesignatedPortal());
        $this->assertTrue($rootUser->isAllowedForPortal('mgmt'));
    }

    public function test_recruiters_assigned_to_rinos_portal(): void
    {
        $recruiterRole = new Role(['name' => 'Recruiter', 'access_level' => 'recruiter']);
        $user1 = new User();
        $user1->id = 10;
        $user1->setRelation('role', $recruiterRole);

        $this->assertSame('rinos', $user1->getDesignatedPortal());
        $this->assertTrue($user1->isAllowedForPortal('rinos'));
        $this->assertFalse($user1->isAllowedForPortal('mgmt'));
        $this->assertFalse($user1->isAllowedForPortal('sales'));
        $this->assertTrue($user1->isRecruiter());
        $this->assertFalse($user1->isManagement());
        $this->assertFalse($user1->isSales());

        $recruiterDlRole = new Role(['name' => 'Recruiter DL', 'access_level' => 'recruiter_dl']);
        $user2 = new User();
        $user2->id = 11;
        $user2->setRelation('role', $recruiterDlRole);

        $this->assertSame('rinos', $user2->getDesignatedPortal());
        $this->assertTrue($user2->isAllowedForPortal('rinos'));
        $this->assertFalse($user2->isAllowedForPortal('mgmt'));
        $this->assertFalse($user2->isAllowedForPortal('sales'));

        $deliveryLeadRole = new Role(['name' => 'Delivery Lead', 'access_level' => 'delivery_lead']);
        $user3 = new User();
        $user3->id = 12;
        $user3->setRelation('role', $deliveryLeadRole);

        $this->assertSame('rinos', $user3->getDesignatedPortal());
        $this->assertTrue($user3->isAllowedForPortal('rinos'));
        $this->assertFalse($user3->isAllowedForPortal('mgmt'));
        $this->assertFalse($user3->isAllowedForPortal('sales'));
    }

    public function test_sales_assigned_to_sales_portal(): void
    {
        $salesRole = new Role(['name' => 'Sales', 'access_level' => 'sales']);
        $user1 = new User();
        $user1->id = 20;
        $user1->setRelation('role', $salesRole);

        $this->assertSame('sales', $user1->getDesignatedPortal());
        $this->assertTrue($user1->isAllowedForPortal('sales'));
        $this->assertFalse($user1->isAllowedForPortal('mgmt'));
        $this->assertFalse($user1->isAllowedForPortal('rinos'));
        $this->assertTrue($user1->isSales());
        $this->assertFalse($user1->isManagement());
        $this->assertFalse($user1->isRecruiter());

        $salesExecRole = new Role(['name' => 'Sales Executive', 'access_level' => 'sales_executive']);
        $user2 = new User();
        $user2->id = 21;
        $user2->setRelation('role', $salesExecRole);

        $this->assertSame('sales', $user2->getDesignatedPortal());
        $this->assertTrue($user2->isAllowedForPortal('sales'));
        $this->assertFalse($user2->isAllowedForPortal('mgmt'));
        $this->assertFalse($user2->isAllowedForPortal('rinos'));

        $bdRole = new Role(['name' => 'Business Development', 'access_level' => 'business_development']);
        $user3 = new User();
        $user3->id = 22;
        $user3->setRelation('role', $bdRole);

        $this->assertSame('sales', $user3->getDesignatedPortal());
        $this->assertTrue($user3->isAllowedForPortal('sales'));
        $this->assertFalse($user3->isAllowedForPortal('mgmt'));
        $this->assertFalse($user3->isAllowedForPortal('rinos'));
    }

    public function test_login_controller_portal_resolution(): void
    {
        $controller = new LoginController();

        // 1. Explicit portal in request input
        $req1 = Request::create('/check-login', 'POST', ['portal' => 'rinos']);
        $this->assertSame('rinos', $controller->resolveRequestedPortal($req1));

        $req2 = Request::create('/check-login', 'POST', ['portal' => 'sales']);
        $this->assertSame('sales', $controller->resolveRequestedPortal($req2));

        $req3 = Request::create('/check-login', 'POST', ['portal' => 'mgmt']);
        $this->assertSame('mgmt', $controller->resolveRequestedPortal($req3));

        // 2. URI path resolution
        $reqPathRinos = Request::create('/rinos/login', 'POST');
        $this->assertSame('rinos', $controller->resolveRequestedPortal($reqPathRinos));

        $reqPathSales = Request::create('/sales/login', 'POST');
        $this->assertSame('sales', $controller->resolveRequestedPortal($reqPathSales));

        $reqPathMgmt = Request::create('/mgmt/login', 'POST');
        $this->assertSame('mgmt', $controller->resolveRequestedPortal($reqPathMgmt));

        // 3. Referer header fallback
        $reqRefererRinos = Request::create('/check-login', 'POST', [], [], [], [
            'HTTP_REFERER' => 'https://szorzo.com/rinos/login',
        ]);
        $this->assertSame('rinos', $controller->resolveRequestedPortal($reqRefererRinos));

        $reqRefererSales = Request::create('/check-login', 'POST', [], [], [], [
            'HTTP_REFERER' => 'https://szorzo.com/sales/login',
        ]);
        $this->assertSame('sales', $controller->resolveRequestedPortal($reqRefererSales));

        // 4. Default fallback
        $reqDefault = Request::create('/check-login', 'POST');
        $this->assertSame('mgmt', $controller->resolveRequestedPortal($reqDefault));
    }

    public function test_portal_config_generation(): void
    {
        // Test portal configs contain required keys
        foreach (['mgmt', 'rinos', 'sales'] as $portal) {
            $config = LoginController::getPortalConfig($portal);
            $this->assertArrayHasKey('portal', $config);
            $this->assertArrayHasKey('portalTitle', $config);
            $this->assertArrayHasKey('portalSubtitle', $config);
            $this->assertArrayHasKey('portalBadge', $config);
            $this->assertArrayHasKey('portalAction', $config);
            $this->assertSame($portal, $config['portal']);
        }
    }
}
