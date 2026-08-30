<?php

namespace Tests\Unit;

use App\Http\Controllers\backend\ContractReportController;
use App\Models\Billing;
use App\Models\Candidate;
use App\Models\Client;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class ContractReportSalaryTest extends TestCase
{
    #[DataProvider('monthlySalaryCases')]
    public function test_monthly_salary_uses_individual_final_amount_and_percentage_billing(
        float $takeHome,
        float $annualCtc,
        float $billingPercentage,
        float $expected,
    ): void {
        $candidate = new Candidate([
            'take_home' => $takeHome,
            'onboarding_ctc' => $annualCtc,
        ]);
        $client = new Client();
        $client->setRelation('billing', new Billing(['value' => $billingPercentage]));
        $candidate->setRelation('client', $client);

        $method = new ReflectionMethod(ContractReportController::class, 'monthlyTakeHome');

        $this->assertSame(
            $expected,
            $method->invoke(new ContractReportController(), $candidate),
        );
    }

    public static function monthlySalaryCases(): array
    {
        return [
            'take-home 50,000 at 80 percent' => [50000, 0, 80, 40000.00],
            'annual CTC fallback at 75 percent' => [0, 1200000, 75, 75000.00],
            'fractional percentage rounds to paise' => [50000, 0, 80.25, 40125.00],
        ];
    }
}
