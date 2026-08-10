<?php

namespace Tests\Feature;

use App\Models\Candidate;
use App\Models\InterviewSchedule;
use App\Models\Recruiter;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InterviewScheduleVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_delivery_lead_sees_schedules_for_own_and_mapped_recruiters_only(): void
    {
        $role = Role::create(['name' => 'Recruiter DL', 'access_level' => 'recruiter_dl', 'status' => 1]);
        $deliveryLead = User::factory()->create(['email' => 'dl@example.test', 'role_id' => $role->id]);
        $otherDeliveryLead = User::factory()->create(['role_id' => $role->id]);

        $own = Recruiter::create(['recruiter_name' => 'DL', 'email' => 'DL@example.test', 'status' => 1]);
        $mapped = Recruiter::create(['recruiter_name' => 'Mapped', 'delivery_lead_user_id' => $deliveryLead->id, 'status' => 1]);
        $other = Recruiter::create(['recruiter_name' => 'Other', 'delivery_lead_user_id' => $otherDeliveryLead->id, 'status' => 1]);

        $ownSchedule = InterviewSchedule::create(['candidate_id' => Candidate::create(['candidate_name' => 'Own', 'recruiter_id' => $own->id])->id, 'status' => 'scheduled']);
        $mappedSchedule = InterviewSchedule::create(['candidate_id' => Candidate::create(['candidate_name' => 'Mapped', 'recruiter_id' => $mapped->id])->id, 'status' => 'scheduled']);
        InterviewSchedule::create(['candidate_id' => Candidate::create(['candidate_name' => 'Other', 'recruiter_id' => $other->id])->id, 'status' => 'scheduled']);

        $this->assertEqualsCanonicalizing(
            [$ownSchedule->id, $mappedSchedule->id],
            InterviewSchedule::visibleTo($deliveryLead->load('role'))->pluck('id')->all()
        );
    }

    public function test_recruiter_sees_only_their_candidate_schedules(): void
    {
        $role = Role::create(['name' => 'Recruiter', 'access_level' => 'recruiter', 'status' => 1]);
        $user = User::factory()->create(['email' => 'recruiter@example.test', 'role_id' => $role->id]);
        $own = Recruiter::create(['recruiter_name' => 'Own', 'email' => 'RECRUITER@example.test', 'status' => 1]);
        $other = Recruiter::create(['recruiter_name' => 'Other', 'status' => 1]);

        $visible = InterviewSchedule::create(['candidate_id' => Candidate::create(['candidate_name' => 'Own', 'recruiter_id' => $own->id])->id, 'status' => 'scheduled']);
        InterviewSchedule::create(['candidate_id' => Candidate::create(['candidate_name' => 'Other', 'recruiter_id' => $other->id])->id, 'status' => 'scheduled']);

        $this->assertSame(
            [$visible->id],
            InterviewSchedule::visibleTo($user->load('role'))->pluck('id')->all()
        );
    }
}
