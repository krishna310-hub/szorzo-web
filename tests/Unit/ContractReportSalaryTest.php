<?php

namespace Tests\Unit;

use App\Http\Controllers\backend\ContractReportController;
use App\Models\Billing;
use App\Models\Candidate;
use App\Models\ClientRequirement;
use App\Models\ContractReport;
use Illuminate\Database\Eloquent\Collection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class ContractReportSalaryTest extends TestCase
{
    #[DataProvider('monthlySalaryCases')]
    public function test_monthly_salary_uses_take_home_without_applying_client_billing(
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
            'take-home is the monthly salary' => [48333, 580000, 48333.00],
            'annual CTC is divided by twelve when take-home is missing' => [0, 1200000, 100000.00],
            'negative salary values cannot produce a negative report salary' => [-1, -120000, 0.00],
        ];
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
            'worked_hours' => $workedHours,
            'monthly_take_home' => $monthlyTakeHome,
        ]);
        $report->setRelation('candidate', $candidate);

        return $report;
    }
}
