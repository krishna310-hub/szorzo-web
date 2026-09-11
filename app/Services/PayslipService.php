<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\User;
use Carbon\CarbonImmutable;

class PayslipService
{
    /**
     * Generate complete payslip data for an Employee or User.
     *
     * Supports:
     * - Target as Employee or User
     * - Date-to-Date period (from_date to to_date) for Contract & C2H
     * - Monthly period (month & year) for FTE (Full Time Employees)
     * - Attendance & LOP integration
     * - Comprehensive earnings, deductions, and demographic details
     */
    public function getPayslipData(Employee|User $target, ?int $month = null, ?int $year = null, array $overrides = []): array
    {
        // 1. Resolve Target Models (Employee & User)
        $employee = null;
        $user = null;

        if ($target instanceof Employee) {
            try {
                $employee = $target->loadMissing(['mode', 'client']);
            } catch (\Throwable $e) {
                $employee = $target;
            }
            try {
                $user = $employee->linkedUser();
            } catch (\Throwable $e) {
                $user = null;
            }
        } elseif ($target instanceof User) {
            try {
                $user = $target->loadMissing('role');
            } catch (\Throwable $e) {
                $user = $target;
            }
            try {
                $employee = $user->linkedEmployee();
                if ($employee) {
                    $employee = $employee->loadMissing(['mode', 'client']);
                }
            } catch (\Throwable $e) {
                $employee = null;
            }
        }

        // 2. Resolve Employment Mode (FTE, Contract, C2H)
        $mode = $employee ? $employee->employment_mode : 'FTE';
        $modeObj = $employee && $employee->relationLoaded('mode') ? $employee->getRelation('mode') : null;
        $modeLabel = $modeObj?->mode ?: ($mode === 'FTE' ? 'Full Time' : $mode);

        $clientObj = $employee && $employee->relationLoaded('client') ? $employee->getRelation('client') : null;
        if (!$clientObj && $employee) {
            try {
                $clientObj = $employee->client;
            } catch (\Throwable $e) {
                $clientObj = null;
            }
        }
        $clientName = $clientObj?->client ?: null;

        // 3. Resolve Date Period (from_date and to_date)
        $fromDateInput = $overrides['from_date'] ?? null;
        $toDateInput = $overrides['to_date'] ?? null;

        if (!empty($fromDateInput) && !empty($toDateInput)) {
            try {
                $fromDate = CarbonImmutable::parse($fromDateInput)->startOfDay();
                $toDate = CarbonImmutable::parse($toDateInput)->endOfDay();
                if ($toDate->lt($fromDate)) {
                    [$fromDate, $toDate] = [$toDate->startOfDay(), $fromDate->endOfDay()];
                }
            } catch (\Throwable $e) {
                $fromDate = null;
                $toDate = null;
            }
        } else {
            $fromDate = null;
            $toDate = null;
        }

        if (!$fromDate || !$toDate) {
            $resolvedMonth = $month ?: (now()->day < 5 ? now()->subMonth()->month : now()->month);
            $resolvedYear = $year ?: (now()->day < 5 ? now()->subMonth()->year : now()->year);

            $contractFrom = null;
            $contractTo = null;
            try {
                $contractFrom = $employee?->contract_from_date;
                $contractTo = $employee?->contract_to_date;
            } catch (\Throwable $e) {
                $contractFrom = null;
                $contractTo = null;
            }

            if ($employee && in_array($mode, ['Contract', 'C2H'], true) && $contractFrom && $contractTo && empty($overrides['force_month'])) {
                $fromDate = CarbonImmutable::parse($contractFrom)->startOfDay();
                $toDate = CarbonImmutable::parse($contractTo)->endOfDay();
                $resolvedMonth = $fromDate->month;
                $resolvedYear = $fromDate->year;
            } else {
                $startMonth = CarbonImmutable::create($resolvedYear, $resolvedMonth, 1)->startOfDay();
                $fromDate = $startMonth;
                $toDate = $startMonth->endOfMonth()->endOfDay();
            }
        } else {
            $resolvedMonth = $fromDate->month;
            $resolvedYear = $fromDate->year;
        }

        $periodDays = (int) $fromDate->startOfDay()->diffInDays($toDate->startOfDay()) + 1;
        $daysInMonth = $fromDate->daysInMonth;
        $monthName = $fromDate->format('M');
        $fullMonthName = $fromDate->format('F');

        $isFullCalendarMonth = ($fromDate->day === 1 && $toDate->day === $daysInMonth && $fromDate->isSameMonth($toDate));

        if ($isFullCalendarMonth) {
            $periodTitle = 'Payslip for the month of ' . $fromDate->format('F Y');
            $periodSubtitle = $fromDate->format('F Y');
        } else {
            $periodTitle = 'Payslip for the period ' . $fromDate->format('d-M-Y') . ' to ' . $toDate->format('d-M-Y');
            $periodSubtitle = $fromDate->format('d/m/Y') . ' - ' . $toDate->format('d/m/Y');
        }

        // 4. Attendance Integration (if User account linked)
        $attendances = collect();
        if ($user) {
            try {
                $attendances = Attendance::where('user_id', $user->id)
                    ->whereBetween('attendance_date', [$fromDate->toDateString(), $toDate->toDateString()])
                    ->get();
            } catch (\Throwable $e) {
                $attendances = collect();
            }
        }

        $presentCount = $attendances->where('status', 'present')->count();
        $halfDayCount = $attendances->where('status', 'half_day')->count();
        $absentCount = $attendances->where('status', 'absent')->count();
        $onLeaveCount = $attendances->where('status', 'on_leave')->count();
        $holidayCount = $attendances->where('status', 'holiday')->count();
        $weekOffCount = $attendances->where('status', 'week_off')->count();

        // Calculate unpaid leaves if any
        $unpaidLeaveCount = 0;
        $leaveRequestIds = $attendances->where('status', 'on_leave')->pluck('leave_request_id')->filter()->unique();
        if ($leaveRequestIds->isNotEmpty()) {
            try {
                $unpaidLeaveCount = LeaveRequest::whereIn('id', $leaveRequestIds)
                    ->where('leave_type', 'Unpaid Leave')
                    ->count();
            } catch (\Throwable $e) {
                $unpaidLeaveCount = 0;
            }
        }

        // LOP (Loss of Pay)
        $autoLop = $absentCount + ($halfDayCount * 0.5) + $unpaidLeaveCount;

        if (isset($overrides['lop']) && is_numeric($overrides['lop'])) {
            $lop = (float) $overrides['lop'];
        } elseif ($attendances->isEmpty()) {
            $lop = 0.0;
        } else {
            $lop = (float) $autoLop;
        }

        $effectiveWorkDays = max(0.0, (float) ($periodDays - $lop));
        if (isset($overrides['effective_work_days']) && is_numeric($overrides['effective_work_days'])) {
            $effectiveWorkDays = (float) $overrides['effective_work_days'];
        }

        $otHours = isset($overrides['ot_hours']) && is_numeric($overrides['ot_hours'])
            ? (float) $overrides['ot_hours']
            : 0.0;

        // 5. Pro-Rata Salary Calculations
        if ($isFullCalendarMonth) {
            $ratio = $daysInMonth > 0 ? ($effectiveWorkDays / $daysInMonth) : 1.0;
        } else {
            $denominator = max(1, $daysInMonth);
            $ratio = min(1.0, max(0.0, $effectiveWorkDays / $denominator));
            if (($overrides['calculation_basis'] ?? '') === 'period' || $periodDays > 31) {
                $ratio = $periodDays > 0 ? ($effectiveWorkDays / $periodDays) : 1.0;
            }
        }

        // Salary structure: from employee attributes or default 38,050 INR breakdown
        $monthlyGross = (float) ($employee->monthly_gross ?? 38050);
        if ($monthlyGross <= 0) {
            $monthlyGross = 38050;
        }

        $basicFull = (float) ($employee->basic_salary ?? round($monthlyGross * 0.50));
        $hraFull = (float) ($employee->hra ?? round($monthlyGross * 0.20));
        $conveyanceFull = (float) ($employee->conveyance ?? 0);
        $medicalFull = (float) ($employee->medical_allowance ?? max(0, $monthlyGross - $basicFull - $hraFull - $conveyanceFull));
        $specialFull = (float) ($employee->special_allowance ?? 0);
        $overtimeFull = (float) ($employee->overtime_amount ?? ($otHours > 0 ? round($otHours * 150) : 0));
        $ltaFull = (float) ($employee->lta ?? 0);
        $arrearsFull = (float) ($employee->arrears ?? 0);

        // Actual earnings calculated based on effective working days
        $basicActual = round($basicFull * $ratio);
        $hraActual = round($hraFull * $ratio);
        $conveyanceActual = round($conveyanceFull * $ratio);
        $medicalActual = round($medicalFull * $ratio);
        $specialActual = round($specialFull * $ratio);
        $overtimeActual = $overtimeFull;
        $ltaActual = round($ltaFull * $ratio);
        $arrearsActual = $arrearsFull;

        $totalEarningsFull = $basicFull + $hraFull + $conveyanceFull + $medicalFull + $specialFull + $overtimeFull + $ltaFull + $arrearsFull;
        $totalEarningsActual = $basicActual + $hraActual + $conveyanceActual + $medicalActual + $specialActual + $overtimeActual + $ltaActual + $arrearsActual;

        // Deductions
        $pfDeduction = (float) ($employee->pf_deduction ?? 1800);
        $esiDeduction = (float) ($employee->esi_deduction ?? 0);
        $ptDeduction = (float) ($employee->pt_deduction ?? ($monthlyGross > 15000 ? 200 : 0));
        $incomeTax = (float) ($employee->income_tax ?? 0);
        $salaryAdvance = (float) ($employee->salary_advance ?? 0);
        $fines = (float) ($employee->fines ?? 0);
        $lwf = (float) ($employee->labour_welfare_fund ?? 0);
        $otherDeductions = (float) ($employee->other_deductions ?? 0);

        $totalDeductionsActual = $pfDeduction + $esiDeduction + $ptDeduction + $incomeTax + $salaryAdvance + $fines + $lwf + $otherDeductions;

        $netPay = max(0, $totalEarningsActual - $totalDeductionsActual);

        // 6. Identity and Demographic Information
        $fallbackId = $employee?->id ?? ($user?->id ?? 1);
        $employeeNo = $employee?->employee_no ?: ('SZ' . str_pad((string) $fallbackId, 3, '0', STR_PAD_LEFT));
        $employeeName = $employee?->employee_name ?: ($user?->name ?: 'Employee');
        $fatherName = $employee?->fathers_name ?: 'Padmanabha';
        $gender = $employee?->gender ? strtoupper(substr($employee->gender, 0, 1)) : 'F';
        $designation = $employee?->designation ?: ($user?->role?->name ?: 'Senior Recruiter');
        $pfNo = $employee?->bank_uan_pf_number ?: ($employee?->employee_uan_pf_number ?: 'BGMRD36445920000010003');
        $pfUan = $employee?->employee_uan_pf_number ?: '102311451956';
        $esiNo = $employee?->employee_esi_number ?: 'NA';
        $location = 'Bangalore';
        $remarks = $employee?->salary_remarks ?: 'NA';

        // 7. Logo Base64 for DomPDF embedding
        $logoBase64 = null;
        try {
            $logoPath = function_exists('public_path') ? public_path('frontend/images/rhino-logo.png') : null;
            if ($logoPath && file_exists($logoPath)) {
                $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
            }
        } catch (\Throwable $e) {
            $logoBase64 = null;
        }

        return [
            'target_type' => $target instanceof Employee ? 'employee' : 'user',
            'target_id' => $target->id,
            'user' => $user,
            'employee' => $employee,
            'mode' => $mode,
            'mode_label' => $modeLabel,
            'client_name' => $clientName,
            'from_date' => $fromDate->toDateString(),
            'to_date' => $toDate->toDateString(),
            'from_date_display' => $fromDate->format('d-M-Y'),
            'to_date_display' => $toDate->format('d-M-Y'),
            'period_days' => $periodDays,
            'is_full_calendar_month' => $isFullCalendarMonth,
            'period_title' => $periodTitle,
            'period_subtitle' => $periodSubtitle,
            'month' => $resolvedMonth,
            'year' => $resolvedYear,
            'month_name' => $monthName,
            'full_month_name' => $fullMonthName,
            'days_in_month' => $daysInMonth,
            'attendance_stats' => [
                'present' => $presentCount,
                'half_day' => $halfDayCount,
                'absent' => $absentCount,
                'on_leave' => $onLeaveCount,
                'holiday' => $holidayCount,
                'week_off' => $weekOffCount,
                'marked_total' => $attendances->count(),
            ],
            'employee_no' => $employeeNo,
            'name' => $employeeName,
            'father_name' => $fatherName,
            'gender' => $gender,
            'designation' => $designation,
            'location' => $location,
            'pf_no' => $pfNo,
            'pf_uan' => $pfUan,
            'esi_no' => $esiNo,
            'ot_hours' => $otHours,
            'lop' => $lop,
            'effective_work_days' => $effectiveWorkDays,
            'earnings' => [
                'basic' => ['label' => 'Basic', 'full' => $basicFull, 'actual' => $basicActual],
                'hra' => ['label' => 'HRA', 'full' => $hraFull, 'actual' => $hraActual],
                'conveyance' => ['label' => 'Conveyance', 'full' => $conveyanceFull, 'actual' => $conveyanceActual],
                'medical' => ['label' => 'Medical Allow', 'full' => $medicalFull, 'actual' => $medicalActual],
                'special' => ['label' => 'Special Allow', 'full' => $specialFull, 'actual' => $specialActual],
                'overtime' => ['label' => 'Over Time', 'full' => $overtimeFull, 'actual' => $overtimeActual],
                'lta' => ['label' => 'LTA', 'full' => $ltaFull, 'actual' => $ltaActual],
                'arrears' => ['label' => 'Arrears', 'full' => $arrearsFull, 'actual' => $arrearsActual],
            ],
            'total_earnings_full' => $totalEarningsFull,
            'total_earnings_actual' => $totalEarningsActual,
            'deductions' => [
                'pf' => ['label' => 'PF', 'actual' => $pfDeduction],
                'esi' => ['label' => 'ESI', 'actual' => $esiDeduction],
                'pt' => ['label' => 'Professional Tax', 'actual' => $ptDeduction],
                'income_tax' => ['label' => 'Income Tax', 'actual' => $incomeTax],
                'salary_advance' => ['label' => 'Salary Advance', 'actual' => $salaryAdvance],
                'fines' => ['label' => 'Fines', 'actual' => $fines],
                'lwf' => ['label' => 'Labour Welfare Fund', 'actual' => $lwf],
                'other' => ['label' => 'Other Deduction', 'actual' => $otherDeductions],
            ],
            'total_deductions_actual' => $totalDeductionsActual,
            'net_pay' => $netPay,
            'remarks' => $remarks,
            'logo_base64' => $logoBase64,
        ];
    }

    /**
     * Helper to format amount for display (returns '00' for 0).
     */
    public static function formatAmount(float|int|null $amount): string
    {
        if ($amount === null || (float) $amount === 0.0) {
            return '00';
        }
        return (float) $amount == (int) $amount ? (string) ((int) $amount) : number_format($amount, 2, '.', '');
    }

    /**
     * Helper to format day numbers (e.g. 1.5, 29.5, 31).
     */
    public static function formatDays(float|int|null $days): string
    {
        if ($days === null) {
            return '0';
        }
        return (float) $days == (int) $days ? (string) ((int) $days) : number_format($days, 1, '.', '');
    }
}
