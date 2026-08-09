<?php

namespace Tests\Feature;

use App\Models\Candidate;
use App\Models\Recruiter;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CandidateVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_delivery_lead_sees_candidates_for_own_and_mapped_recruiters_only(): void
    {
        $role = Role::create(['name' => 'Recruiter DL', 'access_level' => 'delivery_lead', 'status' => 1]);
        $deliveryLead = User::factory()->create(['email' => 'dl@example.test', 'role_id' => $role->id]);
        $otherDeliveryLead = User::factory()->create(['role_id' => $role->id]);

        $own = Recruiter::create(['recruiter_name' => 'DL', 'email' => 'DL@example.test', 'status' => 1]);
        $mapped = Recruiter::create(['recruiter_name' => 'Team Recruiter', 'delivery_lead_user_id' => $deliveryLead->id, 'status' => 1]);
        $other = Recruiter::create(['recruiter_name' => 'Other Team', 'delivery_lead_user_id' => $otherDeliveryLead->id, 'status' => 1]);

        $visibleOwn = Candidate::create(['candidate_name' => 'Own Candidate', 'recruiter_id' => $own->id]);
        $visibleMapped = Candidate::create(['candidate_name' => 'Team Candidate', 'recruiter_id' => $mapped->id]);
        Candidate::create(['candidate_name' => 'Other Candidate', 'recruiter_id' => $other->id]);

        $this->assertEqualsCanonicalizing(
            [$visibleOwn->id, $visibleMapped->id],
            Candidate::visibleTo($deliveryLead->load('role'))->pluck('id')->all()
        );
    }

    public function test_recruiter_sees_only_candidates_for_their_own_record(): void
    {
        $role = Role::create(['name' => 'Recruiter', 'access_level' => 'recruiter', 'status' => 1]);
        $user = User::factory()->create(['email' => 'recruiter@example.test', 'role_id' => $role->id]);
        $own = Recruiter::create(['recruiter_name' => 'Own', 'email' => 'RECRUITER@example.test', 'status' => 1]);
        $other = Recruiter::create(['recruiter_name' => 'Other', 'email' => 'other@example.test', 'status' => 1]);

        $visible = Candidate::create(['candidate_name' => 'Own Candidate', 'recruiter_id' => $own->id]);
        Candidate::create(['candidate_name' => 'Other Candidate', 'recruiter_id' => $other->id]);

        $this->assertSame(
            [$visible->id],
            Candidate::visibleTo($user->load('role'))->pluck('id')->all()
        );
    }

    public function test_super_admin_sees_all_candidates(): void
    {
        $role = Role::create(['name' => 'Super Admin', 'access_level' => 'super_admin', 'status' => 1]);
        $admin = User::factory()->create(['role_id' => $role->id]);
        Candidate::create(['candidate_name' => 'First']);
        Candidate::create(['candidate_name' => 'Second']);

        $this->assertCount(2, Candidate::visibleTo($admin->load('role'))->get());
    }
}
