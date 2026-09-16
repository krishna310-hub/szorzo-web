<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalLoginFeatureTest extends TestCase
{
    public function test_portal_login_pages_render_correct_portal_data(): void
    {
        // 1. Management Portal
        $mgmtResponse = $this->get('/mgmt/login');
        $mgmtResponse->assertStatus(200);
        $mgmtResponse->assertSee('Management Portal');
        $mgmtResponse->assertSee('value="mgmt"', false);

        // 2. Rinos Recruiter Portal
        $rinosResponse = $this->get('/rinos/login');
        $rinosResponse->assertStatus(200);
        $rinosResponse->assertSee('Recruiter Portal');
        $rinosResponse->assertSee('value="rinos"', false);

        // 3. Sales Portal
        $salesResponse = $this->get('/sales/login');
        $salesResponse->assertStatus(200);
        $salesResponse->assertSee('Sales Portal');
        $salesResponse->assertSee('value="sales"', false);
    }

    public function test_legacy_login_url_redirects_to_mgmt(): void
    {
        $response = $this->get('/login');
        $response->assertRedirect('/mgmt/login');
    }
}
