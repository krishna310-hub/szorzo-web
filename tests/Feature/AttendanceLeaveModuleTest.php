<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceLeaveModuleTest extends TestCase
{
    use RefreshDatabase;

    private function role(string $access, array $permissions = []): Role
    {
        $role=Role::create(['name'=>ucwords(str_replace('_',' ',$access)),'access_level'=>$access,'status'=>1]);
        foreach($permissions as [$page,$name]) $role->permissions()->attach(Permission::firstOrCreate(['page'=>$page,'name'=>$name]));
        return $role;
    }

    private function user(Role $role,string $email): User
    {
        return User::factory()->create(['role_id'=>$role->id,'email'=>$email,'is_active'=>1]);
    }

    public function test_super_admin_is_excluded_from_daily_attendance(): void
    {
        $admin=$this->user($this->role('super_admin'),'admin@example.test');
        $employee=$this->user($this->role('staff',[['attendance','Read']]),'staff@example.test');
        $this->actingAs($admin)->get(route('admin.attendance.index'))->assertOk()->assertSee($employee->name)->assertDontSee($admin->email);
    }

    public function test_attendance_upserts_without_creating_duplicates(): void
    {
        $manager=$this->user($this->role('manager',[['attendance','Create'],['attendance','Edit']]),'manager@example.test');
        $employee=$this->user($this->role('staff'),'employee@example.test');
        $payload=['attendance_date'=>'2026-09-08','records'=>[['user_id'=>$employee->id,'status'=>'present','check_in'=>'09:00','check_out'=>'18:00','remarks'=>'Office']]];
        $this->actingAs($manager)->post(route('admin.attendance.store'),$payload)->assertSessionHas('success');
        $payload['records'][0]['status']='half_day';
        $this->actingAs($manager)->post(route('admin.attendance.store'),$payload)->assertSessionHas('success');
        $this->assertDatabaseCount('attendances',1); $this->assertDatabaseHas('attendances',['user_id'=>$employee->id,'status'=>'half_day']);
    }

    public function test_unauthorized_user_cannot_mark_attendance(): void
    {
        $viewer=$this->user($this->role('viewer',[['attendance','Read']]),'viewer@example.test');
        $employee=$this->user($this->role('staff'),'employee2@example.test');
        $this->actingAs($viewer)->post(route('admin.attendance.store'),['attendance_date'=>'2026-09-08','records'=>[['user_id'=>$employee->id,'status'=>'present']]])->assertForbidden();
    }

    public function test_conflicting_leave_request_is_rejected(): void
    {
        $user=$this->user($this->role('staff',[['leave','Create'],['leave','Read']]),'leave@example.test');
        LeaveRequest::create(['user_id'=>$user->id,'leave_type'=>'Casual Leave','from_date'=>'2026-09-10','to_date'=>'2026-09-12','number_of_days'=>3,'reason'=>'Existing']);
        $this->actingAs($user)->post(route('admin.leaves.store'),['leave_type'=>'Sick Leave','from_date'=>'2026-09-11','to_date'=>'2026-09-13','reason'=>'Overlap'])->assertSessionHasErrors('from_date');
        $this->assertDatabaseCount('leave_requests',1);
    }

    public function test_approval_creates_leave_attendance_for_every_date(): void
    {
        $manager=$this->user($this->role('manager',[['leave','Approve']]),'approver@example.test');
        $employee=$this->user($this->role('staff'),'leave-user@example.test');
        $leave=LeaveRequest::create(['user_id'=>$employee->id,'leave_type'=>'Earned Leave','from_date'=>'2026-09-10','to_date'=>'2026-09-12','number_of_days'=>3,'reason'=>'Trip']);
        $this->actingAs($manager)->put(route('admin.leaves.review',$leave),['status'=>'approved','review_remarks'=>'Approved'])->assertSessionHas('success');
        $this->assertDatabaseHas('leave_requests',['id'=>$leave->id,'status'=>'approved','reviewed_by'=>$manager->id]);
        $this->assertSame(3,Attendance::where('leave_request_id',$leave->id)->where('status','on_leave')->count());
    }
}
