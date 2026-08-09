<?php

namespace Tests\Feature;

use App\Models\Recruiter;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecruiterVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_delivery_lead_sees_own_record_and_mapped_recruiters_only(): void
    {
        $role = Role::create([
            'name' => 'Recruiter DL',
            'access_level' => 'recruiter_dl',
            'status' => 1,
        ]);
        $deliveryLead = User::factory()->create([
            'email' => 'dl@example.test',
            'role_id' => $role->id,
        ]);
        $otherDeliveryLead = User::factory()->create(['role_id' => $role->id]);

        $own = Recruiter::create([
            'recruiter_name' => 'DL Record',
            'email' => 'DL@EXAMPLE.TEST',
            'status' => 1,
        ]);
        $mapped = Recruiter::create([
            'recruiter_name' => 'Mapped Recruiter',
            'delivery_lead_user_id' => $deliveryLead->id,
            'status' => 1,
        ]);
        Recruiter::create([
            'recruiter_name' => 'Other Team Recruiter',
            'delivery_lead_user_id' => $otherDeliveryLead->id,
            'status' => 1,
        ]);

        $this->assertEqualsCanonicalizing(
            [$own->id, $mapped->id],
            Recruiter::visibleTo($deliveryLead->load('role'))->pluck('id')->all()
        );
    }

    public function test_recruiter_sees_only_their_email_linked_record(): void
    {
        $role = Role::create([
            'name' => 'Recruiter',
            'access_level' => 'recruiter',
            'status' => 1,
        ]);
        $user = User::factory()->create([
            'email' => 'recruiter@example.test',
            'role_id' => $role->id,
        ]);

        $own = Recruiter::create([
            'recruiter_name' => 'Own Record',
            'email' => 'RECRUITER@example.test',
            'status' => 1,
        ]);
        Recruiter::create([
            'recruiter_name' => 'Another Recruiter',
            'email' => 'another@example.test',
            'delivery_lead_user_id' => $user->id,
            'status' => 1,
        ]);

        $this->assertSame(
            [$own->id],
            Recruiter::visibleTo($user->load('role'))->pluck('id')->all()
        );
    }
}
