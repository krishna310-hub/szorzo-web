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

    public function index(Request $request)
    {
        $this->authorize('read', Report::class);
        $month = $this->month($request);

        return view('backend.contract-reports.index', [
            'month' => $month,
            'daysInMonth' => $month->daysInMonth,
            'reports' => $this->query($request, $month)->paginate(50)->withQueryString(),
        ]);
    }

    public function refresh(Request $request)
    {
        $this->authorize('export', Report::class);
        $month = $this->month($request);
        $candidates = $this->contractCandidates($request)
            ->get(['candidates.id', 'candidates.take_home']);
        $created = 0;

        foreach ($candidates as $candidate) {
            $report = ContractReport::firstOrCreate(
                ['candidate_id' => $candidate->id, 'salary_month' => $month->toDateString()],
                [
                    'monthly_take_home' => $candidate->take_home ?? 0,
                    'present_days' => $month->daysInMonth,
                    'absent_days' => 0,
                    'payable_salary' => $candidate->take_home ?? 0,
                ]
            );
            $created += $report->wasRecentlyCreated ? 1 : 0;
        }

        return redirect()->route('admin.contract-reports.index', ['month' => $month->format('Y-m')])
            ->with('success', $created.' contract candidate(s) added for '.$month->format('F Y').'.');
    }

    public function update(Request $request, ContractReport $contractReport)
    {
        $this->authorize('export', Report::class);
        $this->ensureVisible($request, $contractReport);
        $days = $contractReport->salary_month->daysInMonth;
        $data = $request->validate([
            'present_days' => ['required', 'integer', 'min:0', 'max:'.$days],
            'absent_days' => ['required', 'integer', 'min:0', 'max:'.$days],
        ]);

        if ($days !== $data['present_days'] + $data['absent_days']) {
            throw ValidationException::withMessages([
                'present_days' => 'Present and absent days must total '.$days.' days for '.$contractReport->salary_month->format('F Y').'.',
            ]);
        }

        $contractReport->update([
            ...$data,
            'payable_salary' => round(((float) $contractReport->monthly_take_home / $days) * $data['present_days'], 2),
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
