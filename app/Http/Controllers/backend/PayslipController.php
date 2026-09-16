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

        $allEmployees = Employee::with(['mode', 'client'])
            ->orderBy('employee_name')
            ->get();

        $internalEmployees = $allEmployees->filter(fn ($emp) => $emp->isInternal())->values();
        $externalEmployees = $allEmployees->filter(fn ($emp) => $emp->isExternal())->values();

        return view('backend.payslip.index', array_merge($payslipData, [
            'can_manage_all' => true,
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
     * Resolve the target Employee from the Employee module based on request inputs.
     */
    protected function resolveTarget(Request $request): Employee
    {
        if ($request->filled('employee_id')) {
            $employee = Employee::with(['mode', 'client'])->find($request->input('employee_id'));
            if ($employee) {
                return $employee;
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

        return new Employee([
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
