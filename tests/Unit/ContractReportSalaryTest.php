<?php

namespace Tests\Unit;

use App\Http\Controllers\backend\AdminController;
use App\Http\Controllers\backend\ContractReportController;
use App\Http\Controllers\backend\RevenueController;
use App\Models\Billing;
use App\Models\Candidate;
use App\Models\Client;
use App\Models\ClientRequirement;
use App\Models\ContractReport;
use Illuminate\Database\Eloquent\Collection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class ContractReportSalaryTest extends TestCase
{
    #[DataProvider('monthlySalaryCases')]
    public function test_monthly_salary_uses_onboarding_ctc_divided_by_twelve(
        float $takeHome,
        float $annualCtc,
        float $expected,
    ): void {
        $candidate = new Candidate([
            'take_home' => $takeHome,
            'onboarding_ctc' => $annualCtc,
        ]);
        $method = new ReflectionMethod(ContractReportController::class, 'monthlyTakeHome');

        $this->assertSame(
            $expected,
            $method->invoke(new ContractReportController, $candidate),
        );
    }

    public static function monthlySalaryCases(): array
    {
        return [
            'take-home is ignored' => [48333, 525600, 43800.00],
            'annual CTC is divided by twelve when take-home is missing' => [0, 1200000, 100000.00],
            'negative salary values cannot produce a negative report salary' => [-1, -120000, 0.00],
        ];
    }

    public function test_salary_is_the_complement_of_the_revenue_percentage(): void
    {
        $controller = new ContractReportController;
        $salaryMethod = new ReflectionMethod($controller, 'salaryShare');

        $payableSalary = $salaryMethod->invoke($controller, 8000.00, 35);
        $revenue = round(8000.00 * 35 / 100, 2);

        $this->assertSame(5200.00, $payableSalary);
        $this->assertSame(2800.00, $revenue);
        $this->assertSame(8000.00, $payableSalary + $revenue);
        $this->assertSame(0.01, $salaryMethod->invoke($controller, 0.03, 50));
    }

    public function test_monthly_total_salary_keeps_the_full_monthly_amount_while_hourly_keeps_the_revenue_split(): void
    {
        $controller = new ContractReportController;
        $method = new ReflectionMethod($controller, 'totalSalary');

        $this->assertSame(138958.00, $method->invoke($controller, 138958, 15, false));
        $this->assertSame(54400.00, $method->invoke($controller, 64000, 15, true));
    }

    public function test_contract_invoice_adds_each_selected_candidates_salary_and_revenue(): void
    {
        $client = new Client(['client' => 'Example Client']);
        $client->id = 10;
        $first = $this->invoiceCandidate($client, 138958.00, 15);
        $second = $this->invoiceCandidate($client, 48333.00, 15);
        $method = new ReflectionMethod(RevenueController::class, 'calculatedData');

        $invoice = $method->invoke(new RevenueController, [
            'invoice_type' => 'contract',
            'contract_month' => '2026-09',
            'gst_percentage' => 18,
        ], collect([$first, $second]));

        $this->assertSame(187291.00, $invoice['onboarding_ctc']);
        $this->assertSame(30.0, $invoice['billing_percentage']);
        $this->assertSame(28093.65, $invoice['service_amount']);
        $this->assertSame(5056.86, $invoice['gst_amount']);
        $this->assertSame(33150.51, $invoice['total_amount']);
    }

    public function test_revenue_form_recalculates_fresh_contract_payable_values(): void
    {
        $method = new ReflectionMethod(RevenueController::class, 'contractPayableSalary');
        $monthly = new ContractReport;
        $monthly->setRawAttributes([
            'salary_month' => '2026-01-01',
            'monthly_take_home' => 138958,
            'is_hourly' => false,
            'absent_days' => 0,
            'payable_salary' => 0,
        ], true);
        $hourly = new ContractReport;
        $hourly->setRawAttributes([
            'salary_month' => '2026-09-01',
            'is_hourly' => true,
            'hourly_salary' => 400,
            'worked_hours' => 160,
            'payable_salary' => 0,
        ], true);

        $this->assertSame(138958.00, $method->invoke(new RevenueController, $monthly));
        $this->assertSame(64000.00, $method->invoke(new RevenueController, $hourly));
    }

    public function test_revenue_uses_each_candidates_explicitly_mapped_requirement(): void
    {
        $first = $this->reportWithRequirement(28.57, 400, true, 150);
        $second = $this->reportWithRequirement(35, 400, true, 160);
        $monthly = $this->reportWithRequirement(15, 0, false, 0, 48333);
        $method = new ReflectionMethod(ContractReportController::class, 'addRevenueMetrics');

        $method->invoke(new ContractReportController, new Collection([$first, $second, $monthly]));

        $this->assertSame(28.57, $first->revenue_percentage);
        $this->assertSame(17142.0, $first->contract_revenue);
        $this->assertSame(35.0, $second->revenue_percentage);
        $this->assertSame(22400.0, $second->contract_revenue);
        $this->assertSame(7249.95, $monthly->contract_revenue);
    }

    public function test_dashboard_uses_the_contract_report_revenue_calculation(): void
    {
        $monthly = $this->reportWithRequirement(15, 0, false, 0, 48333);
        $monthly->salary_month = '2026-07-01';
        $monthly->absent_days = 0;
        $monthly->candidate->onboarding_ctc = 1667496;
        $monthly->candidate->take_home = 48333;

        $hourly = $this->reportWithRequirement(35, 400, true, 56);
        $hourly->salary_month = '2026-07-01';
        $hourly->candidate->hourly_salary = 400;

        $method = new ReflectionMethod(AdminController::class, 'contractReportRevenue');
        $controller = new AdminController;

        $this->assertSame(20843.70, $method->invoke($controller, $monthly));
        $this->assertSame(7840.00, $method->invoke($controller, $hourly));
    }

    public function test_dashboard_onboarded_revenue_matches_the_june_external_fte_calculation(): void
    {
        $controller = new AdminController;
        $method = new ReflectionMethod($controller, 'candidateRevenue');
        $billing = new Billing(['value' => 8.33]);
        $requirement = new ClientRequirement;
        $requirement->setRelation('billing', $billing);
        $externalClient = new Client(['client' => 'External Client']);

        $externalRevenue = collect([1300000, 2100000, 1500000, 4600000, 2900000])
            ->sum(function (int $ctc) use ($externalClient, $requirement, $method, $controller) {
                $candidate = new Candidate([
                    'mode_id' => 1,
                    'level_of_interview_id' => 20,
                    'onboarding_ctc' => $ctc,
                ]);
                $candidate->setRelation('client', $externalClient);
                $candidate->setRelation('clientRequirement', $requirement);

                return $method->invoke($controller, $candidate);
            });

        $internal = new Candidate([
            'mode_id' => 1,
            'level_of_interview_id' => 20,
            'onboarding_ctc' => 360000,
            'expected_ctc' => 350000,
        ]);
        $internal->setRelation('client', new Client(['client' => 'SZORZO Technologies Private Limited']));

        $this->assertSame(1032920.0, $externalRevenue);
        $this->assertSame(0.0, $method->invoke($controller, $internal));
        $this->assertSame(1032920.0, $externalRevenue + $method->invoke($controller, $internal));
    }

    public function test_dashboard_does_not_mix_contract_revenue_into_onboarded_revenue(): void
    {
        $candidate = new Candidate([
            'mode_id' => 2,
            'level_of_interview_id' => 20,
            'onboarding_ctc' => 1667500,
        ]);
        $method = new ReflectionMethod(AdminController::class, 'candidateRevenue');

        $this->assertSame(0.0, $method->invoke(new AdminController, $candidate));
    }

    public function test_dashboard_calculates_offer_declined_revenue_from_expected_ctc(): void
    {
        $billing = new Billing(['value' => 10]);
        $requirement = new ClientRequirement;
        $requirement->setRelation('billing', $billing);
        $candidate = new Candidate([
            'mode_id' => 1,
            'level_of_interview_id' => 22,
            'expected_ctc' => 600000,
            'onboarding_ctc' => null,
        ]);
        $candidate->setRelation('client', new Client(['client' => 'External Client']));
        $candidate->setRelation('clientRequirement', $requirement);
        $method = new ReflectionMethod(AdminController::class, 'candidateRevenue');

        $this->assertSame(60000.0, $method->invoke(new AdminController, $candidate));
    }

    public function test_dashboard_calculates_offer_declined_revenue_from_requirement_ctc_when_candidate_ctc_is_zero(): void
    {
        $billing = new Billing(['value' => 8.33]);
        $requirement = new ClientRequirement(['ctc' => 1200000]);
        $requirement->setRelation('billing', $billing);
        $candidate = new Candidate([
            'mode_id' => 1,
            'level_of_interview_id' => 22,
            'expected_ctc' => 0,
            'onboarding_ctc' => 0,
        ]);
        $candidate->setRelation('client', new Client(['client' => 'External Client']));
        $candidate->setRelation('clientRequirement', $requirement);
        $method = new ReflectionMethod(AdminController::class, 'candidateRevenue');

        $this->assertSame(99960.0, $method->invoke(new AdminController, $candidate));
    }

    private function reportWithRequirement(
        float $billingPercentage,
        float $requirementCtc,
        bool $isHourly,
        float $workedHours,
        float $monthlyTakeHome = 0,
    ): ContractReport {
        $billing = new Billing(['value' => $billingPercentage]);
        $requirement = new ClientRequirement(['ctc' => $requirementCtc]);
        $requirement->setRelation('billing', $billing);
        $candidate = new Candidate;
        $candidate->setRelation('clientRequirement', $requirement);
        $report = new ContractReport([
            'is_hourly' => $isHourly,
            'hourly_salary' => $isHourly ? $requirementCtc : null,
            'worked_hours' => $workedHours,
            'monthly_take_home' => $monthlyTakeHome,
            'payable_salary' => $isHourly
                ? $requirementCtc * $workedHours
                : $monthlyTakeHome,
        ]);
        $report->setRelation('candidate', $candidate);

        return $report;
    }

    private function invoiceCandidate(Client $client, float $salary, float $billingPercentage): Candidate
    {
        $billing = new Billing(['value' => $billingPercentage]);
        $requirement = new ClientRequirement(['mode_id' => 2]);
        $requirement->setRelation('billing', $billing);
        $candidate = new Candidate([
            'candidate_name' => 'Candidate '.number_format($salary, 0, '.', ''),
            'mode_id' => 2,
            'client_id' => $client->id,
        ]);
        $candidate->setAttribute('contract_invoice_base', $salary);
        $candidate->setAttribute('contract_invoice_service', round($salary * $billingPercentage / 100, 2));
        $candidate->setRelation('clientRequirement', $requirement);
        $candidate->setRelation('client', $client);

        return $candidate;
    }
}
