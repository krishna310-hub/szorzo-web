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

            if (! $report->wasRecentlyCreated) {
                $this->syncReportSalary($report, $month->daysInMonth);
            }

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

        $contractReport->update([
            ...$data,
            'worked_hours' => $contractReport->is_hourly ? round((float) $data['worked_hours'], 2) : null,
            'payable_salary' => $contractReport->is_hourly
                ? $this->hourlyPayableSalary(
                    (float) $contractReport->hourly_salary,
                    (float) $data['worked_hours'],
                )
                : $this->payableSalary(
                    (float) $contractReport->monthly_take_home,
                    $data['absent_days'],
                    $days
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

    public function invoice(Request $request, ContractReport $contractReport)
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

        return Pdf::loadView('backend.contract-reports.invoice', compact('contractReport', 'invoiceNumber'))
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

        return Candidate::query()
            ->with('client.billing')
            ->visibleTo($request->user()->loadMissing('role'))
            ->where('status', true)
            ->where('mode_id', 2)
            ->whereDate('contract_from_date', '<=', $month->endOfMonth()->toDateString())
            ->whereDate('contract_to_date', '>=', $month->startOfMonth()->toDateString());
    }

    private function monthlyTakeHome(Candidate $candidate): float
    {
        $monthlyTakeHome = (float) $candidate->take_home;

        if ($monthlyTakeHome <= 0) {
            $monthlyTakeHome = (float) $candidate->onboarding_ctc / 12;
        }

        return round(max(0, $monthlyTakeHome), 2);
    }

    private function syncReportSalary(ContractReport $report, int $daysInMonth): void
    {
        if (! $report->relationLoaded('candidate')) {
            $report->load('candidate');
        }

        if (! $report->candidate) {
            return;
        }

        $monthlyTakeHome = $this->monthlyTakeHome($report->candidate);
        $report->fill([
            'monthly_take_home' => $monthlyTakeHome,
            'is_hourly' => $report->candidate->is_hourly,
            'hourly_salary' => $report->candidate->is_hourly
                ? $report->candidate->hourly_salary
                : null,
            'worked_hours' => $report->candidate->is_hourly
                ? ($report->worked_hours ?? 0)
                : null,
            'payable_salary' => $report->candidate->is_hourly
                ? $this->hourlyPayableSalary(
                    (float) $report->candidate->hourly_salary,
                    (float) ($report->worked_hours ?? 0),
                )
                : $this->payableSalary(
                    $monthlyTakeHome,
                    (int) $report->absent_days,
                    $daysInMonth
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

    private function addRevenueMetrics($reports): void
    {
        $reports->each(function (ContractReport $report) {
            $candidate = $report->candidate;
            $requirement = $candidate?->clientRequirement;
            $billingAmountPerHour = $report->is_hourly ? (float) ($requirement?->ctc ?? 0) : null;
            // Billing percentage belongs to the matched client requirement and
            // applies to both monthly and hourly contract candidates.
            $revenuePercentage = (float) ($requirement?->billing?->value ?? 0);
            $revenuePerHour = $report->is_hourly
                ? round(($billingAmountPerHour * $revenuePercentage) / 100, 2)
                : null;
            $totalRevenue = $report->is_hourly
                ? round($revenuePerHour * (float) ($report->worked_hours ?? 0), 2)
                : round((float) ($report->payable_salary ?? $report->monthly_take_home) * $revenuePercentage / 100, 2);

            $report->setAttribute('billing_amount_per_hour', $billingAmountPerHour);
            $report->setAttribute('revenue_percentage', $revenuePercentage);
            $report->setAttribute('revenue_per_hour', $revenuePerHour);
            $report->setAttribute('contract_revenue', $totalRevenue);
        });
    }

    private function query(Request $request, CarbonImmutable $month, string $contractType)
    {
        return ContractReport::query()
            ->with(['candidate.clientRequirement.billing', 'candidate.client', 'candidate.jobRole', 'candidate.recruiter'])
            ->whereDate('salary_month', $month->toDateString())
            ->when($contractType === 'monthly', fn ($query) => $query->where('is_hourly', false))
            ->when($contractType === 'hourly', fn ($query) => $query->where('is_hourly', true))
            ->whereHas('candidate', fn ($query) => $query
                ->visibleTo($request->user()->loadMissing('role'))
                ->where('status', true)
                ->where('mode_id', 2)
                ->whereDate('contract_from_date', '<=', $month->endOfMonth()->toDateString())
                ->whereDate('contract_to_date', '>=', $month->startOfMonth()->toDateString()))
            ->orderBy(Candidate::select('candidate_name')->whereColumn('candidates.id', 'contract_reports.candidate_id'));
    }

    private function ensureVisible(Request $request, ContractReport $report): void
    {
        $month = $report->salary_month->toImmutable();

        abort_unless(
            Candidate::visibleTo($request->user()->loadMissing('role'))
                ->whereKey($report->candidate_id)
                ->where('status', true)
                ->where('mode_id', 2)
                ->whereDate('contract_from_date', '<=', $month->endOfMonth()->toDateString())
                ->whereDate('contract_to_date', '>=', $month->startOfMonth()->toDateString())
                ->exists(),
            403
        );
    }
}
