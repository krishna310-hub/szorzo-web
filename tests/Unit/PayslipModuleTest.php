<?php

namespace Tests\Unit;

use App\Http\Controllers\backend\PayslipController;
use App\Models\Billing;
use App\Models\Candidate;
use App\Models\Client;
use App\Models\ClientRequirement;
use App\Models\ContractReport;
use App\Models\Employee;
use App\Models\JobRole;
use App\Models\Mode;
use App\Models\Role;
use App\Models\User;
use App\Services\PayslipService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PayslipModuleTest extends TestCase
{
    public function test_user_is_super_admin_detection(): void
    {
        $superAdminRole = new Role(['access_level' => 'super_admin']);
        $adminRole = new Role(['access_level' => 'admin']);
        $hyphenSuperAdminRole = new Role(['access_level' => 'super-admin']);
        $recruiterRole = new Role(['access_level' => 'recruiter']);

        $superUser = new User();
        $superUser->setRelation('role', $superAdminRole);
        $this->assertTrue($superUser->isSuperAdmin());
        $this->assertTrue($superUser->isAdmin());

        $adminUser = new User();
        $adminUser->setRelation('role', $adminRole);
        $this->assertTrue($adminUser->isSuperAdmin());

        $hyphenUser = new User();
        $hyphenUser->setRelation('role', $hyphenSuperAdminRole);
        $this->assertTrue($hyphenUser->isSuperAdmin());

        $recruiterUser = new User();
        $recruiterUser->setRelation('role', $recruiterRole);
        $this->assertFalse($recruiterUser->isSuperAdmin());
        $this->assertFalse($recruiterUser->isAdmin());
    }

    public function test_employee_employment_modes(): void
    {
        $contractMode = new Mode(['mode' => 'Contract']);
        $c2hMode = new Mode(['mode' => 'C2H']);
        $fteMode = new Mode(['mode' => 'Full Time']);

        $emp1 = new Employee();
        $emp1->setRelation('mode', $contractMode);
        $this->assertSame('Contract', $emp1->employment_mode);
        $this->assertTrue($emp1->isContract());
        $this->assertFalse($emp1->isFTE());

        $emp2 = new Employee();
        $emp2->setRelation('mode', $c2hMode);
        $this->assertSame('C2H', $emp2->employment_mode);
        $this->assertTrue($emp2->isC2H());
        $this->assertFalse($emp2->isContract());

        $emp3 = new Employee();
        $emp3->setRelation('mode', $fteMode);
        $this->assertSame('FTE', $emp3->employment_mode);
        $this->assertTrue($emp3->isFTE());
        $this->assertFalse($emp3->isContract());

        // Default when no mode assigned
        $empDefault = new Employee();
        $this->assertSame('FTE', $empDefault->employment_mode);
        $this->assertTrue($empDefault->isFTE());
    }

    public function test_payslip_service_for_fte_monthly(): void
    {
        $service = new PayslipService();

        $fteMode = new Mode(['mode' => 'Full Time']);
        $employee = new Employee([
            'employee_name' => 'Alice Johnson',
            'employee_no' => 'SZ101',
            'monthly_gross' => 50000.00,
            'basic_salary' => 25000.00,
            'hra' => 10000.00,
            'pf_deduction' => 1800.00,
            'pt_deduction' => 200.00,
        ]);
        $employee->setRelation('mode', $fteMode);
        $employee->setRelation('client', null);
        $employee->id = 101;

        // Full month August 2026 (31 days)
        $data = $service->getPayslipData($employee, 8, 2026, ['lop' => 0]);

        $this->assertSame('employee', $data['target_type']);
        $this->assertSame('FTE', $data['mode']);
        $this->assertTrue($data['is_full_calendar_month']);
        $this->assertSame(31, $data['period_days']);
        $this->assertSame(31.0, (float) $data['effective_work_days']);
        $this->assertSame(25000.0, (float) $data['earnings']['basic']['actual']);
        $this->assertSame(10000.0, (float) $data['earnings']['hra']['actual']);
        $this->assertSame(1800.0, (float) $data['deductions']['pf']['actual']);
        $this->assertSame(200.0, (float) $data['deductions']['pt']['actual']);
        $this->assertGreaterThan(0, $data['net_pay']);
    }

    public function test_payslip_service_for_contract_date_to_date(): void
    {
        $service = new PayslipService();

        $contractMode = new Mode(['mode' => 'Contract']);
        $employee = new Employee([
            'employee_name' => 'Bob Smith',
            'employee_no' => 'SZ202',
            'monthly_gross' => 60000.00,
            'basic_salary' => 30000.00,
            'hra' => 12000.00,
            'pf_deduction' => 1800.00,
            'pt_deduction' => 200.00,
        ]);
        $employee->setRelation('mode', $contractMode);
        $employee->setRelation('client', null);
        $employee->id = 202;

        // Date-to-Date for 15 days in August (31 days in month)
        $data = $service->getPayslipData($employee, null, null, [
            'from_date' => '2026-08-01',
            'to_date' => '2026-08-15',
            'lop' => 0,
        ]);

        $this->assertSame('Contract', $data['mode']);
        $this->assertFalse($data['is_full_calendar_month']);
        $this->assertSame(15, $data['period_days']);
        $this->assertSame(15.0, (float) $data['effective_work_days']);
        $this->assertStringContainsString('01-Aug-2026', $data['period_title']);
        $this->assertStringContainsString('15-Aug-2026', $data['period_title']);

        // Prorated: 15 / 31 * 30,000 = 14,516.13
        $expectedBasic = round(30000.00 * (15 / 31), 2);
        $this->assertSame($expectedBasic, (float) $data['earnings']['basic']['actual']);
    }

    public function test_payslip_service_for_c2h_with_lop(): void
    {
        $service = new PayslipService();

        $c2hMode = new Mode(['mode' => 'C2H']);
        $employee = new Employee([
            'employee_name' => 'Charlie Brown',
            'employee_no' => 'SZ303',
            'monthly_gross' => 40000.00,
            'basic_salary' => 20000.00,
            'hra' => 8000.00,
            'pf_deduction' => 1800.00,
            'pt_deduction' => 200.00,
        ]);
        $employee->setRelation('mode', $c2hMode);
        $employee->setRelation('client', null);
        $employee->id = 303;

        // Period 20 days with 2 days LOP -> effective work days = 18
        $data = $service->getPayslipData($employee, null, null, [
            'from_date' => '2026-09-01',
            'to_date' => '2026-09-20',
            'lop' => 2.0,
        ]);

        $this->assertSame('C2H', $data['mode']);
        $this->assertSame(20, $data['period_days']);
        $this->assertSame(2.0, (float) $data['lop']);
        $this->assertSame(18.0, (float) $data['effective_work_days']);

        // 18 days out of 30 days in September: ratio = 18 / 30 = 0.6
        $expectedBasic = round(20000.00 * (18 / 30));
        $this->assertSame($expectedBasic, (float) $data['earnings']['basic']['actual']);
    }

    public function test_inverted_dates_auto_corrected(): void
    {
        $service = new PayslipService();

        $emp = new Employee(['employee_name' => 'Test', 'monthly_gross' => 30000]);
        $emp->setRelation('mode', new Mode(['mode' => 'Contract']));
        $emp->setRelation('client', null);

        // Pass to_date earlier than from_date
        $data = $service->getPayslipData($emp, null, null, [
            'from_date' => '2026-08-20',
            'to_date' => '2026-08-01',
        ]);

        $this->assertSame('2026-08-01', $data['from_date']);
        $this->assertSame('2026-08-20', $data['to_date']);
        $this->assertSame(20, $data['period_days']);
    }

    public function test_payslip_service_for_system_user(): void
    {
        $service = new PayslipService();

        $role = new Role(['name' => 'HR Manager', 'access_level' => 'admin']);
        $user = new User([
            'name' => 'System Admin',
            'email' => 'admin@szorzo.com',
        ]);
        $user->setRelation('role', $role);
        $user->id = 1;

        // When salary is not updated, shows 0 on payslip without error
        $data = $service->getPayslipData($user, 7, 2026, ['lop' => 0]);

        $this->assertSame('user', $data['target_type']);
        $this->assertSame('System Admin', $data['name']);
        $this->assertSame('HR Manager', $data['designation']);
        $this->assertSame('SZ001', $data['employee_no']);
        $this->assertSame(0.0, (float) $data['net_pay']);

        // When salary is configured, calculates accurately
        $dataWithSalary = $service->getPayslipData($user, 7, 2026, ['monthly_salary' => 50000.0, 'lop' => 0]);
        $this->assertGreaterThan(0, $dataWithSalary['net_pay']);
    }

    public function test_helpers_format_amount_and_days(): void
    {
        $this->assertSame('00', PayslipService::formatAmount(0));
        $this->assertSame('00', PayslipService::formatAmount(null));
        $this->assertSame('5000', PayslipService::formatAmount(5000.0));
        $this->assertSame('5000.50', PayslipService::formatAmount(5000.50));

        $this->assertSame('0', PayslipService::formatDays(null));
        $this->assertSame('0', PayslipService::formatDays(0));
        $this->assertSame('15', PayslipService::formatDays(15.0));
        $this->assertSame('15.5', PayslipService::formatDays(15.5));
    }

    public function test_controller_filename_resolution(): void
    {
        $controller = new PayslipController(new PayslipService());
        $method = new ReflectionMethod($controller, 'resolveFilename');

        $monthlyData = [
            'is_full_calendar_month' => true,
            'employee_no' => 'SZ042',
            'month_name' => 'Aug',
            'year' => 2026,
            'from_date' => '2026-08-01',
            'to_date' => '2026-08-31',
        ];
        $this->assertSame('Payslip_SZ042_Aug_2026.pdf', $method->invoke($controller, $monthlyData));

        $rangeData = [
            'is_full_calendar_month' => false,
            'employee_no' => 'SZ042',
            'month_name' => 'Aug',
            'year' => 2026,
            'from_date' => '2026-08-01',
            'to_date' => '2026-08-15',
        ];
        $this->assertSame('Payslip_SZ042_20260801_to_20260815.pdf', $method->invoke($controller, $rangeData));
    }

    public function test_exact_contract_monthly_salary_zaheer(): void
    {
        $service = new PayslipService();

        $contractMode = new Mode(['mode' => 'Contract']);
        $client = new Client(['client' => 'Exyte Design & Engineering Services India Private Limited']);
        $jobRole = new JobRole(['job_role' => 'Electrical Design - REVIT']);

        $candidate = new Candidate([
            'candidate_name' => 'Mohammed Zaheer Ali',
            'email' => 'zaheerali.mohammed@gmail.com',
            'onboarding_ctc' => 138958.00 * 12,
            'is_hourly' => false,
        ]);
        $candidate->id = 1;
        $candidate->setRelation('mode', $contractMode);
        $candidate->setRelation('client', $client);
        $candidate->setRelation('jobRole', $jobRole);
        $candidate->setRelation('clientRequirement', null);

        // August 2026 (31 days), 7 absent days (present = 24)
        $data = $service->getPayslipData($candidate, 8, 2026, [
            'absent_days' => 7,
            'present_days' => 24,
        ]);

        $this->assertSame('candidate', $data['target_type']);
        $this->assertSame('Mohammed Zaheer Ali', $data['name']);
        $this->assertSame('Contract', $data['mode']);
        $this->assertSame(31, $data['days_in_month']);
        $this->assertSame(7.0, (float) $data['absent_days']);
        $this->assertSame(24.0, (float) $data['present_days']);
        $this->assertSame(138958.0, (float) $data['contract_intake']);

        // EXACT matching Contract Report screenshot: ₹107,580.39
        $this->assertSame(107580.39, (float) $data['payable_salary']);
        $this->assertSame(107580.39, (float) $data['net_pay']);
        $this->assertSame(107580.39, (float) $data['total_earnings_actual']);
        $this->assertSame(0.0, (float) $data['total_deductions_actual']);
    }

    public function test_exact_contract_monthly_salary_sowmya(): void
    {
        $service = new PayslipService();

        $contractMode = new Mode(['mode' => 'Contract']);
        $client = new Client(['client' => 'Exyte Design & Engineering Services India Private Limited']);
        $jobRole = new JobRole(['job_role' => 'Recruit Coordinator']);

        $candidate = new Candidate([
            'candidate_name' => 'Sowmya apannagary',
            'email' => 'sowmyaappannagari@gmail.com',
            'onboarding_ctc' => 43800.00 * 12,
            'is_hourly' => false,
        ]);
        $candidate->id = 3;
        $candidate->setRelation('mode', $contractMode);
        $candidate->setRelation('client', $client);
        $candidate->setRelation('jobRole', $jobRole);
        $candidate->setRelation('clientRequirement', null);

        // August 2026 (31 days), 0 absent days (present = 31)
        $data = $service->getPayslipData($candidate, 8, 2026, [
            'absent_days' => 0,
            'present_days' => 31,
        ]);

        $this->assertSame(43800.0, (float) $data['contract_intake']);
        // EXACT matching Contract Report screenshot: ₹43,800.00
        $this->assertSame(43800.0, (float) $data['payable_salary']);
        $this->assertSame(43800.0, (float) $data['net_pay']);
    }

    public function test_exact_contract_hourly_salary_farsham(): void
    {
        $service = new PayslipService();

        $contractMode = new Mode(['mode' => 'Contract']);
        $client = new Client(['client' => 'EDS Technologies Private Limited']);
        $jobRole = new JobRole(['job_role' => 'UI / UX']);
        $billing = new Billing(['value' => 35.00]);
        $requirement = new ClientRequirement();
        $requirement->setRelation('billing', $billing);

        $candidate = new Candidate([
            'candidate_name' => 'Muhammed Farsham V M',
            'email' => 'mdfarshamvm@gmail.com',
            'is_hourly' => true,
            'hourly_salary' => 400.00,
        ]);
        $candidate->id = 2;
        $candidate->setRelation('mode', $contractMode);
        $candidate->setRelation('client', $client);
        $candidate->setRelation('jobRole', $jobRole);
        $candidate->setRelation('clientRequirement', $requirement);

        // August 2026: 20 worked hours @ ₹400/hr, 35% billing margin
        $data = $service->getPayslipData($candidate, 8, 2026, [
            'worked_hours' => 20.00,
        ]);

        $this->assertTrue($data['is_hourly']);
        $this->assertSame(400.0, (float) $data['hourly_salary']);
        $this->assertSame(20.0, (float) $data['worked_hours']);
        $this->assertSame(8000.0, (float) $data['contract_intake']);
        $this->assertSame(2800.0, (float) $data['contract_revenue']);

        // EXACT matching Contract Report screenshot: ₹5,200.00
        $this->assertSame(5200.0, (float) $data['payable_salary']);
        $this->assertSame(5200.0, (float) $data['net_pay']);

        // Check that for hour-based external employee, all cells in 'Full' column are strictly 0.0 ('00')
        $this->assertSame(0.0, (float) $data['total_earnings_full']);
        $this->assertSame('00', PayslipService::formatAmount($data['total_earnings_full']));
        foreach ($data['earnings'] as $key => $earn) {
            $this->assertSame(0.0, (float) $earn['full'], "Full value for {$key} should be 0.0");
            $this->assertSame('00', PayslipService::formatAmount($earn['full']), "Formatted Full value for {$key} should be '00'");
        }
        $this->assertSame(2600.0, (float) $data['earnings']['basic']['actual']);
        $this->assertSame(1040.0, (float) $data['earnings']['hra']['actual']);
        $this->assertSame(1560.0, (float) $data['earnings']['special']['actual']);
    }

    public function test_hour_based_external_employee_payslip_all_full_cells_are_zero(): void
    {
        $service = new PayslipService();

        $contractMode = new Mode(['mode' => 'Contract']);
        $client = new Client(['client' => 'Affluent Technology']);
        $jobRole = new JobRole(['job_role' => 'Software Engineer']);
        $billing = new Billing(['value' => 35.00]);
        $requirement = new ClientRequirement();
        $requirement->setRelation('billing', $billing);

        $candidate = new Candidate([
            'candidate_name' => 'Hourly External Staff',
            'email' => 'hourly.staff@szorzo.com',
            'is_hourly' => true,
            'hourly_salary' => 200.00,
        ]);
        $candidate->id = 55;
        $candidate->setRelation('mode', $contractMode);
        $candidate->setRelation('client', $client);
        $candidate->setRelation('jobRole', $jobRole);
        $candidate->setRelation('clientRequirement', $requirement);

        // 276 hours worked @ ₹200/hr = ₹55,200 intake. 35% margin = ₹19,320. Payable Actual = ₹35,880
        $data = $service->getPayslipData($candidate, 8, 2026, [
            'worked_hours' => 276.00,
        ]);

        $this->assertTrue($data['is_hourly']);
        $this->assertSame(55200.0, (float) $data['contract_intake']);
        $this->assertSame(19320.0, (float) $data['contract_revenue']);
        $this->assertSame(35880.0, (float) $data['payable_salary']);
        $this->assertSame(35880.0, (float) $data['total_earnings_actual']);
        $this->assertSame(35880.0, (float) $data['net_pay']);

        // Check Actual breakdown matches user screenshot: Basic: 17940, HRA: 7176, Hourly Intake Share: 10764
        $this->assertSame(17940.0, (float) $data['earnings']['basic']['actual']);
        $this->assertSame(7176.0, (float) $data['earnings']['hra']['actual']);
        $this->assertSame('Hourly Intake Share', $data['earnings']['special']['label']);
        $this->assertSame(10764.0, (float) $data['earnings']['special']['actual']);

        // Verify that in Full, all cells are strictly 0 / '00' without error
        $this->assertSame(0.0, (float) $data['total_earnings_full']);
        $this->assertSame('00', PayslipService::formatAmount($data['total_earnings_full']));
        foreach ($data['earnings'] as $key => $earn) {
            $this->assertSame(0.0, (float) $earn['full'], "Earnings full for {$key} must be 0.0");
            $this->assertSame('00', PayslipService::formatAmount($earn['full']), "Formatted earnings full for {$key} must be '00'");
        }
    }

    public function test_exact_contract_hourly_salary_vignesh(): void
    {
        $service = new PayslipService();

        $contractMode = new Mode(['mode' => 'Contract']);
        $client = new Client(['client' => 'EDS Technologies Private Limited']);
        $jobRole = new JobRole(['job_role' => 'UI / UX']);
        $billing = new Billing(['value' => 35.00]);
        $requirement = new ClientRequirement();
        $requirement->setRelation('billing', $billing);

        $candidate = new Candidate([
            'candidate_name' => 'Vignesh Chandrasekaran',
            'email' => 'rv.vignesh@gmail.com',
            'is_hourly' => true,
            'hourly_salary' => 400.00,
        ]);
        $candidate->id = 4;
        $candidate->setRelation('mode', $contractMode);
        $candidate->setRelation('client', $client);
        $candidate->setRelation('jobRole', $jobRole);
        $candidate->setRelation('clientRequirement', $requirement);

        // August 2026: 168 worked hours @ ₹400/hr, 35% billing margin
        $data = $service->getPayslipData($candidate, 8, 2026, [
            'worked_hours' => 168.00,
        ]);

        $this->assertTrue($data['is_hourly']);
        $this->assertSame(400.0, (float) $data['hourly_salary']);
        $this->assertSame(168.0, (float) $data['worked_hours']);
        $this->assertSame(67200.0, (float) $data['contract_intake']);
        $this->assertSame(23520.0, (float) $data['contract_revenue']);

        // EXACT matching Contract Report screenshot: ₹43,680.00
        $this->assertSame(43680.0, (float) $data['payable_salary']);
        $this->assertSame(43680.0, (float) $data['net_pay']);
    }

    public function test_static_contract_calculation_helpers(): void
    {
        // Monthly helper matching Zaheer Ali
        $monthly = PayslipService::calculateContractMonthlySalary(138958.00, 7, 31);
        $this->assertSame(107580.39, $monthly['payable_salary']);
        $this->assertSame(31377.61, $monthly['leave_deduction']);

        // Hourly helper matching Farsham
        $hourly = PayslipService::calculateContractHourlySalary(400.00, 20.00, 35.00);
        $this->assertSame(8000.0, $hourly['gross_intake']);
        $this->assertSame(2800.0, $hourly['revenue_share']);
        $this->assertSame(5200.0, $hourly['payable_salary']);
    }

    public function test_payslip_view_and_controller_lists_only_employee_module_members(): void
    {
        $baseDir = dirname(__DIR__, 2);
        $viewPath = $baseDir . '/resources/views/backend/payslip/index.blade.php';
        $this->assertFileExists($viewPath);
        $viewContent = file_get_contents($viewPath);

        // Verify that candidates and system users optgroups and hidden inputs have been removed
        $this->assertStringNotContainsString('optgroup label="Contract Candidates', $viewContent);
        $this->assertStringNotContainsString('optgroup label="System Users', $viewContent);
        $this->assertStringNotContainsString('hiddenCandidateId', $viewContent);
        $this->assertStringNotContainsString('hiddenUserId', $viewContent);

        // Verify that only employee module list is present
        // Verify that the dropdown splits employees into Internal Users and External Users
        $this->assertStringContainsString('optgroup label="Internal Users - Szorzo employees"', $viewContent);
        $this->assertStringContainsString('optgroup label="External Users - Contract Employees"', $viewContent);
        $this->assertStringContainsString('Select Employee', $viewContent);
        $this->assertStringContainsString('name="employee_id"', $viewContent);
        $this->assertStringContainsString('$all_employees', $viewContent);

        // Verify controller only resolves Employee
        // Verify controller passes both internal and external employee collections
        $controllerPath = $baseDir . '/app/Http/Controllers/backend/PayslipController.php';
        $this->assertFileExists($controllerPath);
        $controllerContent = file_get_contents($controllerPath);

        $this->assertStringContainsString('resolveTarget(Request $request): Employee', $controllerContent);
        $this->assertStringNotContainsString('\'all_candidates\' => $allCandidates', $controllerContent);
        $this->assertStringNotContainsString('\'all_users\' => $allUsers', $controllerContent);
        $this->assertStringContainsString('\'internal_employees\' => $internalEmployees', $controllerContent);
        $this->assertStringContainsString('\'external_employees\' => $externalEmployees', $controllerContent);
    }

    public function test_employee_internal_and_external_categorization(): void
    {
        $contractMode = new Mode(['mode' => 'Contract']);
        $c2hMode = new Mode(['mode' => 'C2H']);
        $fteMode = new Mode(['mode' => 'Full Time']);

        // Internal FTE Employee
        $internalEmp = new Employee(['employee_name' => 'Internal Staff']);
        $internalEmp->setRelation('mode', $fteMode);
        $this->assertTrue($internalEmp->isInternal());
        $this->assertFalse($internalEmp->isExternal());
        $this->assertSame('Internal Users - Szorzo employees', $internalEmp->user_category);

        // Internal FTE Employee with SZORZO client assignment
        $szorzoClient = new Client(['client' => 'SZORZO Technologies Private Limited']);
        $internalSzorzoEmp = new Employee(['employee_name' => 'Triveni Panduranga', 'client_id' => 1]);
        $internalSzorzoEmp->setRelation('mode', $fteMode);
        $internalSzorzoEmp->setRelation('client', $szorzoClient);
        $this->assertTrue($internalSzorzoEmp->isInternal());
        $this->assertFalse($internalSzorzoEmp->isExternal());
        $this->assertSame('Internal Users - Szorzo employees', $internalSzorzoEmp->user_category);

        // External Contract Employee
        $externalContract = new Employee(['employee_name' => 'Contract Staff']);
        $externalContract->setRelation('mode', $contractMode);
        $this->assertTrue($externalContract->isExternal());
        $this->assertFalse($externalContract->isInternal());
        $this->assertSame('External Users - Contract Employees', $externalContract->user_category);

        // External C2H Employee
        $externalC2h = new Employee(['employee_name' => 'C2H Staff']);
        $externalC2h->setRelation('mode', $c2hMode);
        $this->assertTrue($externalC2h->isExternal());
        $this->assertFalse($externalC2h->isInternal());
        $this->assertSame('External Users - Contract Employees', $externalC2h->user_category);

        // Employee with client assignment is external
        // Employee with external client is external
        $exyteClient = new Client(['client' => 'Exyte Design & Engineering Services India Private Limited']);
        $externalClientEmp = new Employee(['employee_name' => 'Apannagary Sowmya', 'client_id' => 2]);
        $externalClientEmp->setRelation('mode', $contractMode);
        $externalClientEmp->setRelation('client', $exyteClient);
        $this->assertTrue($externalClientEmp->isExternal());
        $this->assertFalse($externalClientEmp->isInternal());
        $this->assertSame('External Users - Contract Employees', $externalClientEmp->user_category);

        // Employee with unresolvable client ID defaults to external in standalone tests
        $clientEmp = new Employee(['employee_name' => 'Client Staff', 'client_id' => 99]);
        $this->assertTrue($clientEmp->isExternal());
        $this->assertFalse($clientEmp->isInternal());
    }

    public function test_employee_self_service_access_and_security(): void
    {
        $controller = new PayslipController(new PayslipService());

        // Super Admin has management rights
        $superRole = new Role(['access_level' => 'super_admin']);
        $admin = new User(['name' => 'Admin User']);
        $admin->setRelation('role', $superRole);
        $this->assertTrue($controller->canManageAll($admin));

        // Regular Employee does NOT have management rights
        $employeeRole = new Role(['access_level' => 'employee']);
        $regularUser = new User([
            'name' => 'John Employee',
            'email' => 'john.emp@example.com',
            'is_active' => 1,
        ]);
        $regularUser->setRelation('role', $employeeRole);
        $this->assertFalse($controller->canManageAll($regularUser));

        // Mock authenticated user as regular employee
        $container = new \Illuminate\Container\Container();
        \Illuminate\Support\Facades\Facade::setFacadeApplication($container);
        $authMock = \Mockery::mock('stdClass');
        $authMock->shouldReceive('user')->andReturn($regularUser);
        $container->instance('auth', $authMock);

        $linkedEmp = new Employee([
            'employee_name' => 'John Employee',
            'employee_no' => 'SZ999',
            'official_mail' => 'john.emp@example.com',
            'monthly_gross' => 45000,
        ]);
        $regularUser->setRelation('employee', $linkedEmp);

        // Even if requesting another employee_id=123, resolveTarget strictly returns John Employee
        $request = new Request(['employee_id' => 123, 'month' => 5, 'year' => 2026]);
        $resolved = (new ReflectionMethod($controller, 'resolveTarget'))->invoke($controller, $request);

        $this->assertSame($linkedEmp, $resolved);
        $this->assertSame('John Employee', $resolved->employee_name);
    }

    public function test_views_and_navigation_support_employee_month_year_download(): void
    {
        $baseDir = dirname(__DIR__, 2);

        // 1. Payslip Index View
        $indexView = file_get_contents($baseDir . '/resources/views/backend/payslip/index.blade.php');
        $this->assertStringContainsString('Choose Month', $indexView);
        $this->assertStringContainsString('Choose Year', $indexView);
        $this->assertStringContainsString('Download PDF', $indexView);
        $this->assertStringContainsString('My Payslip', $indexView);
        $this->assertStringContainsString('updateDownloadLinks', $indexView);

        // 2. Sidebar Navigation
        $sidebarView = file_get_contents($baseDir . '/resources/views/backend/layouts/sidebar.blade.php');
        $this->assertStringContainsString('My Payslip', $sidebarView);
        $this->assertStringContainsString("route('admin.payslip.index')", $sidebarView);

        // 3. Header Dropdown Navigation
        $headerView = file_get_contents($baseDir . '/resources/views/backend/layouts/header.blade.php');
        $this->assertStringContainsString('My Payslip', $headerView);
        $this->assertStringContainsString("route('admin.payslip.index')", $headerView);
    }

    public function test_tenure_resolution_for_active_and_relieved_employees(): void
    {
        $controller = new PayslipController(new PayslipService());

        // 1. Relieved Employee: joined March 2026, relieved August 2026
        $relievedEmp = new Employee();
        $relievedEmp->setRawAttributes([
            'employee_name' => 'Relieved Staff',
            'date_of_joining' => '2026-03-15',
            'relieving_date' => '2026-08-20',
        ]);

        $relievedTenure = $controller->resolveTenure($relievedEmp);
        $this->assertTrue($relievedTenure['is_relieved']);
        $this->assertSame(2026, $relievedTenure['start_year']);
        $this->assertSame(3, $relievedTenure['start_month']);
        $this->assertSame(2026, $relievedTenure['end_year']);
        $this->assertSame(8, $relievedTenure['end_month']);
        $this->assertSame([2026], $relievedTenure['available_years']);
        $this->assertSame([3, 4, 5, 6, 7, 8], $relievedTenure['available_months_by_year'][2026]);

        // Months within tenure: March to August 2026
        $this->assertTrue($controller->isWithinTenure(3, 2026, $relievedTenure));
        $this->assertTrue($controller->isWithinTenure(8, 2026, $relievedTenure));

        // Months outside tenure: before March 2026 or after August 2026
        $this->assertFalse($controller->isWithinTenure(2, 2026, $relievedTenure));
        $this->assertFalse($controller->isWithinTenure(9, 2026, $relievedTenure));
        $this->assertFalse($controller->isWithinTenure(1, 2025, $relievedTenure));
        $this->assertFalse($controller->isWithinTenure(1, 2027, $relievedTenure));

        // 2. Active Employee: joined January 2026, no relieving date
        $activeEmp = new Employee();
        $activeEmp->setRawAttributes([
            'employee_name' => 'Active Staff',
            'date_of_joining' => '2026-01-10',
            'relieving_date' => null,
            'contract_to_date' => '2027-12-31', // Should not be treated as relieving date
        ]);

        $activeTenure = $controller->resolveTenure($activeEmp);
        $this->assertFalse($activeTenure['is_relieved']);
        $this->assertSame(2026, $activeTenure['start_year']);
        $this->assertSame(1, $activeTenure['start_month']);
        // Capped at latest completed month (previous month end)
        $latestCompleted = now()->subMonth();
        $this->assertSame((int) $latestCompleted->year, $activeTenure['end_year']);
        $this->assertSame((int) $latestCompleted->month, $activeTenure['end_month']);
        $this->assertSame((int) $latestCompleted->month, $activeTenure['default_month']);

        // Current ongoing month has not completed and cannot be accessed
        $this->assertFalse($controller->isWithinTenure((int) now()->month, (int) now()->year, $activeTenure));

        // Future dates beyond current date cannot be accessed
        $this->assertFalse($controller->isWithinTenure(12, 2027, $activeTenure));
    }

    public function test_employee_model_and_export_produce_dash_when_relieving_date_is_empty(): void
    {
        $activeEmp = new Employee();
        $activeEmp->setRawAttributes([
            'id' => 999,
            'employee_name' => 'Active Developer',
            'date_of_joining' => '2026-01-15',
            'relieving_date' => null,
            'contract_to_date' => '2027-04-22',
            'monthly_gross' => 50000,
        ]);
        $activeEmp->setRelation('client', null);
        $activeEmp->setRelation('candidate', null);
        $activeEmp->setRelation('mode', null);

        $details = $activeEmp->getInvestorProfileDetails();
        $this->assertSame('-', $details['relieving_date']);

        $export = new \App\Exports\EmployeeExport(collect([$activeEmp]));
        $row = $export->map($activeEmp);
        // Column index 6 is 'Date of Relieving (DOR)'
        $this->assertSame('-', $row[6]);
        $this->assertNotSame('Active / Ongoing', $row[6]);
        $this->assertNotSame('22-04-2027', $row[6]);
    }

    public function test_employee_with_pf_salary_slip_calculates_twelve_percent_pf(): void
    {
        $service = new PayslipService();
        $fteMode = new Mode(['mode' => 'Full Time']);

        // 1. Employee with salary_slip = 'PF' and gross = 40,000 -> PF statutory capped at 1,800
        $empWithExplicitBasic = new Employee([
            'id' => 601,
            'employee_name' => 'John Doe',
            'employee_no' => 'SZ601',
            'salary_slip' => 'PF',
            'monthly_gross' => 40000.0,
            'basic_salary' => 20000.0,
            'hra' => 10000.0,
            'special_allowance' => 10000.0,
        ]);
        $empWithExplicitBasic->setRelation('mode', $fteMode);
        $empWithExplicitBasic->setRelation('client', null);

        $data1 = $service->getPayslipData($empWithExplicitBasic, 10, 2026, ['lop' => 0]);
        $this->assertSame(20000.0, (float) $data1['earnings']['basic']['actual']);
        $this->assertSame(1800.0, (float) $data1['deductions']['pf']['actual']); // Statutory PF capped at 1,800

        // 1b. Employee with explicit pf_deduction = 2,400 respects explicit configuration
        $empWithExplicitPf = new Employee([
            'id' => 6012,
            'employee_name' => 'John Custom',
            'employee_no' => 'SZ6012',
            'salary_slip' => 'PF',
            'monthly_gross' => 40000.0,
            'basic_salary' => 20000.0,
            'pf_deduction' => 2400.0,
        ]);
        $empWithExplicitPf->setRelation('mode', $fteMode);
        $empWithExplicitPf->setRelation('client', null);
        $data1b = $service->getPayslipData($empWithExplicitPf, 10, 2026, ['lop' => 0, 'pf_deduction' => 2400.0]);
        $this->assertSame(2400.0, (float) $data1b['deductions']['pf']['actual']);

        // 2. Employee SZ006 with salary_slip = 'PF', PF No, and blank salary (uses standard default 38,050 gross, 19,025 basic -> statutory PF capped at 1,800)
        $shenbagaEmp = new Employee([
            'id' => 6,
            'employee_name' => 'Shenbagaseetharaman M',
            'employee_no' => 'SZ006',
            'designation' => 'Recruiter DL',
            'employment_type' => 'Internal',
            'salary_slip' => 'PF',
            'bank_uan_pf_number' => '100999013974',
            'monthly_gross' => null, // Left blank
            'basic_salary' => null,  // Left blank
        ]);
        $shenbagaEmp->setRelation('mode', $fteMode);
        $shenbagaEmp->setRelation('client', null);

        $data2 = $service->getPayslipData($shenbagaEmp, 10, 2026, ['lop' => 0]);
        $this->assertSame('SZ006', $data2['employee_no']);
        $this->assertSame('100999013974', $data2['pf_no']);
        $this->assertSame(38050.0, (float) $data2['total_earnings_full']);
        $this->assertSame(38050.0, (float) $data2['total_earnings_actual']);
        $this->assertSame(19025.0, (float) $data2['earnings']['basic']['actual']);
        $this->assertSame(1800.0, (float) $data2['deductions']['pf']['actual']); // Statutory PF capped at 1,800
        $this->assertSame(200.0, (float) $data2['deductions']['pt']['actual']);
        $this->assertSame(2000.0, (float) $data2['total_deductions_actual']);
        $this->assertSame(36050.0, (float) $data2['net_pay']);

        // 3. Employee with salary_slip = 'TDS' has 0.00 PF deduction and fixed 10% on Income Tax
        $tdsEmp = new Employee([
            'id' => 603,
            'employee_name' => 'Consultant A',
            'employee_no' => 'SZ603',
            'salary_slip' => 'TDS',
            'monthly_gross' => 50000.0,
            'basic_salary' => 25000.0,
        ]);
        $tdsEmp->setRelation('mode', $fteMode);
        $tdsEmp->setRelation('client', null);

        $data3 = $service->getPayslipData($tdsEmp, 10, 2026, ['lop' => 0]);
        $this->assertSame(0.0, (float) $data3['deductions']['pf']['actual']);
        $this->assertSame('00', PayslipService::formatAmount($data3['deductions']['pf']['actual']));
        $this->assertSame(5000.0, (float) $data3['deductions']['income_tax']['actual']); // Fixed 10% TDS
        $this->assertSame(5000.0, (float) $data3['total_deductions_actual']);
        $this->assertSame(45000.0, (float) $data3['net_pay']);

        // 4. Employee with PF number mentioned in bank_uan_pf_number gets 12% PF
        $empWithPfNumber = new Employee([
            'id' => 604,
            'employee_name' => 'Staff Member',
            'employee_no' => 'SZ604',
            'bank_uan_pf_number' => '101234567890',
            'monthly_gross' => 30000.0,
            'basic_salary' => 15000.0,
        ]);
        $empWithPfNumber->setRelation('mode', $fteMode);
        $empWithPfNumber->setRelation('client', null);

        $data4 = $service->getPayslipData($empWithPfNumber, 10, 2026, ['lop' => 0]);
        $this->assertSame(15000.0, (float) $data4['earnings']['basic']['actual']);
        $this->assertSame(1800.0, (float) $data4['deductions']['pf']['actual']); // 12% of 15,000 = 1,800
    }

    public function test_pf_calculation_twelve_percent_of_actual_gross_for_gross_33600(): void
    {
        $service = new PayslipService();
        $contractMode = new Mode(['mode' => 'Contract']);

        // Employee with Gross Earnings: ₹33,600
        // Actual Basic: ₹16,800 (50% of gross)
        // PF: 12% of Total Actual Earnings capped at statutory 1,800: min(1800, 33600 * 12%) = 1800
        // Professional Tax: ₹200 (since gross > 15,000)
        // Total Deductions: ₹2,000
        // Net Pay: ₹31,600
        $emp = new Employee();
        $emp->setRawAttributes([
            'id' => 336,
            'employee_name' => 'Karun Kumar',
            'employee_no' => 'SZ 012',
            'employment_type' => 'External',
            'salary_slip' => 'PF',
            'monthly_gross' => 33600.0,
        ]);
        $emp->setRelation('mode', $contractMode);
        $emp->setRelation('client', null);

        $data = $service->getPayslipData($emp, 10, 2026, ['lop' => 0]);

        $this->assertSame(33600.0, (float) $data['total_earnings_actual']);
        $this->assertSame(16800.0, (float) $data['earnings']['basic']['actual']);
        $this->assertSame(1800.0, (float) $data['deductions']['pf']['actual']);
        $this->assertSame(200.0, (float) $data['deductions']['pt']['actual']);
        $this->assertSame(2000.0, (float) $data['total_deductions_actual']);
        $this->assertSame(31600.0, (float) $data['net_pay']);
    }

    public function test_sowmya_november_twenty_four_days_payslip_corrects_pf_from_2439(): void
    {
        $service = new PayslipService();
        $contractMode = new Mode(['mode' => 'Contract']);

        // Payslip shown in user uploaded screenshot:
        // Period: 01-Nov-2026 to 24-Nov-2026 (24 days in 30-day month)
        // Employee No: SZ 006, Name: Appannagary Sowmya
        // Full Gross: 42,000 -> Actual Gross: 33,600 (42,000 * 24 / 30)
        // Full Basic: 21,000 -> Actual Basic: 16,800
        // Full HRA: 8,400 -> Actual HRA: 6,720
        // Full Special: 12,600 -> Actual Special: 10,080
        // PF Calculation:
        // WRONG stored profile: 2,439
        // CORRECT statutory calculation: 12% on actual total earnings capped at 1,800
        // Professional Tax: 200
        // Total Deductions: 1,800 + 200 = 2,000
        // Net Pay: 33,600 - 2,000 = 31,600
        $sowmya = new Employee();
        $sowmya->setRawAttributes([
            'id' => 6,
            'employee_name' => 'Appannagary Sowmya',
            'employee_no' => 'SZ 006',
            'employment_type' => 'External',
            'salary_slip' => 'PF',
            'monthly_gross' => 42000.0,
            'basic_salary' => 21000.0,
            'hra' => 8400.0,
            'special_allowance' => 12600.0,
            'pf_deduction' => 2439.0, // Stale/erroneous value stored on profile must NOT override dynamic formula!
            'bank_uan_pf_number' => '101918324788',
        ]);
        $sowmya->setRelation('mode', $contractMode);
        $sowmya->setRelation('client', null);

        $data = $service->getPayslipData($sowmya, 11, 2026, [
            'from_date' => '2026-11-01',
            'to_date' => '2026-11-24',
        ]);

        $this->assertSame(24, $data['period_days']);
        $this->assertSame(42000.0, (float) $data['total_earnings_full']);
        $this->assertSame(33600.0, (float) $data['total_earnings_actual']);
        $this->assertSame(21000.0, (float) $data['earnings']['basic']['full']);
        $this->assertSame(16800.0, (float) $data['earnings']['basic']['actual']);
        $this->assertSame(8400.0, (float) $data['earnings']['hra']['full']);
        $this->assertSame(6720.0, (float) $data['earnings']['hra']['actual']);
        $this->assertSame(12600.0, (float) $data['earnings']['special']['full']);
        $this->assertSame(10080.0, (float) $data['earnings']['special']['actual']);

        // Corrected PF deduction: statutory cap of 1,800 (not stale 2,439)
        $this->assertSame(1800.0, (float) $data['deductions']['pf']['actual']);
        $this->assertNotSame(2439.0, (float) $data['deductions']['pf']['actual']);

        $this->assertSame(200.0, (float) $data['deductions']['pt']['actual']);
        $this->assertSame(2000.0, (float) $data['total_deductions_actual']);
        $this->assertSame(31600.0, (float) $data['net_pay']);
    }

    public function test_appanagary_sowmya_payslips_june_july_august(): void
    {
        $service = new PayslipService();
        $contractMode = new Mode(['mode' => 'Contract']);
        $exyteClient = new Client(['client' => 'Exyte Design & Engineering Services India Private Limited']);

        $sowmya = new Employee();
        $sowmya->setRawAttributes([
            'id' => 3,
            'employee_name' => 'Appanagary Sowmya',
            'employee_no' => 'SZ003',
            'employment_type' => 'External',
            'salary_slip' => 'PF',
            'monthly_gross' => 42000.0,
            'pf_deduction' => 1800.0,
            'contract_from_date' => '2026-06-29',
            'contract_to_date' => '2026-09-29',
            'date_of_joining' => '2026-06-29',
        ]);
        $sowmya->setRelation('mode', $contractMode);
        $sowmya->setRelation('client', $exyteClient);

        // 1. June - 2 days (June 29 to June 30)
        // Basic: 1400, HRA: 560, Special Allowance: 840, Gross: 2800, PF: 336, PT: 0, Net payable: 2464
        // Automatically calculated without needing pf_deduction override!
        $juneData = $service->getPayslipData($sowmya, 6, 2026);
        $this->assertSame(2, $juneData['period_days']);
        $this->assertSame(1400.0, (float) $juneData['earnings']['basic']['actual']);
        $this->assertSame(560.0, (float) $juneData['earnings']['hra']['actual']);
        $this->assertSame(840.0, (float) $juneData['earnings']['special']['actual']);
        $this->assertSame(2800.0, (float) $juneData['total_earnings_actual']);
        $this->assertSame(336.0, (float) $juneData['deductions']['pf']['actual']);
        $this->assertSame(0.0, (float) $juneData['deductions']['pt']['actual']);
        $this->assertSame(336.0, (float) $juneData['total_deductions_actual']);
        $this->assertSame(2464.0, (float) $juneData['net_pay']);

        // 2. July - 31 days
        // Basic: 21000, HRA: 8400, Special Allowance: 12600, Gross: 42000, PF: 1800, PT: 200, Net payable: 40000
        // Automatically calculated without needing pf_deduction override!
        $julyData = $service->getPayslipData($sowmya, 7, 2026);
        $this->assertSame(31, $julyData['period_days']);
        $this->assertSame(21000.0, (float) $julyData['earnings']['basic']['actual']);
        $this->assertSame(8400.0, (float) $julyData['earnings']['hra']['actual']);
        $this->assertSame(12600.0, (float) $julyData['earnings']['special']['actual']);
        $this->assertSame(42000.0, (float) $julyData['total_earnings_actual']);
        $this->assertSame(1800.0, (float) $julyData['deductions']['pf']['actual']);
        $this->assertSame(200.0, (float) $julyData['deductions']['pt']['actual']);
        $this->assertSame(2000.0, (float) $julyData['total_deductions_actual']);
        $this->assertSame(40000.0, (float) $julyData['net_pay']);

        // 3. August - 31 days
        // Basic: 21000, HRA: 8400, Special Allowance: 12600, Gross: 42000, PF: 1800, PT: 200, Net payable: 40000
        // Automatically calculated without needing pf_deduction override!
        $aug31Data = $service->getPayslipData($sowmya, 8, 2026);
        $this->assertSame(31, $aug31Data['period_days']);
        $this->assertSame(21000.0, (float) $aug31Data['earnings']['basic']['actual']);
        $this->assertSame(8400.0, (float) $aug31Data['earnings']['hra']['actual']);
        $this->assertSame(12600.0, (float) $aug31Data['earnings']['special']['actual']);
        $this->assertSame(42000.0, (float) $aug31Data['total_earnings_actual']);
        $this->assertSame(1800.0, (float) $aug31Data['deductions']['pf']['actual']);
        $this->assertSame(200.0, (float) $aug31Data['deductions']['pt']['actual']);
        $this->assertSame(2000.0, (float) $aug31Data['total_deductions_actual']);
        $this->assertSame(40000.0, (float) $aug31Data['net_pay']);

        // 4. August - 30 days
        // Basic: 21000, HRA: 8400, Special Allowance: 12600, Gross: 42000, PF: 2136, PT: 200, Net payable: 39664
        $aug30Data = $service->getPayslipData($sowmya, 8, 2026, ['days' => 30, 'pf_deduction' => 2136.0]);
        $this->assertSame(21000.0, (float) $aug30Data['earnings']['basic']['actual']);
        $this->assertSame(8400.0, (float) $aug30Data['earnings']['hra']['actual']);
        $this->assertSame(12600.0, (float) $aug30Data['earnings']['special']['actual']);
        $this->assertSame(42000.0, (float) $aug30Data['total_earnings_actual']);
        $this->assertSame(2136.0, (float) $aug30Data['deductions']['pf']['actual']);
        $this->assertSame(200.0, (float) $aug30Data['deductions']['pt']['actual']);
        $this->assertSame(2336.0, (float) $aug30Data['total_deductions_actual']);
        $this->assertSame(39664.0, (float) $aug30Data['net_pay']);

        // Also test August 30 days via date range with arrear
        $augRangeData = $service->getPayslipData($sowmya, 8, 2026, [
            'from_date' => '2026-08-01',
            'to_date' => '2026-08-30',
            'days' => 30,
            'pf_deduction' => 2136.0,
        ]);
        $this->assertSame(21000.0, (float) $augRangeData['earnings']['basic']['actual']);
        $this->assertSame(8400.0, (float) $augRangeData['earnings']['hra']['actual']);
        $this->assertSame(12600.0, (float) $augRangeData['earnings']['special']['actual']);
        $this->assertSame(42000.0, (float) $augRangeData['total_earnings_actual']);
        $this->assertSame(2136.0, (float) $augRangeData['deductions']['pf']['actual']);
        $this->assertSame(200.0, (float) $augRangeData['deductions']['pt']['actual']);
        $this->assertSame(39664.0, (float) $augRangeData['net_pay']);
    }

    public function test_tds_employee_shows_fixed_ten_percent_on_income_tax(): void
    {
        $service = new PayslipService();
        $contractMode = new Mode(['mode' => 'Contract']);
        $edsClient = new Client(['client' => 'EDS Technologies Private Limited']);

        // Vignesh Chandrasekaran (from user screenshot: External, TDS)
        $tdsEmp = new Employee([
            'id' => 8,
            'employee_name' => 'Vignesh Chandrasekaran',
            'employee_no' => 'SZ 008',
            'employment_type' => 'External',
            'salary_slip' => 'TDS',
            'monthly_gross' => 45000.0,
        ]);
        $tdsEmp->setRelation('mode', $contractMode);
        $tdsEmp->setRelation('client', $edsClient);

        $data = $service->getPayslipData($tdsEmp, 8, 2026);
        $this->assertSame(45000.0, (float) $data['total_earnings_actual']);
        $this->assertSame(0.0, (float) $data['deductions']['pf']['actual']);
        $this->assertSame('00', PayslipService::formatAmount($data['deductions']['pf']['actual']));
        $this->assertSame(4500.0, (float) $data['deductions']['income_tax']['actual']); // Fixed 10% TDS
        $this->assertSame('4500', PayslipService::formatAmount($data['deductions']['income_tax']['actual']));
        $this->assertSame(4500.0, (float) $data['total_deductions_actual']);
        $this->assertSame(40500.0, (float) $data['net_pay']);
    }

    /**
     * Verify Kavita S Ichalal's August 2026 payslip matches row 5 of "szorzo - Salary Sheet - Aug 2026.xlsx" exactly:
     * - Days in month: 31
     * - Days Payable: 15.5
     * - Basic + DA: 10,000 (from 20,000 full)
     * - Med Allnce: 1,100 (from 2,200 full)
     * - Gross Salary: 11,100 (from 22,200 full)
     * - PF 12%: 1,200 (12% of Basic 10,000)
     * - PT: 0 (Gross <= 15,000)
     * - Total Deductions: 1,200
     * - Net Salary Payable: 9,900
     */
    public function test_kavita_s_ichalal_august_2026_payslip_matches_salary_sheet_exactly(): void
    {
        $service = new PayslipService();
        $fteMode = new Mode(['mode' => 'Full Time']);

        $kavita = new Employee([
            'id' => 3,
            'employee_name' => 'Kavita S Ichalal',
            'employee_no' => 'SZ 005',
            'father_name' => 'Sanganna Ichalal',
            'designation' => 'Recruiter',
            'location' => 'Bangalore',
            'bank_uan_pf_number' => 'BGMRD36445920000010004',
            'employee_uan_pf_number' => '101778447580',
            'salary_slip' => 'PF',
            'monthly_gross' => 22200.00,
            'basic_salary' => 20000.00,
            'medical_allowance' => 2200.00,
        ]);
        $kavita->setRelation('mode', $fteMode);
        $kavita->setRelation('client', null);

        // 1. With absent_days = 15.5 (31 days in month, Days Payable = 15.5)
        $data = $service->getPayslipData($kavita, 8, 2026, [
            'absent_days' => 15.5,
        ]);

        $this->assertSame(31, $data['period_days']);
        $this->assertSame(15.5, (float) $data['lop']);
        $this->assertSame(15.5, (float) $data['effective_work_days']);
        $this->assertSame(20000.0, (float) $data['earnings']['basic']['full']);
        $this->assertSame(10000.0, (float) $data['earnings']['basic']['actual']);
        $this->assertSame(2200.0, (float) $data['earnings']['medical']['full']);
        $this->assertSame(1100.0, (float) $data['earnings']['medical']['actual']);
        $this->assertSame(22200.0, (float) $data['total_earnings_full']);
        $this->assertSame(11100.0, (float) $data['total_earnings_actual']);

        // Statutory Deductions: PF is 12% of Basic (10,000 * 0.12 = 1,200), not gross
        $this->assertSame(1200.0, (float) $data['deductions']['pf']['actual']);
        $this->assertSame(0.0, (float) $data['deductions']['pt']['actual']);
        $this->assertSame(1200.0, (float) $data['total_deductions_actual']);

        // Net Salary Payable
        $this->assertSame(9900.0, (float) $data['net_pay']);

        // 2. Direct Days Payable override: days_payable = 15.5
        $dataPayable = $service->getPayslipData($kavita, 8, 2026, [
            'days_payable' => 15.5,
        ]);
        $this->assertSame(15.5, (float) $dataPayable['effective_work_days']);
        $this->assertSame(15.5, (float) $dataPayable['lop']);
        $this->assertSame(10000.0, (float) $dataPayable['earnings']['basic']['actual']);
        $this->assertSame(1100.0, (float) $dataPayable['earnings']['medical']['actual']);
        $this->assertSame(11100.0, (float) $dataPayable['total_earnings_actual']);
        $this->assertSame(1200.0, (float) $dataPayable['deductions']['pf']['actual']);
        $this->assertSame(9900.0, (float) $dataPayable['net_pay']);

        // 3. Date-to-Date range (01-Aug-2026 to 31-Aug-2026 with absent_days = 15.5)
        $dataRange = $service->getPayslipData($kavita, 8, 2026, [
            'from_date' => '2026-08-01',
            'to_date' => '2026-08-31',
            'absent_days' => 15.5,
        ]);
        $this->assertSame(15.5, (float) $dataRange['effective_work_days']);
        $this->assertSame(15.5, (float) $dataRange['lop']);
        $this->assertSame(10000.0, (float) $dataRange['earnings']['basic']['actual']);
        $this->assertSame(1100.0, (float) $dataRange['earnings']['medical']['actual']);
        $this->assertSame(11100.0, (float) $dataRange['total_earnings_actual']);
        $this->assertSame(1200.0, (float) $dataRange['deductions']['pf']['actual']);
        $this->assertSame(9900.0, (float) $dataRange['net_pay']);

        // 4. Full month verification (31 days, 0 LOP)
        $fullMonth = $service->getPayslipData($kavita, 8, 2026, ['lop' => 0]);
        $this->assertSame(31.0, (float) $fullMonth['effective_work_days']);
        $this->assertSame(20000.0, (float) $fullMonth['earnings']['basic']['actual']);
        $this->assertSame(2200.0, (float) $fullMonth['earnings']['medical']['actual']);
        $this->assertSame(22200.0, (float) $fullMonth['total_earnings_actual']);
        $this->assertSame(1800.0, (float) $fullMonth['deductions']['pf']['actual']); // Statutory capped at 1,800
        $this->assertSame(200.0, (float) $fullMonth['deductions']['pt']['actual']);   // Gross > 15,000 => 200
        $this->assertSame(2000.0, (float) $fullMonth['total_deductions_actual']);
        $this->assertSame(20200.0, (float) $fullMonth['net_pay']);
    }
}
