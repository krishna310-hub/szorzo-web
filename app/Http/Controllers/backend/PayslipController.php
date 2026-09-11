<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\Employee;
use App\Models\User;
use App\Services\PayslipService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PayslipController extends Controller
{
    public function __construct(
        protected PayslipService $payslipService
    ) {}

    /**
     * Display the payslip view & download page (Admin Only).
     */
    public function index(Request $request)
    {
        $this->ensureAdminAccess();

        $target = $this->resolveTarget($request);
        $month = $this->resolveMonth($request);
        $year = $this->resolveYear($request);

        $payslipData = $this->payslipService->getPayslipData($target, $month, $year, $request->all());

        $allCandidates = Candidate::with(['mode', 'client', 'jobRole', 'clientRequirement.billing'])
            ->where('status', true)
            ->orderBy('candidate_name')
            ->get();

        $allEmployees = Employee::with(['mode', 'client'])
            ->where('status', 1)
            ->orderBy('employee_name')
            ->get();

        $allUsers = User::with('role')
            ->where('is_active', 1)
            ->orderBy('name')
            ->get();

        return view('backend.payslip.index', array_merge($payslipData, [
            'can_manage_all' => true,
            'all_candidates' => $allCandidates,
            'all_employees' => $allEmployees,
            'all_users' => $allUsers,
            'target' => $target,
            'selected_candidate_id' => $target instanceof Candidate ? $target->id : null,
            'selected_employee_id' => $target instanceof Employee ? $target->id : null,
            'selected_user_id' => $target instanceof User ? $target->id : null,
        ]));
    }

    /**
     * Generate and download the payslip PDF.
     */
    public function download(Request $request)
    {
        $this->ensureAdminAccess();

        $target = $this->resolveTarget($request);
        $month = $this->resolveMonth($request);
        $year = $this->resolveYear($request);

        $data = $this->payslipService->getPayslipData($target, $month, $year, $request->all());

        $pdf = Pdf::loadView('backend.payslip.pdf', $data)
            ->setPaper('a4', 'portrait');

        return $pdf->download($this->resolveFilename($data));
    }

    /**
     * Preview the payslip PDF directly in browser.
     */
    public function preview(Request $request)
    {
        $this->ensureAdminAccess();

        $target = $this->resolveTarget($request);
        $month = $this->resolveMonth($request);
        $year = $this->resolveYear($request);

        $data = $this->payslipService->getPayslipData($target, $month, $year, $request->all());

        $pdf = Pdf::loadView('backend.payslip.pdf', $data)
            ->setPaper('a4', 'portrait');

        return $pdf->stream($this->resolveFilename($data));
    }

    /**
     * Ensure the authenticated user is an administrator.
     */
    protected function ensureAdminAccess(): void
    {
        $currentUser = Auth::user();
        abort_unless(
            $currentUser && $currentUser->isSuperAdmin(),
            403,
            'Unauthorized access. Payslips are restricted to administrators only.'
        );
    }

    /**
     * Resolve the target Employee, Candidate, or User based on request inputs.
     */
    protected function resolveTarget(Request $request): Employee|User|Candidate
    {
        if ($request->filled('candidate_id')) {
            $candidate = Candidate::with(['mode', 'client', 'jobRole', 'clientRequirement.billing', 'recruiter'])
                ->find($request->input('candidate_id'));
            if ($candidate) {
                return $candidate;
            }
        }

        if ($request->filled('employee_id')) {
            $employee = Employee::with(['mode', 'client'])->find($request->input('employee_id'));
            if ($employee) {
                return $employee;
            }
        }

        if ($request->filled('user_id')) {
            $user = User::with('role')->find($request->input('user_id'));
            if ($user) {
                return $user;
            }
        }

        // Default to first active candidate or employee
        $firstCandidate = Candidate::with(['mode', 'client', 'jobRole', 'clientRequirement.billing'])
            ->where('status', true)
            ->orderBy('candidate_name')
            ->first();

        if ($firstCandidate) {
            return $firstCandidate;
        }

        $firstEmployee = Employee::with(['mode', 'client'])
            ->where('status', 1)
            ->orderBy('employee_name')
            ->first();

        if ($firstEmployee) {
            return $firstEmployee;
        }

        return Auth::user();
    }

    protected function resolveMonth(Request $request): int
    {
        $defaultMonth = now()->day < 5 ? now()->subMonth()->month : now()->month;
        return (int) $request->input('month', $defaultMonth);
    }

    protected function resolveYear(Request $request): int
    {
        $defaultYear = now()->day < 5 ? now()->subMonth()->year : now()->year;
        return (int) $request->input('year', $defaultYear);
    }

    protected function resolveFilename(array $data): string
    {
        $empIdentifier = preg_replace('/[^A-Za-z0-9_-]/', '', (string) ($data['employee_no'] ?: 'SZ'));
        if (!empty($data['is_full_calendar_month'])) {
            return 'Payslip_' . $empIdentifier . '_' . $data['month_name'] . '_' . $data['year'] . '.pdf';
        }
        $from = str_replace('-', '', (string) $data['from_date']);
        $to = str_replace('-', '', (string) $data['to_date']);
        return 'Payslip_' . $empIdentifier . '_' . $from . '_to_' . $to . '.pdf';
    }
}
