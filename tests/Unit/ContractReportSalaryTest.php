<?php

namespace Tests\Unit;

use App\Http\Controllers\backend\ContractReportController;
use App\Models\Candidate;
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
            $method->invoke(new ContractReportController(), $candidate),
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
}
