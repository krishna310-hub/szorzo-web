<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\ContractReport;
use App\Models\Report;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ContractReportController extends Controller
{
    use AuthorizesRequests;

    private const WORKING_HOURS_PER_DAY = 8;

    public function index(Request $request)
    {
        $this->authorize('read', Report::class);
        $month = $this->month($request);
        $reports = $this->query($request, $month)->paginate(50)->withQueryString();

        $reports->getCollection()->each(function (ContractReport $report) use ($month) {
            $this->syncReportSalary($report, $month->daysInMonth);
        });

        return view('backend.contract-reports.index', [
            'month' => $month,
            'daysInMonth' => $month->daysInMonth,
            'reports' => $reports,
        ]);
    }

    public function refresh(Request $request)
    {
        $this->authorize('export', Report::class);
        $month = $this->month($request);
        $candidates = $this->contractCandidates($request)
            ->get(['candidates.id', 'candidates.take_home', 'candidates.onboarding_ctc']);
        $created = 0;

        foreach ($candidates as $candidate) {
            $monthlyTakeHome = $this->monthlyTakeHome($candidate);
            $report = ContractReport::firstOrCreate(
                ['candidate_id' => $candidate->id, 'salary_month' => $month->toDateString()],
                [
                    'monthly_take_home' => $monthlyTakeHome,
                    'present_days' => $month->daysInMonth,
                    'absent_days' => 0,
                    'payable_salary' => $monthlyTakeHome,
                ]
            );

            if (! $report->wasRecentlyCreated) {
                $this->syncReportSalary($report, $month->daysInMonth);
            }

            $created += $report->wasRecentlyCreated ? 1 : 0;
        }

        $updated = $candidates->count() - $created;

        return redirect()->route('admin.contract-reports.index', ['month' => $month->format('Y-m')])
            ->with('success', $created.' contract candidate(s) added and '.$updated.' updated for '.$month->format('F Y').'.');
    }

    public function update(Request $request, ContractReport $contractReport)
    {
        $this->authorize('export', Report::class);
        $this->ensureVisible($request, $contractReport);
        $days = $contractReport->salary_month->daysInMonth;
        $data = $request->validate([
            'present_days' => ['required', 'integer', 'min:0', 'max:'.$days],
            'absent_days' => ['required', 'integer', 'min:0', 'max:'.$days],
            'worked_hours' => ['nullable', 'numeric', 'min:0', 'max:'.($days * self::WORKING_HOURS_PER_DAY)],
        ]);

        if ($days !== $data['present_days'] + $data['absent_days']) {
            throw ValidationException::withMessages([
                'present_days' => 'Present and leave days must total '.$days.' days for '.$contractReport->salary_month->format('F Y').'.',
            ]);
        }

        $contractReport->update([
            ...$data,
            'worked_hours' => isset($data['worked_hours']) ? round((float) $data['worked_hours'], 2) : null,
            'payable_salary' => isset($data['worked_hours'])
                ? $this->hourlyPayableSalary(
                    (float) $contractReport->monthly_take_home,
                    (float) $data['worked_hours'],
                    $days
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
        $this->authorize('export', Report::class);
        $month = $this->month($request);
        $reports = $this->query($request, $month)->get();

        return Pdf::loadView('backend.contract-reports.pdf', compact('month', 'reports'))
            ->setPaper('a4', 'landscape')
            ->download('contract-report-'.$month->format('Y-m').'.pdf');
    }

    public function invoice(Request $request, ContractReport $contractReport)
    {
        $this->authorize('export', Report::class);
        $this->ensureVisible($request, $contractReport);
        $contractReport->load(['candidate.client', 'candidate.jobRole', 'candidate.recruiter']);

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

    private function contractCandidates(Request $request)
    {
        $month = $this->month($request);

        return Candidate::query()->visibleTo($request->user()->loadMissing('role'))
            ->where('status', true)
            ->where('mode_id', 2)
            ->whereDate('contract_from_date', '<=', $month->endOfMonth()->toDateString())
            ->whereDate('contract_to_date', '>=', $month->startOfMonth()->toDateString());
    }

    private function monthlyTakeHome(Candidate $candidate): float
    {
        $takeHome = (float) $candidate->take_home;

        if ($takeHome > 0) {
            return round($takeHome, 2);
        }

        return round((float) $candidate->onboarding_ctc / 12, 2);
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
            'payable_salary' => $report->worked_hours !== null
                ? $this->hourlyPayableSalary(
                    $monthlyTakeHome,
                    (float) $report->worked_hours,
                    $daysInMonth
                )
                : $this->payableSalary(
                    $monthlyTakeHome,
                    (int) $report->absent_days,
                    $daysInMonth
                ),
        ]);

        if ($report->isDirty(['monthly_take_home', 'payable_salary'])) {
            $report->save();
        }
    }

    private function payableSalary(float $monthlySalary, int $leaveDays, int $daysInMonth): float
    {
        $leaveDeduction = ($monthlySalary / $daysInMonth) * $leaveDays;

        return round(max(0, $monthlySalary - $leaveDeduction), 2);
    }

    private function hourlyPayableSalary(float $monthlySalary, float $workedHours, int $daysInMonth): float
    {
        $monthlyHours = $daysInMonth * self::WORKING_HOURS_PER_DAY;

        if ($monthlyHours <= 0) {
            return 0;
        }

        return round(max(0, ($monthlySalary / $monthlyHours) * $workedHours), 2);
    }

    private function query(Request $request, CarbonImmutable $month)
    {
        return ContractReport::query()
            ->with(['candidate.client', 'candidate.jobRole', 'candidate.recruiter'])
            ->whereDate('salary_month', $month->toDateString())
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
