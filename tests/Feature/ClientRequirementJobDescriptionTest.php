<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\ClientJobRole;
use App\Models\ClientRequirement;
use App\Models\JobRole;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientRequirementJobDescriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_description_is_derived_from_selected_client_job_role(): void
    {
        $role = Role::create([
            'name' => 'Administrator',
            'access_level' => 'super_admin',
            'status' => true,
        ]);
        $user = User::factory()->create(['role_id' => $role->id]);
        $client = Client::create(['client' => 'Acme', 'status' => true]);
        $jobRole = JobRole::create(['job_role' => 'Developer', 'status' => true]);
        $clientJobRole = ClientJobRole::create([
            'client_id' => $client->id,
            'job_role_id' => $jobRole->id,
            'job_description' => '<p>Build and maintain applications.</p>',
            'status' => true,
        ]);

        $response = $this->actingAs($user)->post(route('admin.client-requirements.store'), [
            'client_id' => $client->id,
            'job_role_id' => $jobRole->id,
            'status' => 1,
            'is_priority' => 0,
        ]);

        $response->assertRedirect(route('admin.client-requirements.index'));
        $response->assertSessionHasNoErrors();
        $this->assertSame(
            $clientJobRole->id,
            ClientRequirement::firstOrFail()->job_description_id
        );

        $indexResponse = $this->actingAs($user)->getJson(route('admin.client-requirements.index', [
            'draw' => 1,
            'start' => 0,
            'length' => 10,
        ]), ['X-Requested-With' => 'XMLHttpRequest']);

        $indexResponse->assertOk();
        $indexResponse->assertJsonPath('data.0.job_description_content', '<p>Build and maintain applications.</p>');
        $this->assertStringContainsString('view-job-description', $indexResponse->json('data.0.job_description_action'));
    }
}
