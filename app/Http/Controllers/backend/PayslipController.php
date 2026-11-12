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
        $tenure = $this->resolveTenure($target);

        $hasCompletedMonths = !empty($tenure['has_completed_months']);
        $canGenerate = $hasCompletedMonths;

        if (!$hasCompletedMonths) {
            $month = null;
            $year = null;
            $tenureNotice = "Cannot generate payslip: This employee joined on {$tenure['doj_display']} and has not completed a full working month yet (minimum 28 to 30 days or 1 completed month required). Official monthly payslips will be available once the month completes.";
            $payslipData = $this->payslipService->getEmptyPayslipData($target, $tenureNotice);
        } else {
            $requestedMonth = $this->resolveMonth($request, $tenure);
            $requestedYear = $this->resolveYear($request, $tenure);

            // Clamp to valid working tenure
            if (!$this->isWithinTenure($requestedMonth, $requestedYear, $tenure)) {
                $month = $tenure['default_month'];
                $year = $tenure['default_year'];
                $tenureNotice = "Requested period ({$requestedMonth}/{$requestedYear}) has not completed yet or is outside completed working tenure ({$tenure['tenure_label']}). Displaying latest completed month.";
            } else {
                $month = $requestedMonth;
                $year = $requestedYear;
                $tenureNotice = null;
            }

            // Check if Date-to-Date range is requested
            $isRange = ($request->input('period_type') === 'range' && $request->filled('from_date') && $request->filled('to_date'));
            if ($isRange) {
                try {
                    $fromDate = \Carbon\CarbonImmutable::parse($request->input('from_date'))->startOfDay();
                    $toDate = \Carbon\CarbonImmutable::parse($request->input('to_date'))->endOfDay();
                    $periodDays = (int) $fromDate->diffInDays($toDate) + 1;
                    if ($periodDays < 28) {
                        $canGenerate = false;
                        $tenureNotice = "Cannot generate payslip: Payslips require a minimum period of 28 to 30 days or one completed month. The selected period is only {$periodDays} days.";
                        $payslipData = $this->payslipService->getEmptyPayslipData($target, $tenureNotice);
                    } elseif ($toDate->greaterThan(now())) {
                        $canGenerate = false;
                        $tenureNotice = "Cannot generate payslip: The selected period extends into the future or current incomplete month. Payslips can only be generated for completed periods.";
                        $payslipData = $this->payslipService->getEmptyPayslipData($target, $tenureNotice);
                    } else {
                        $payslipData = $this->payslipService->getPayslipData($target, $month, $year, $request->all());
                    }
                } catch (\Throwable $e) {
                    $payslipData = $this->payslipService->getPayslipData($target, $month, $year, $request->all());
                }
            } else {
                $payslipData = $this->payslipService->getPayslipData($target, $month, $year, $request->all());
            }
        }

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
            'can_generate' => $canGenerate,
            'all_employees' => $allEmployees,
            'internal_employees' => $internalEmployees,
            'external_employees' => $externalEmployees,
            'target' => $target,
            'selected_employee_id' => $target instanceof Employee ? $target->id : null,
            'tenure' => $tenure,
            'tenure_notice' => $tenureNotice,
            'month' => $month,
            'year' => $year,
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
        $tenure = $this->resolveTenure($target);

        if (empty($tenure['has_completed_months'])) {
            return redirect()->route('admin.payslip.index', [
                'employee_id' => $target instanceof Employee ? $target->id : null,
            ])->with('error', "Cannot generate payslip: Employee joined on {$tenure['doj_display']} and has not completed a full working month yet (minimum 28 to 30 days or 1 completed month required). Official monthly payslips will be available once the month completes.");
        }

        if ($request->input('period_type') === 'range' && $request->filled('from_date') && $request->filled('to_date')) {
            try {
                $from = \Carbon\CarbonImmutable::parse($request->input('from_date'))->startOfDay();
                $to = \Carbon\CarbonImmutable::parse($request->input('to_date'))->endOfDay();
                $periodDays = (int) $from->diffInDays($to) + 1;
                if ($periodDays < 28 || $to->greaterThan(now())) {
                    return redirect()->route('admin.payslip.index', [
                        'employee_id' => $target instanceof Employee ? $target->id : null,
                    ])->with('error', "Cannot generate payslip: Payslips require a completed period of at least 28 to 30 days or one completed month.");
                }
            } catch (\Throwable $e) {}
        }

        $requestedMonth = $this->resolveMonth($request, $tenure);
        $requestedYear = $this->resolveYear($request, $tenure);

        // Verify that requested period falls within working tenure
        if (!$this->isWithinTenure($requestedMonth, $requestedYear, $tenure)) {
            $monthName = date('F', mktime(0, 0, 0, $requestedMonth, 1));
            return redirect()->route('admin.payslip.index', [
                'employee_id' => $target instanceof Employee ? $target->id : null,
                'month' => $tenure['default_month'],
                'year' => $tenure['default_year'],
            ])->with('error', "Payslip cannot be downloaded for {$monthName} {$requestedYear} as this month has not completed yet or is outside working tenure ({$tenure['tenure_label']}). Only completed working months are eligible.");
        }

        $month = $requestedMonth;
        $year = $requestedYear;
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
        $tenure = $this->resolveTenure($target);

        if (empty($tenure['has_completed_months'])) {
            abort(422, "Cannot preview payslip: Employee joined on {$tenure['doj_display']} and has not completed a full working month yet (minimum 28 to 30 days or 1 completed month required). Official monthly payslips will be available once the month completes.");
        }

        if ($request->input('period_type') === 'range' && $request->filled('from_date') && $request->filled('to_date')) {
            try {
                $from = \Carbon\CarbonImmutable::parse($request->input('from_date'))->startOfDay();
                $to = \Carbon\CarbonImmutable::parse($request->input('to_date'))->endOfDay();
                $periodDays = (int) $from->diffInDays($to) + 1;
                if ($periodDays < 28 || $to->greaterThan(now())) {
                    abort(422, "Cannot preview payslip: Payslips require a completed period of at least 28 to 30 days or one completed month.");
                }
            } catch (\Throwable $e) {}
        }

        $requestedMonth = $this->resolveMonth($request, $tenure);
        $requestedYear = $this->resolveYear($request, $tenure);

        if (!$this->isWithinTenure($requestedMonth, $requestedYear, $tenure)) {
            $monthName = date('F', mktime(0, 0, 0, $requestedMonth, 1));
            abort(422, "Payslip cannot be previewed for {$monthName} {$requestedYear} as this month has not completed yet or is outside working tenure ({$tenure['tenure_label']}).");
        }

        $month = $requestedMonth;
        $year = $requestedYear;
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

    /**
     * Resolve the employee's active working tenure (from Join Date to Relieving Date / Current Month).
     */
    public function resolveTenure(Employee|Candidate|User $target): array
    {
        $joinDate = null;
        $relievingDate = null;

        if ($target instanceof Employee) {
            $joinDate = $target->date_of_joining ?: $target->contract_from_date;
            $relievingDate = $target->relieving_date;
            if (!$joinDate) {
                $joinDate = $target->created_at;
            }
        } elseif ($target instanceof Candidate) {
            $joinDate = $target->contract_from_date ?: $target->created_at;
            $relievingDate = null;
        } elseif ($target instanceof User) {
            $linked = $target->linkedEmployee();
            if ($linked) {
                return $this->resolveTenure($linked);
            }
            $joinDate = $target->created_at;
        }

        $now = \Carbon\CarbonImmutable::now();

        // 1. Start Date (Joining)
        $actualJoinDate = null;
        try {
            $actualJoinDate = $joinDate ? \Carbon\CarbonImmutable::parse($joinDate)->startOfDay() : null;
            $start = $actualJoinDate ? $actualJoinDate->startOfMonth() : $now->startOfYear();
        } catch (\Throwable $e) {
            $actualJoinDate = null;
            $start = $now->startOfYear();
        }

        // 2. End Date (Relieving or Latest Completed Month)
        // A month must be completed to be eligible for payslip generation.
        // Current ongoing month has not completed yet, so the latest completed month is the previous month end.
        $latestCompletedMonth = $now->subMonth()->endOfMonth();

        $isRelieved = false;
        $relievedCarbon = null;
        if (!empty($relievingDate)) {
            try {
                $relievedCarbon = \Carbon\CarbonImmutable::parse($relievingDate)->endOfDay();
                $isRelieved = true;
                // If relieved, cap at relieving date or latest completed month, whichever is earlier
                $end = $relievedCarbon->lt($latestCompletedMonth) ? $relievedCarbon->endOfMonth() : $latestCompletedMonth;
            } catch (\Throwable $e) {
                $end = $latestCompletedMonth;
            }
        } else {
            // For active ongoing employees, eligible tenure extends up to the latest completed month
            $end = $latestCompletedMonth;
        }

        // Determine if employee has completed at least 28-30 days or one full month:
        // Rule 1: Joined in current ongoing month -> month is NOT completed yet!
        $joinedInCurrentOrFutureMonth = $actualJoinDate ? $actualJoinDate->greaterThan($latestCompletedMonth) : false;

        // Rule 2: Relieved before completing at least 28 days
        $relievedTooEarly = ($isRelieved && $relievedCarbon && $actualJoinDate && $actualJoinDate->diffInDays($relievedCarbon) < 28);

        // Rule 3: Joined in previous month with fewer than 28 days in that month, and no other month completed
        $joinedInPrevMonthWithTooFewDays = false;
        if ($actualJoinDate && $actualJoinDate->isSameMonth($latestCompletedMonth)) {
            $daysInPrevMonth = (int) $actualJoinDate->diffInDays($latestCompletedMonth) + 1;
            if ($daysInPrevMonth < 28) {
                $joinedInPrevMonthWithTooFewDays = true;
            }
        }

        $hasCompletedMonths = !$joinedInCurrentOrFutureMonth && !$relievedTooEarly && !$joinedInPrevMonthWithTooFewDays && $start->lte($end);

        // Safeguard: if employee joined in current uncompleted month, clamp end to start month
        if ($end->lt($start)) {
            $end = $start->endOfMonth();
        }

        $startYear = (int) $start->year;
        $startMonth = (int) $start->month;
        $endYear = (int) $end->year;
        $endMonth = (int) $end->month;

        $availableYears = [];
        $availableMonthsByYear = [];
        $allWorkingPeriods = [];

        if ($hasCompletedMonths) {
            for ($y = $endYear; $y >= $startYear; $y--) {
                $availableYears[] = $y;
            }

            for ($y = $endYear; $y >= $startYear; $y--) {
                $mStart = ($y === $startYear) ? $startMonth : 1;
                $mEnd = ($y === $endYear) ? $endMonth : 12;

                $monthsForYear = [];
                for ($m = $mEnd; $m >= $mStart; $m--) {
                    $monthsForYear[] = $m;
                    $dateObj = \Carbon\CarbonImmutable::create($y, $m, 1);
                    $allWorkingPeriods[] = [
                        'year' => $y,
                        'month' => $m,
                        'month_name' => $dateObj->format('F'),
                        'label' => $dateObj->format('F Y'),
                    ];
                }
                sort($monthsForYear);
                $availableMonthsByYear[$y] = $monthsForYear;
            }

            $defaultMonth = $endMonth;
            $defaultYear = $endYear;
        } else {
            // No completed months yet (e.g. employee joined in current ongoing month or has not completed 28-30 days / 1 full month)
            $availableYears = [];
            $availableMonthsByYear = [];
            $allWorkingPeriods = [];
            $defaultMonth = null;
            $defaultYear = null;
        }

        $dojLabel = $actualJoinDate ? $actualJoinDate->format('d-M-Y') : $start->format('d-M-Y');
        $dorLabel = ($isRelieved && $relievedCarbon) ? $relievedCarbon->format('d-M-Y') : 'Active / Present';
        $tenureLabel = "{$dojLabel} to {$dorLabel}";

        return [
            'start_date' => $start,
            'end_date' => $end,
            'start_year' => $startYear,
            'start_month' => $startMonth,
            'end_year' => $endYear,
            'end_month' => $endMonth,
            'is_relieved' => $isRelieved,
            'has_completed_months' => $hasCompletedMonths,
            'doj_display' => $dojLabel,
            'dor_display' => $dorLabel,
            'tenure_label' => $tenureLabel,
            'available_years' => $availableYears,
            'available_months_by_year' => $availableMonthsByYear,
            'all_working_periods' => $allWorkingPeriods,
            'default_month' => $defaultMonth,
            'default_year' => $defaultYear,
        ];
    }

    /**
     * Check if a given month & year falls within the employee's working tenure.
     */
    public function isWithinTenure(int $month, int $year, array $tenure): bool
    {
        if (empty($tenure['has_completed_months'])) {
            return false;
        }

        // Current ongoing month and future months are never within completed working tenure
        $periodStart = \Carbon\CarbonImmutable::create($year, $month, 1)->startOfMonth();
        $currentMonthStart = \Carbon\CarbonImmutable::now()->startOfMonth();
        if ($periodStart->greaterThanOrEqualTo($currentMonthStart)) {
            return false;
        }

        return $this->isPeriodWithinRange(
            $month,
            $year,
            $tenure['start_year'],
            $tenure['start_month'],
            $tenure['end_year'],
            $tenure['end_month']
        );
    }

    /**
     * Integer range check for (year * 100 + month).
     */
    public function isPeriodWithinRange(int $month, int $year, int $startYear, int $startMonth, int $endYear, int $endMonth): bool
    {
        $periodVal = ($year * 100) + $month;
        $startVal = ($startYear * 100) + $startMonth;
        $endVal = ($endYear * 100) + $endMonth;

        return $periodVal >= $startVal && $periodVal <= $endVal;
    }

    protected function resolveMonth(Request $request, ?array $tenure = null): int
    {
        $defaultMonth = $tenure['default_month'] ?? now()->subMonth()->month;
        $fallback = $defaultMonth ?: now()->subMonth()->month;
        $inputMonth = (int) $request->input('month', $fallback);
        return ($inputMonth >= 1 && $inputMonth <= 12) ? $inputMonth : $fallback;
    }

    protected function resolveYear(Request $request, ?array $tenure = null): int
    {
        $defaultYear = $tenure['default_year'] ?? now()->subMonth()->year;
        $fallback = $defaultYear ?: now()->subMonth()->year;
        $inputYear = (int) $request->input('year', $fallback);
        return ($inputYear >= 2000 && $inputYear <= 2100) ? $inputYear : $fallback;
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
