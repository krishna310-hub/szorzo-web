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
     * Display the payslip view & download page.
     * Accessible by Administrators (all employees) and Employees (own payslip).
     */
    public function index(Request $request)
    {
        $currentUser = Auth::user();
        $this->ensureAccess($currentUser);

        $canManageAll = $this->canManageAll($currentUser);
        $target = $this->resolveTarget($request);
        $month = $this->resolveMonth($request);
        $year = $this->resolveYear($request);

        $payslipData = $this->payslipService->getPayslipData($target, $month, $year, $request->all());

        $allEmployees = collect();
        $internalEmployees = collect();
        $externalEmployees = collect();

        if ($canManageAll) {
            $allEmployees = Employee::with(['mode', 'client'])
                ->orderBy('employee_name')
                ->get();

            $internalEmployees = $allEmployees->filter(fn ($emp) => $emp->isInternal())->values();
            $externalEmployees = $allEmployees->filter(fn ($emp) => $emp->isExternal())->values();
        }

        return view('backend.payslip.index', array_merge($payslipData, [
            'can_manage_all' => $canManageAll,
            'all_employees' => $allEmployees,
            'internal_employees' => $internalEmployees,
            'external_employees' => $externalEmployees,
            'target' => $target,
            'selected_employee_id' => $target instanceof Employee ? $target->id : null,
        ]));
    }

    /**
     * Generate and download the payslip PDF.
     */
    public function download(Request $request)
    {
        $currentUser = Auth::user();
        $this->ensureAccess($currentUser);

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
        $currentUser = Auth::user();
        $this->ensureAccess($currentUser);

        $target = $this->resolveTarget($request);
        $month = $this->resolveMonth($request);
        $year = $this->resolveYear($request);

        $data = $this->payslipService->getPayslipData($target, $month, $year, $request->all());

        $pdf = Pdf::loadView('backend.payslip.pdf', $data)
            ->setPaper('a4', 'portrait');

        return $pdf->stream($this->resolveFilename($data));
    }

    /**
     * Check if user has administrative rights to manage and view all employees' payslips.
     */
    public function canManageAll(?User $user = null): bool
    {
        $user = $user ?? Auth::user();
        if (!$user) {
            return false;
        }

        return $user->isSuperAdmin()
            || (int) $user->id === 1
            || $user->isAdmin();
    }

    /**
     * Ensure the authenticated user has access to view/download payslips.
     */
    protected function ensureAccess(?User $user = null): void
    {
        $currentUser = $user ?? Auth::user();
        abort_unless($currentUser, 401, 'Please log in to view payslips.');

        // Investors do not access employee payroll
        if ($currentUser->isInvestor() || (int) $currentUser->role_id === 6) {
            abort(403, 'Unauthorized access. Investors do not have access to employee payslips.');
        }

        if (!$currentUser->is_active) {
            abort(403, 'Your account is inactive. Please contact administration.');
        }
    }

    /**
     * Backward-compatible check for legacy callers.
     */
    protected function ensureAdminAccess(): void
    {
        $this->ensureAccess();
    }

    /**
     * Resolve the target Employee from the Employee module based on request inputs.
     */
    protected function resolveTarget(Request $request): Employee|Candidate|User
    {
        $currentUser = Auth::user();
        $canManageAll = $this->canManageAll($currentUser);

        // For non-admin employees: enforce viewing strictly their own payslip
        if (!$canManageAll && $currentUser) {
            $myEmployee = $currentUser->linkedEmployee();
            if ($myEmployee) {
                return $myEmployee;
            }

            // Check if matching Candidate exists
            $myCandidate = Candidate::with(['mode', 'client', 'jobRole', 'clientRequirement.billing', 'recruiter'])
                ->whereRaw('LOWER(email) = ?', [mb_strtolower($currentUser->email)])
                ->first();
            if ($myCandidate) {
                return $myCandidate;
            }

            return $currentUser;
        }

        // Admin management resolution:
        if ($request->filled('candidate_id')) {
            $candidate = Candidate::with(['mode', 'client', 'jobRole', 'clientRequirement.billing', 'recruiter'])->find($request->input('candidate_id'));
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
            $user = User::find($request->input('user_id'));
            if ($user) {
                return $user->linkedEmployee() ?? $user;
            }
        }

        // Default to first active employee in Employee module
        $firstEmployee = Employee::with(['mode', 'client'])
            ->where('status', 1)
            ->orderBy('employee_name')
            ->first();

        if ($firstEmployee) {
            return $firstEmployee;
        }

        // Fallback to first available employee in Employee module
        $anyEmployee = Employee::with(['mode', 'client'])
            ->orderBy('employee_name')
            ->first();

        if ($anyEmployee) {
            return $anyEmployee;
        }

        return $currentUser ?? new Employee([
            'employee_name' => 'No Employees Found',
            'employee_no' => 'EMP000',
            'employment_mode' => 'FTE',
        ]);
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
