<?php

namespace Tests\Unit;

use App\Http\Requests\StoreLeaveRequest;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\Role;
use App\Models\User;
use App\Notifications\LeaveStatusNotification;
use App\Policies\LeaveRequestPolicy;
use PHPUnit\Framework\TestCase;

class LeaveRequestModuleTest extends TestCase
{
    public function test_leave_request_constants_and_fillables(): void
    {
        $this->assertContains('Casual Leave', LeaveRequest::TYPES);
        $this->assertContains('Sick Leave', LeaveRequest::TYPES);
        $this->assertContains('Earned Leave', LeaveRequest::TYPES);
        $this->assertContains('Unpaid Leave', LeaveRequest::TYPES);
        $this->assertContains('pending', LeaveRequest::STATUSES);
        $this->assertContains('approved', LeaveRequest::STATUSES);
        $this->assertContains('rejected', LeaveRequest::STATUSES);

        $leave = new LeaveRequest();
        $this->assertContains('reason', $leave->getFillable());
        $this->assertContains('review_remarks', $leave->getFillable());
        $this->assertContains('reviewed_by', $leave->getFillable());
        $this->assertContains('reviewed_at', $leave->getFillable());
        $this->assertContains('attachment', $leave->getFillable());
    }

    public function test_leave_status_notification_structure(): void
    {
        $notif = new LeaveStatusNotification(
            title: 'New Leave Request Received',
            message: 'John Doe requested Casual Leave for 2 days. Reason: Fever',
            actionUrl: '/admin/leaves?status=pending',
            status: 'pending',
            leaveId: 42,
            leaveType: 'Casual Leave',
            actionType: 'leave_request',
            userId: 15
        );

        $user = new User();
        $user->id = 15;
        $this->assertSame(['database'], $notif->via($user));
        $data = $notif->toArray($user);

        $this->assertSame('New Leave Request Received', $data['title']);
        $this->assertSame('pending', $data['status']);
        $this->assertSame(42, $data['leave_id']);
        $this->assertSame('Casual Leave', $data['leave_type']);
        $this->assertSame('leave_request', $data['action_type']);
        $this->assertSame(15, $data['user_id']);
        $this->assertSame(15, $data['notifiable_id']);
        $this->assertStringContainsString('Fever', $data['message']);
    }

    public function test_store_leave_request_validation_rules(): void
    {
        $request = new StoreLeaveRequest();
        $rules = $request->rules();

        $this->assertArrayHasKey('reason', $rules);
        $this->assertContains('required', $rules['reason']);
        $this->assertContains('min:5', $rules['reason']);

        $this->assertArrayHasKey('from_date', $rules);
        $this->assertContains('required', $rules['from_date']);

        $this->assertArrayHasKey('to_date', $rules);
        $this->assertContains('after_or_equal:from_date', $rules['to_date']);

        $messages = $request->messages();
        $this->assertArrayHasKey('reason.required', $messages);
        $this->assertArrayHasKey('reason.min', $messages);
    }

    public function test_user_internal_employee_classification(): void
    {
        // 1. Investor is not an internal employee
        $investorRole = new Role(['access_level' => 'investor', 'name' => 'Investor']);
        $investorUser = new User(['role_id' => 6, 'is_active' => 1]);
        $investorUser->setRelation('role', $investorRole);
        $this->assertFalse($investorUser->isInternalEmployee());

        // 2. Inactive user is not an internal employee
        $staffRole = new Role(['access_level' => 'staff', 'name' => 'Recruiter']);
        $inactiveUser = new User(['role_id' => 2, 'is_active' => 0]);
        $inactiveUser->setRelation('role', $staffRole);
        $this->assertFalse($inactiveUser->isInternalEmployee());

        // 3. Active internal staff is internal employee
        $activeUser = new User(['role_id' => 2, 'is_active' => 1]);
        $activeUser->setRelation('role', $staffRole);
        $this->assertTrue($activeUser->isInternalEmployee());

        // 4. Linked external employee makes isInternalEmployee false
        $extEmployee = new Employee([
            'employee_name' => 'External Contractor',
            'employment_type' => 'External',
        ]);
        $externalUser = new User(['role_id' => 2, 'is_active' => 1, 'email' => 'ext@example.com']);
        $externalUser->setRelation('role', $staffRole);
        $externalUser->setRelation('employee', $extEmployee);
        $this->assertTrue($extEmployee->isExternal());
        $this->assertFalse($extEmployee->isInternal());
        $this->assertFalse($externalUser->isInternalEmployee());
    }

    public function test_leave_request_policy_enforcement(): void
    {
        $policy = new LeaveRequestPolicy();

        // Super Admin
        $adminRole = new Role(['access_level' => 'super_admin', 'name' => 'Super Admin']);
        $adminUser = new User(['role_id' => 1, 'is_active' => 1]);
        $adminUser->setRelation('role', $adminRole);

        // Super Admin has full access without access denied
        $this->assertTrue($policy->create($adminUser));
        $this->assertTrue($policy->before($adminUser, 'create'));
        $this->assertTrue($policy->approve($adminUser));
        $this->assertTrue($policy->read($adminUser));

        // Active Internal Employee
        $staffRole = new Role(['access_level' => 'staff', 'name' => 'Recruiter']);
        $staffUser = new User(['role_id' => 2, 'is_active' => 1]);
        $staffUser->setRelation('role', $staffRole);

        // Internal staff can create and read
        $this->assertTrue($policy->create($staffUser));
        $this->assertTrue($policy->read($staffUser));

        // Investor cannot create leave request
        $investorRole = new Role(['access_level' => 'investor', 'name' => 'Investor']);
        $investorUser = new User(['role_id' => 6, 'is_active' => 1]);
        $investorUser->setRelation('role', $investorRole);
        $this->assertFalse($policy->create($investorUser));

        // External employee cannot create leave request
        $extEmployee = new Employee(['employee_name' => 'Vendor Staff', 'employment_type' => 'External']);
        $extUser = new User(['role_id' => 2, 'is_active' => 1]);
        $extUser->setRelation('role', $staffRole);
        $extUser->setRelation('employee', $extEmployee);
        $this->assertFalse($policy->create($extUser));
    }

    public function test_dashboard_petition_amount_section_at_bottom(): void
    {
        $baseDir = dirname(__DIR__, 2);
        $dashboardContent = file_get_contents($baseDir . '/resources/views/backend/index.blade.php');

        // Confirm Petition Amount section is in the dashboard
        $this->assertStringContainsString('Petition Amount (Petty Cash Ledger)', $dashboardContent);

        // Verify that Petition Amount appears AFTER the monthlyRevenueBarChart (at bottom of dashboard)
        $chartPos = strpos($dashboardContent, 'monthlyRevenueBarChart');
        $pettyPos = strpos($dashboardContent, 'Petition Amount (Petty Cash Ledger)');

        $this->assertNotFalse($chartPos);
        $this->assertNotFalse($pettyPos);
        $this->assertGreaterThan($chartPos, $pettyPos, 'Petition Amount section should appear after charts at bottom of dashboard');
    }

    public function test_leave_views_contain_required_flows_and_reason_fields(): void
    {
        $baseDir = dirname(__DIR__, 2);
        $createView = file_get_contents($baseDir . '/resources/views/backend/leaves/create.blade.php');
        $indexView = file_get_contents($baseDir . '/resources/views/backend/leaves/index.blade.php');

        // Create view requires reason
        $this->assertStringContainsString('name="reason"', $createView);
        $this->assertStringContainsString('required', $createView);
        $this->assertStringContainsString('from_date', $createView);
        $this->assertStringContainsString('to_date', $createView);

        // Index view has reject modal with reason textarea required
        $this->assertStringContainsString('id="rejectLeaveModal"', $indexView);
        $this->assertStringContainsString('name="review_remarks"', $indexView);
        $this->assertStringContainsString('required', $indexView);
        $this->assertStringContainsString('id="approveLeaveModal"', $indexView);

        // Apply for leave button is hidden in admin login
        $this->assertStringContainsString('!$canApprove && !auth()->user()->isSuperAdmin()', $indexView);
    }

    public function test_leave_types_does_not_contain_other(): void
    {
        $this->assertNotContains('Other', LeaveRequest::TYPES);
        $this->assertEquals(['Casual Leave', 'Sick Leave', 'Earned Leave', 'Unpaid Leave'], LeaveRequest::TYPES);
    }

    public function test_employee_export_maps_empty_relieving_date_to_dash(): void
    {
        $baseDir = dirname(__DIR__, 2);
        $exportContent = file_get_contents($baseDir . '/app/Exports/EmployeeExport.php');

        $this->assertStringContainsString("\$dor = \$employee->relieving_date\n            ? \$employee->relieving_date->format('d-m-Y')\n            : '-';", $exportContent);
        $this->assertStringNotContainsString('Active / Ongoing', $exportContent);
    }

    public function test_investor_profile_view_hides_szorzo_share_for_investor(): void
    {
        $baseDir = dirname(__DIR__, 2);
        $showView = file_get_contents($baseDir . '/resources/views/backend/investers/show.blade.php');

        $this->assertStringContainsString('@if(!$isInvestor)', $showView);
        $this->assertStringContainsString('SZ Share (70%)', $showView);
    }
}
