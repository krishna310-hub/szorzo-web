<?php

namespace Tests\Feature;

use App\Models\ClientRequirement;
use App\Models\Recruiter;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientRequirementVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_recruiter_only_sees_requirements_mapped_to_their_email_linked_recruiter(): void
    {
        $role = Role::create([
            'id' => 3,
            'name' => 'Recruiter',
            'access_level' => 'recruiter',
            'status' => 1,
        ]);
        $user = User::factory()->create([
            'email' => 'recruiter@example.test',
            'role_id' => $role->id,
        ]);

        $mappedRecruiter = Recruiter::create([
            'recruiter_name' => 'Mapped Recruiter',
            'email' => 'RECRUITER@example.test',
            'status' => 1,
        ]);
        $otherRecruiter = Recruiter::create([
            'recruiter_name' => 'Other Recruiter',
            'email' => 'other@example.test',
            'status' => 1,
        ]);

        $mapped = ClientRequirement::create([
            'project_owner' => $mappedRecruiter->id,
            'project_owner_ids' => [$mappedRecruiter->id],
            'status' => 1,
        ]);
        ClientRequirement::create([
            'project_owner' => $otherRecruiter->id,
            'project_owner_ids' => [$otherRecruiter->id],
            'status' => 1,
        ]);

        $visibleIds = ClientRequirement::visibleTo($user)->pluck('id');

        $this->assertEquals([$mapped->id], $visibleIds->all());
    }

    public function test_delivery_lead_and_admin_roles_see_all_requirements(): void
    {
        ClientRequirement::create(['status' => 1]);
        ClientRequirement::create(['status' => 1]);

        foreach ([2, 1] as $roleId) {
            $role = Role::create([
                'id' => $roleId,
                'name' => $roleId === 2 ? 'Recruiter DL' : 'Super Admin',
                'access_level' => $roleId === 2 ? 'delivery_lead' : 'super_admin',
                'status' => 1,
            ]);
            $user = User::factory()->create(['role_id' => $role->id]);

            $this->assertCount(2, ClientRequirement::visibleTo($user)->get());
        }
    }
}
