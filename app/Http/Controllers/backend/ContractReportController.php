<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\ContractReport;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ContractReportController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('read', ContractReport::class);
        $month = $this->month($request);
        $contractType = $this->contractType($request);
        $reports = $this->query($request, $month, $contractType)->paginate(50)->withQueryString();

        $reports->getCollection()->each(function (ContractReport $report) use ($month) {
            $this->syncReportSalary($report, $month->daysInMonth);
        });
        $this->addRevenueMetrics($reports->getCollection());

        return view('backend.contract-reports.index', [
            'month' => $month,
            'daysInMonth' => $month->daysInMonth,
            'reports' => $reports,
            'contractType' => $contractType,
        ]);
    }

    public function refresh(Request $request)
    {
        $this->authorize('export', ContractReport::class);
        $month = $this->month($request);
        $contractType = $this->contractType($request);

        $isPrevious = $month->startOfMonth()->lt(CarbonImmutable::now()->startOfMonth());

        // For current and future months, prune contract reports where candidate contract is inactive
        if (! $isPrevious) {
            ContractReport::whereDate('salary_month', $month->toDateString())
                ->whereDoesntHave('candidate', fn ($q) => $this->applyActiveContractFilter(
                    $q->visibleTo($request->user()->loadMissing('role')),
                    $month,
                    false
                ))
                ->delete();
        }

        $candidates = $this->contractCandidates($request)
            ->get([
                'candidates.id',
                'candidates.client_id',
                'candidates.take_home',
                'candidates.onboarding_ctc',
                'candidates.is_hourly',
                'candidates.hourly_salary',
            ]);
        $created = 0;

        foreach ($candidates as $candidate) {
            $monthlyTakeHome = $this->monthlyTakeHome($candidate);
            $report = ContractReport::firstOrCreate(
                ['candidate_id' => $candidate->id, 'salary_month' => $month->toDateString()],
                [
                    'monthly_take_home' => $monthlyTakeHome,
                    'is_hourly' => $candidate->is_hourly,
                    'hourly_salary' => $candidate->is_hourly ? $candidate->hourly_salary : null,
                    'present_days' => $month->daysInMonth,
                    'absent_days' => 0,
                    'worked_hours' => $candidate->is_hourly ? 0 : null,
                    'payable_salary' => $candidate->is_hourly ? 0 : $monthlyTakeHome,
                ]
            );

            // Apply the configured revenue/salary split to new and existing rows.
            $this->syncReportSalary($report, $month->daysInMonth);

            $created += $report->wasRecentlyCreated ? 1 : 0;
        }

        $updated = $candidates->count() - $created;

        return redirect()->route('admin.contract-reports.index', [
            'month' => $month->format('Y-m'),
            'contract_type' => $contractType,
        ])
            ->with('success', $created.' contract candidate(s) added and '.$updated.' updated for '.$month->format('F Y').'.');
    }

    public function update(Request $request, ContractReport $contractReport)
    {
        $this->authorize('export', ContractReport::class);
        $this->ensureVisible($request, $contractReport);
        $days = $contractReport->salary_month->daysInMonth;
        $data = $request->validate([
            'present_days' => ['required', 'integer', 'min:0', 'max:'.$days],
            'absent_days' => ['required', 'integer', 'min:0', 'max:'.$days],
            'worked_hours' => [
                $contractReport->is_hourly ? 'required' : 'nullable',
                'numeric',
                'min:0',
            ],
        ]);

        if ($days !== $data['present_days'] + $data['absent_days']) {
            throw ValidationException::withMessages([
                'present_days' => 'Present and leave days must total '.$days.' days for '.$contractReport->salary_month->format('F Y').'.',
            ]);
        }

        $contractReport->loadMissing('candidate.clientRequirement.billing');
        $grossIntake = $contractReport->is_hourly
            ? $this->hourlyPayableSalary(
                (float) $contractReport->hourly_salary,
                (float) $data['worked_hours'],
            )
            : $this->payableSalary(
                (float) $contractReport->monthly_take_home,
                $data['absent_days'],
                $days
            );

        $contractReport->update([
            ...$data,
            'worked_hours' => $contractReport->is_hourly ? round((float) $data['worked_hours'], 2) : null,
            'payable_salary' => $this->totalSalary(
                $grossIntake,
                $this->revenuePercentage($contractReport),
                (bool) $contractReport->is_hourly,
            ),
        ]);

        return back()->with('success', 'Monthly attendance and salary updated.');
    }

    public function pdf(Request $request)
    {
        $this->authorize('export', ContractReport::class);
        $month = $this->month($request);
        $contractType = $this->contractType($request);
        $reports = $this->query($request, $month, $contractType)->get();

        $reports->each(function (ContractReport $report) use ($month) {
            $this->syncReportSalary($report, $month->daysInMonth);
        });
        $this->addRevenueMetrics($reports);

        return Pdf::loadView('backend.contract-reports.pdf', compact('month', 'reports', 'contractType'))
            ->setPaper('a4', 'landscape')
            ->download('contract-report-'.$month->format('Y-m').'.pdf');
    }

    public function invoice(Request $request, ContractReport $contractReport, \App\Services\PayslipService $payslipService)
    {
        $this->authorize('export', ContractReport::class);
        $this->ensureVisible($request, $contractReport);
        $contractReport->load(['candidate.clientRequirement.billing', 'candidate.client', 'candidate.jobRole', 'candidate.recruiter']);
        $this->syncReportSalary($contractReport, $contractReport->salary_month->daysInMonth);
        $this->addRevenueMetrics(collect([$contractReport]));

        $invoiceNumber = sprintf(
            'CR-%s-%05d',
            $contractReport->salary_month->format('Ym'),
            $contractReport->id
        );
        $candidateName = preg_replace(
            '/[^A-Za-z0-9_-]+/',
            '-',
            $contractReport->candidate?->candidate_name ?? 'candidate'
        );

        $payslipData = $payslipService->getPayslipData(
            $contractReport->candidate, 
            $contractReport->salary_month->month, 
            $contractReport->salary_month->year
        );

        return Pdf::loadView('backend.contract-reports.invoice', array_merge($payslipData, compact('contractReport', 'invoiceNumber')))
            ->setPaper('a4', 'portrait')
            ->download($invoiceNumber.'-'.trim($candidateName, '-').'.pdf');
    }

    private function month(Request $request): CarbonImmutable
    {
        $data = $request->validate(['month' => ['nullable', 'date_format:Y-m']]);

        return isset($data['month'])
            ? CarbonImmutable::createFromFormat('!Y-m', $data['month'])->startOfMonth()
            : CarbonImmutable::now()->startOfMonth();
    }

    private function contractType(Request $request): string
    {
        $data = $request->validate([
            'contract_type' => ['nullable', 'in:all,monthly,hourly'],
        ]);

        return $data['contract_type'] ?? 'all';
    }

    private function contractCandidates(Request $request)
    {
        $month = $this->month($request);

        $query = Candidate::query()
            ->with('client.billing')
            ->visibleTo($request->user()->loadMissing('role'));

        return $this->applyActiveContractFilter($query, $month);
    }

    private function monthlyTakeHome(Candidate $candidate): float
    {
        return round(max(0, (float) $candidate->onboarding_ctc / 12), 2);
    }

    private function syncReportSalary(ContractReport $report, int $daysInMonth): void
    {
        $report->loadMissing('candidate.clientRequirement.billing');

        if (! $report->candidate) {
            return;
        }

        $monthlyTakeHome = $this->monthlyTakeHome($report->candidate);
        $grossIntake = $report->candidate->is_hourly
            ? $this->hourlyPayableSalary(
                (float) $report->candidate->hourly_salary,
                (float) ($report->worked_hours ?? 0),
            )
            : $this->payableSalary(
                $monthlyTakeHome,
                (int) $report->absent_days,
                $daysInMonth
            );

        $report->fill([
            'monthly_take_home' => $monthlyTakeHome,
            'is_hourly' => $report->candidate->is_hourly,
            'hourly_salary' => $report->candidate->is_hourly
                ? $report->candidate->hourly_salary
                : null,
            'worked_hours' => $report->candidate->is_hourly
                ? ($report->worked_hours ?? 0)
                : null,
            'payable_salary' => $this->totalSalary(
                $grossIntake,
                $this->revenuePercentage($report),
                (bool) $report->candidate->is_hourly,
            ),
        ]);

        if ($report->isDirty(['monthly_take_home', 'is_hourly', 'hourly_salary', 'worked_hours', 'payable_salary'])) {
            $report->save();
        }
    }

    private function payableSalary(float $monthlySalary, int $leaveDays, int $daysInMonth): float
    {
        $leaveDeduction = ($monthlySalary / $daysInMonth) * $leaveDays;

        return round(max(0, $monthlySalary - $leaveDeduction), 2);
    }

    private function hourlyPayableSalary(float $hourlySalary, float $workedHours): float
    {
        return round(max(0, $hourlySalary * $workedHours), 2);
    }

    private function salaryShare(float $grossIntake, float $revenuePercentage): float
    {
        $revenuePercentage = min(100, max(0, $revenuePercentage));
        $grossIntake = round(max(0, $grossIntake), 2);
        $revenueShare = round($grossIntake * $revenuePercentage / 100, 2);

        // Subtract the rounded revenue so salary + revenue always reconciles
        // exactly to the displayed gross intake, including one-paise edge cases.
        return round($grossIntake - $revenueShare, 2);
    }

    private function totalSalary(float $grossIntake, float $revenuePercentage, bool $isHourly): float
    {
        return $isHourly
            ? $this->salaryShare($grossIntake, $revenuePercentage)
            : round(max(0, $grossIntake), 2);
    }

    private function revenuePercentage(ContractReport $report): float
    {
        return (float) ($report->candidate?->clientRequirement?->billing?->value ?? 0);
    }

    private function addRevenueMetrics($reports): void
    {
        $reports->each(function (ContractReport $report) {
            $candidate = $report->candidate;
            $requirement = $candidate?->clientRequirement;
            $billingAmountPerHour = $report->is_hourly ? (float) ($report->hourly_salary ?? 0) : null;
            $revenuePercentage = min(100, max(0, (float) ($requirement?->billing?->value ?? 0)));
            $grossIntake = $report->is_hourly
                ? $this->hourlyPayableSalary(
                    (float) ($report->hourly_salary ?? 0),
                    (float) ($report->worked_hours ?? 0)
                )
                : $this->payableSalary(
                    (float) ($report->monthly_take_home ?? 0),
                    (int) ($report->absent_days ?? 0),
                    max(1, $report->salary_month->daysInMonth)
                );
            $revenuePerHour = $report->is_hourly
                ? round(((float) $report->hourly_salary * $revenuePercentage) / 100, 2)
                : null;
            // The configured billing percentage is the company's share of the
            // gross intake. The complementary percentage is candidate salary.
            $totalRevenue = round($grossIntake * $revenuePercentage / 100, 2);

            $report->setAttribute('billing_amount_per_hour', $billingAmountPerHour);
            $report->setAttribute('revenue_percentage', $revenuePercentage);
            $report->setAttribute('revenue_per_hour', $revenuePerHour);
            $report->setAttribute('contract_intake', $grossIntake);
            $report->setAttribute('contract_revenue', $totalRevenue);
        });
    }

    private function query(Request $request, CarbonImmutable $month, string $contractType)
    {
        $isPrevious = $month->startOfMonth()->lt(CarbonImmutable::now()->startOfMonth());

        return ContractReport::query()
            ->with(['candidate.clientRequirement.billing', 'candidate.client', 'candidate.jobRole', 'candidate.recruiter'])
            ->whereDate('salary_month', $month->toDateString())
            ->when($contractType === 'monthly', fn ($query) => $query->where('is_hourly', false))
            ->when($contractType === 'hourly', fn ($query) => $query->where('is_hourly', true))
            ->whereHas('candidate', function ($candidateQuery) use ($request, $month, $isPrevious) {
                $visible = $candidateQuery->visibleTo($request->user()->loadMissing('role'));

                if ($isPrevious) {
                    // For previous months where this person already worked, show their existing report!
                    return $visible->where(function ($q) use ($month) {
                        $q->where(function ($sq) use ($month) {
                            $this->applyActiveContractFilter($sq, $month, true);
                        })->orWhere(function ($histQ) use ($month) {
                            $histQ->where('candidates.mode_id', 2)
                                  ->whereHas('contractReports', fn ($cr) => $cr->whereDate('salary_month', $month->toDateString()));
                        });
                    });
                }

                // For current and future months, contract must be active
                return $this->applyActiveContractFilter($visible, $month, false);
            })
            ->orderBy(Candidate::select('candidate_name')->whereColumn('candidates.id', 'contract_reports.candidate_id'));
    }

    private function ensureVisible(Request $request, ContractReport $report): void
    {
        $month = $report->salary_month->toImmutable();
        $isPrevious = $month->startOfMonth()->lt(CarbonImmutable::now()->startOfMonth());
        $candidateQuery = Candidate::visibleTo($request->user()->loadMissing('role'))->whereKey($report->candidate_id);

        if ($isPrevious) {
            abort_unless($candidateQuery->exists(), 403);
            return;
        }

        abort_unless(
            $this->applyActiveContractFilter($candidateQuery, $month, false)->exists(),
            403
        );
    }

    private function applyActiveContractFilter($query, CarbonImmutable $month, ?bool $isPreviousMonth = null)
    {
        $monthStart = $month->startOfMonth()->toDateString();
        $monthEnd = $month->endOfMonth()->toDateString();
        $isPrevious = $isPreviousMonth ?? $month->startOfMonth()->lt(CarbonImmutable::now()->startOfMonth());

        return $query
            ->where('candidates.mode_id', 2)
            ->where(function ($dateQ) use ($monthStart, $monthEnd) {
                // Candidate contract dates cover this month
                $dateQ->where(function ($cq) use ($monthStart, $monthEnd) {
                    $cq->whereNotNull('candidates.contract_from_date')
                       ->whereNotNull('candidates.contract_to_date')
                       ->whereDate('candidates.contract_from_date', '<=', $monthEnd)
                       ->whereDate('candidates.contract_to_date', '>=', $monthStart);
                })->orWhere(function ($fallbackQ) use ($monthStart, $monthEnd) {
                    // Fallback to matching employee contract dates if candidate contract dates missing
                    $fallbackQ->where(function ($sq) {
                        $sq->whereNull('candidates.contract_from_date')
                           ->orWhereNull('candidates.contract_to_date');
                    })->whereExists(function ($sub) use ($monthStart, $monthEnd) {
                        $sub->selectRaw('1')->from('employees')
                            ->whereNull('employees.deleted_at')
                            ->where(function ($match) {
                                $match->where(function ($m1) {
                                    $m1->whereNotNull('candidates.email')
                                       ->where('candidates.email', '!=', '')
                                       ->where(function ($e) {
                                           $e->whereRaw('LOWER(employees.official_mail) = LOWER(candidates.email)')
                                             ->orWhereRaw('LOWER(employees.personal_mail) = LOWER(candidates.email)');
                                       });
                                })->orWhere(function ($m2) {
                                    $m2->whereNotNull('candidates.mobile_no')
                                       ->where('candidates.mobile_no', '!=', '')
                                       ->where(function ($p) {
                                           $p->whereRaw('employees.mobile_number = candidates.mobile_no')
                                             ->orWhereRaw('employees.alternate_mobile_number = candidates.mobile_no');
                                       });
                                });
                            })
                            ->whereNotNull('employees.contract_from_date')
                            ->whereNotNull('employees.contract_to_date')
                            ->whereDate('employees.contract_from_date', '<=', $monthEnd)
                            ->whereDate('employees.contract_to_date', '>=', $monthStart);
                    });
                });
            })
            ->when(! $isPrevious, fn ($q) => $q->where('candidates.status', true))
            ->whereNotExists(function ($sub) use ($monthStart, $isPrevious) {
                $sub->selectRaw('1')->from('employees')
                    ->whereNull('employees.deleted_at')
                    ->where(function ($match) {
                        $match->where(function ($m1) {
                            $m1->whereNotNull('candidates.email')
                               ->where('candidates.email', '!=', '')
                               ->where(function ($e) {
                                   $e->whereRaw('LOWER(employees.official_mail) = LOWER(candidates.email)')
                                     ->orWhereRaw('LOWER(employees.personal_mail) = LOWER(candidates.email)');
                               });
                        })->orWhere(function ($m2) {
                            $m2->whereNotNull('candidates.mobile_no')
                               ->where('candidates.mobile_no', '!=', '')
                               ->where(function ($p) {
                                   $p->whereRaw('employees.mobile_number = candidates.mobile_no')
                                     ->orWhereRaw('employees.alternate_mobile_number = candidates.mobile_no');
                               });
                        });
                    })
                    ->where(function ($inactive) use ($monthStart, $isPrevious) {
                        // Employee relieved before this month
                        $inactive->where(function ($rel) use ($monthStart) {
                            $rel->whereNotNull('employees.relieving_date')
                                ->whereDate('employees.relieving_date', '<', $monthStart);
                        })
                        // Employee contract ended before this month
                        ->orWhere(function ($cEnd) use ($monthStart) {
                            $cEnd->whereNotNull('employees.contract_to_date')
                                 ->whereDate('employees.contract_to_date', '<', $monthStart);
                        });

                        // For current and future months, inactive status means inactive contract
                        if (! $isPrevious) {
                            $inactive->orWhere('employees.status', 0);
                        }
                    });
            });
    }
}
