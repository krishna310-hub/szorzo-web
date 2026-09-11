<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Candidate;
use App\Models\ContractReport;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\User;
use Carbon\CarbonImmutable;

class PayslipService
{
    /**
     * Generate complete payslip data for an Employee, User, or Candidate.
     *
     * Synchronized with ContractReportController to provide exact calculation parity:
     * - Monthly Contract: Payable = round(max(0, Monthly - (Monthly / DaysInMonth) * LeaveDays), 2)
     * - Hourly Contract: Intake = HourlyRate * WorkedHours, Payable = Intake - (Intake * Billing% / 100)
     * - Full Time (FTE): Standard salary structure with exact loss-of-pay deduction and statutory taxes
     */
    public function getPayslipData(Employee|User|Candidate $target, ?int $month = null, ?int $year = null, array $overrides = []): array
    {
        // 1. Resolve Target Models (Employee, User, and Candidate)
        $employee = null;
        $user = null;
        $candidate = null;

        if ($target instanceof Candidate) {
            try {
                $candidate = $target->loadMissing(['mode', 'client', 'jobRole', 'clientRequirement.billing', 'recruiter']);
            } catch (\Throwable $e) {
                $candidate = $target;
            }

            // Attempt to resolve matching Employee by email
            try {
                $candEmail = mb_strtolower((string) $candidate->email);
                if (!empty($candEmail)) {
                    $employee = Employee::with(['mode', 'client'])
                        ->where(function ($q) use ($candEmail) {
                            $q->whereRaw('LOWER(official_mail) = ?', [$candEmail])
                              ->orWhereRaw('LOWER(personal_mail) = ?', [$candEmail]);
                        })->first();
                }
            } catch (\Throwable $e) {
                $employee = null;
            }

            // Attempt to resolve matching User by email
            try {
                $candEmail = mb_strtolower((string) $candidate->email);
                if (!empty($candEmail)) {
                    $user = User::whereRaw('LOWER(email) = ?', [$candEmail])->first();
                }
            } catch (\Throwable $e) {
                $user = null;
            }
        } elseif ($target instanceof Employee) {
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

            // Attempt to resolve matching Candidate by email
            try {
                $emails = array_filter([
                    mb_strtolower((string) $employee->official_mail),
                    mb_strtolower((string) $employee->personal_mail),
                ]);
                if (!empty($emails)) {
                    $candidate = Candidate::with(['mode', 'client', 'jobRole', 'clientRequirement.billing', 'recruiter'])
                        ->where(function ($q) use ($emails) {
                            foreach ($emails as $em) {
                                $q->orWhereRaw('LOWER(email) = ?', [$em]);
                            }
                        })->first();
                }
            } catch (\Throwable $e) {
                $candidate = null;
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

            try {
                $usrEmail = mb_strtolower((string) $user->email);
                if (!empty($usrEmail)) {
                    $candidate = Candidate::with(['mode', 'client', 'jobRole', 'clientRequirement.billing', 'recruiter'])
                        ->whereRaw('LOWER(email) = ?', [$usrEmail])->first();
                }
            } catch (\Throwable $e) {
                $candidate = null;
            }
        }

        // 2. Resolve Employment Mode (FTE, Contract, C2H)
        if ($candidate) {
            $mode = $candidate->mode?->mode ?: 'Contract';
            $modeLabel = $mode;
        } elseif ($employee) {
            $mode = $employee->employment_mode;
            $modeObj = $employee->relationLoaded('mode') ? $employee->getRelation('mode') : null;
            $modeLabel = $modeObj?->mode ?: ($mode === 'FTE' ? 'Full Time' : $mode);
        } else {
            $mode = 'FTE';
            $modeLabel = 'Full Time';
        }

        $rawModeLower = strtolower(trim((string) $mode));
        $isContractLike = str_contains($rawModeLower, 'contract') || str_contains($rawModeLower, 'c2h') || ($candidate !== null);

        // Resolve Client Name
        $clientName = $candidate?->client?->client ?: ($employee?->client?->client ?: null);

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

            $contractFrom = $candidate?->contract_from_date ?: $employee?->contract_from_date;
            $contractTo = $candidate?->contract_to_date ?: $employee?->contract_to_date;

            if ($isContractLike && $contractFrom && $contractTo && empty($overrides['force_month']) && empty($month)) {
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
        $salaryMonthDate = $fromDate->startOfMonth()->toDateString();

        $isFullCalendarMonth = ($fromDate->day === 1 && $toDate->day === $daysInMonth && $fromDate->isSameMonth($toDate));

        if ($isFullCalendarMonth) {
            $periodTitle = 'Payslip for the month of ' . $fromDate->format('F Y');
            $periodSubtitle = $fromDate->format('F Y');
        } else {
            $periodTitle = 'Payslip for the period ' . $fromDate->format('d-M-Y') . ' to ' . $toDate->format('d-M-Y');
            $periodSubtitle = $fromDate->format('d/m/Y') . ' - ' . $toDate->format('d/m/Y');
        }

        // 4. Contract Report Model Synchronization
        $contractReport = null;
        if ($candidate) {
            try {
                $contractReport = ContractReport::where('candidate_id', $candidate->id)
                    ->whereDate('salary_month', $salaryMonthDate)
                    ->first();
            } catch (\Throwable $e) {
                $contractReport = null;
            }
        }

        // 5. Hourly Mode & Billing Metrics Resolution
        $isHourly = false;
        if (isset($overrides['is_hourly'])) {
            $isHourly = (bool) $overrides['is_hourly'];
        } elseif ($contractReport) {
            $isHourly = (bool) $contractReport->is_hourly;
        } elseif ($candidate) {
            $isHourly = (bool) $candidate->is_hourly;
        }

        $hourlySalary = 0.0;
        if ($isHourly) {
            $hourlySalary = (float) ($overrides['hourly_salary'] ?? ($contractReport?->hourly_salary ?? ($candidate?->hourly_salary ?? 0)));
        }

        $workedHours = 0.0;
        if ($isHourly) {
            $workedHours = (float) ($overrides['worked_hours'] ?? ($contractReport?->worked_hours ?? ($overrides['ot_hours'] ?? 0)));
        }

        $revenuePercentage = 0.0;
        if ($candidate) {
            $revenuePercentage = (float) ($candidate->clientRequirement?->billing?->value ?? 0);
        }
        if (isset($overrides['revenue_percentage']) && is_numeric($overrides['revenue_percentage'])) {
            $revenuePercentage = (float) $overrides['revenue_percentage'];
        }
        $revenuePercentage = min(100, max(0, $revenuePercentage));

        // 6. Attendance & Leave (Loss of Pay) Integration
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

        $autoLop = $absentCount + ($halfDayCount * 0.5) + $unpaidLeaveCount;

        // Determine Leave Days (Absent Days)
        if (isset($overrides['absent_days']) && is_numeric($overrides['absent_days'])) {
            $leaveDays = (float) $overrides['absent_days'];
        } elseif (isset($overrides['lop']) && is_numeric($overrides['lop'])) {
            $leaveDays = (float) $overrides['lop'];
        } elseif ($contractReport) {
            $leaveDays = (float) $contractReport->absent_days;
        } elseif ($attendances->isEmpty()) {
            $leaveDays = 0.0;
        } else {
            $leaveDays = (float) $autoLop;
        }

        // Determine Present Days
        if (isset($overrides['present_days']) && is_numeric($overrides['present_days'])) {
            $presentDays = (float) $overrides['present_days'];
        } elseif ($contractReport) {
            $presentDays = (float) $contractReport->present_days;
        } else {
            $presentDays = max(0.0, (float) ($periodDays - $leaveDays));
        }

        $effectiveWorkDays = $presentDays;
        if (isset($overrides['effective_work_days']) && is_numeric($overrides['effective_work_days'])) {
            $effectiveWorkDays = (float) $overrides['effective_work_days'];
        }

        $otHours = isset($overrides['ot_hours']) && is_numeric($overrides['ot_hours'])
            ? (float) $overrides['ot_hours']
            : ($isHourly ? $workedHours : 0.0);

        // 7. Base Monthly Salary Resolution
        $monthlySalary = 0.0;
        if (isset($overrides['monthly_salary']) && is_numeric($overrides['monthly_salary'])) {
            $monthlySalary = (float) $overrides['monthly_salary'];
        } elseif ($contractReport && (float) $contractReport->monthly_take_home > 0) {
            $monthlySalary = (float) $contractReport->monthly_take_home;
        } elseif ($candidate) {
            $rawCtc = (float) ($candidate->onboarding_ctc ?? 0);
            if ($rawCtc > 0) {
                $monthlySalary = round($rawCtc / 12, 2);
            } elseif ((float) ($candidate->take_home ?? 0) > 0) {
                $monthlySalary = (float) $candidate->take_home;
            }
        } elseif ($employee && (float) ($employee->monthly_gross ?? 0) > 0) {
            $monthlySalary = (float) $employee->monthly_gross;
        }

        if ($monthlySalary <= 0 && !$isHourly) {
            $monthlySalary = 38050.0;
        }

        // 8. EXACT Contract Report Salary Calculation Engine
        $grossIntake = 0.0;
        $revenueShare = 0.0;
        $payableSalary = 0.0;
        $leaveDeduction = 0.0;

        if ($isHourly) {
            // Hourly Formula: intake = hourly * worked_hours; revenue = intake * billing%; payable = intake - revenue
            $calc = self::calculateContractHourlySalary($hourlySalary, $workedHours, $revenuePercentage);
            $grossIntake = $calc['gross_intake'];
            $revenueShare = $calc['revenue_share'];
            $payableSalary = $calc['payable_salary'];
        } else {
            // Monthly Formula: leave_deduction = (monthly / days_in_month) * leave_days; payable = round(monthly - leave_deduction, 2)
            if ($isFullCalendarMonth) {
                if ($contractReport && !$contractReport->is_hourly && (float) $contractReport->absent_days === (float) $leaveDays && empty($overrides['monthly_salary'])) {
                    $payableSalary = (float) $contractReport->payable_salary;
                    $leaveDeduction = round(max(0, $monthlySalary - $payableSalary), 2);
                } else {
                    $calc = self::calculateContractMonthlySalary($monthlySalary, $leaveDays, $daysInMonth);
                    $leaveDeduction = $calc['leave_deduction'];
                    $payableSalary = $calc['payable_salary'];
                }
            } else {
                // Date-to-Date partial range: per-day intake * effective work days
                $dailyRate = $daysInMonth > 0 ? ($monthlySalary / $daysInMonth) : 0;
                $payableSalary = round(max(0, $dailyRate * $effectiveWorkDays), 2);
                $leaveDeduction = round(max(0, $dailyRate * $leaveDays), 2);
            }
            $grossIntake = $monthlySalary;
            $revenueShare = round($payableSalary * $revenuePercentage / 100, 2);
        }

        // 9. Earnings Breakdown & Statutory Deductions
        $isFTE = !$isContractLike && ($mode === 'FTE');

        if ($isContractLike) {
            // Contract & C2H candidates/employees reconcile directly to Contract Report Total Salary
            $totalEarningsFull = $isHourly ? $grossIntake : $monthlySalary;
            $totalEarningsActual = $payableSalary;

            $hasExplicitStructure = $employee && (float) ($employee->basic_salary ?? 0) > 0;
            if ($hasExplicitStructure) {
                $basicRatio = $totalEarningsFull > 0 ? ((float) $employee->basic_salary / $totalEarningsFull) : 0.50;
                $hraRatio = $totalEarningsFull > 0 ? ((float) ($employee->hra ?? 0) / $totalEarningsFull) : 0.20;
            } else {
                $basicRatio = 0.50;
                $hraRatio = 0.20;
            }

            $basicFull = round($totalEarningsFull * $basicRatio, 2);
            $hraFull = round($totalEarningsFull * $hraRatio, 2);
            $conveyanceFull = 0.0;
            $medicalFull = 0.0;
            $specialFull = round($totalEarningsFull - $basicFull - $hraFull, 2);
            $overtimeFull = 0.0;
            $ltaFull = 0.0;
            $arrearsFull = 0.0;

            $basicActual = round($totalEarningsActual * $basicRatio, 2);
            $hraActual = round($totalEarningsActual * $hraRatio, 2);
            $conveyanceActual = 0.0;
            $medicalActual = 0.0;
            // Subtract to ensure the sum of actual earnings exactly equals $payableSalary to the cent
            $specialActual = round($totalEarningsActual - $basicActual - $hraActual, 2);
            $overtimeActual = 0.0;
            $ltaActual = 0.0;
            $arrearsActual = 0.0;

            // In Contract Report, contract candidates have no statutory PF/PT deductions
            $pfDeduction = 0.0;
            $esiDeduction = 0.0;
            $ptDeduction = 0.0;
            $incomeTax = 0.0;
            $salaryAdvance = 0.0;
            $fines = 0.0;
            $lwf = 0.0;
            $otherDeductions = 0.0;

            if ($employee) {
                // If an employee record explicitly specifies advance/fine/tds
                $salaryAdvance = (float) ($employee->salary_advance ?? 0);
                $fines = (float) ($employee->fines ?? 0);
                $incomeTax = (float) ($employee->income_tax ?? 0);
                $otherDeductions = (float) ($employee->other_deductions ?? 0);
            }

            $totalDeductionsActual = $pfDeduction + $esiDeduction + $ptDeduction + $incomeTax + $salaryAdvance + $fines + $lwf + $otherDeductions;
            $netPay = max(0.0, round($totalEarningsActual - $totalDeductionsActual, 2));
        } else {
            // Full Time Employee (FTE)
            $totalEarningsFull = $monthlySalary;
            $totalEarningsActual = $payableSalary;

            $basicFull = (float) ($employee?->basic_salary ?? round($totalEarningsFull * 0.50));
            $hraFull = (float) ($employee?->hra ?? round($totalEarningsFull * 0.20));
            $conveyanceFull = (float) ($employee?->conveyance ?? 0);
            $medicalFull = (float) ($employee?->medical_allowance ?? max(0, $totalEarningsFull - $basicFull - $hraFull - $conveyanceFull));
            $specialFull = (float) ($employee?->special_allowance ?? 0);
            $overtimeFull = (float) ($employee?->overtime_amount ?? ($otHours > 0 ? round($otHours * 150) : 0));
            $ltaFull = (float) ($employee?->lta ?? 0);
            $arrearsFull = (float) ($employee?->arrears ?? 0);

            $ratio = $totalEarningsFull > 0 ? ($totalEarningsActual / $totalEarningsFull) : 1.0;

            $basicActual = round($basicFull * $ratio);
            $hraActual = round($hraFull * $ratio);
            $conveyanceActual = round($conveyanceFull * $ratio);
            $medicalActual = round($medicalFull * $ratio);
            $specialActual = round($specialFull * $ratio);
            $overtimeActual = $overtimeFull;
            $ltaActual = round($ltaFull * $ratio);
            $arrearsActual = $arrearsFull;

            $totalEarningsActual = $basicActual + $hraActual + $conveyanceActual + $medicalActual + $specialActual + $overtimeActual + $ltaActual + $arrearsActual;

            $pfDeduction = (float) ($employee?->pf_deduction ?? 1800);
            $esiDeduction = (float) ($employee?->esi_deduction ?? 0);
            $ptDeduction = (float) ($employee?->pt_deduction ?? ($totalEarningsFull > 15000 ? 200 : 0));
            $incomeTax = (float) ($employee?->income_tax ?? 0);
            $salaryAdvance = (float) ($employee?->salary_advance ?? 0);
            $fines = (float) ($employee?->fines ?? 0);
            $lwf = (float) ($employee?->labour_welfare_fund ?? 0);
            $otherDeductions = (float) ($employee?->other_deductions ?? 0);

            $totalDeductionsActual = $pfDeduction + $esiDeduction + $ptDeduction + $incomeTax + $salaryAdvance + $fines + $lwf + $otherDeductions;
            $netPay = max(0.0, round($totalEarningsActual - $totalDeductionsActual, 2));
        }

        // 10. Remarks and Audit trail
        if ($isHourly) {
            $remarks = sprintf(
                'Hourly Contract Intake: %s hrs @ INR %s/hr = INR %s. Billing/Margin Share (%s%%): INR %s. Net Payable: INR %s.',
                number_format($workedHours, 2),
                number_format($hourlySalary, 2),
                number_format($grossIntake, 2),
                number_format($revenuePercentage, 2),
                number_format($revenueShare, 2),
                number_format($payableSalary, 2)
            );
        } elseif ($isContractLike) {
            if ($leaveDays > 0) {
                $remarks = sprintf(
                    'Monthly Contract Intake: INR %s (%d calendar days). Leave Deduction (%s days): INR %s. Net Payable: INR %s.',
                    number_format($monthlySalary, 2),
                    $daysInMonth,
                    self::formatDays($leaveDays),
                    number_format($leaveDeduction, 2),
                    number_format($payableSalary, 2)
                );
            } else {
                $remarks = sprintf(
                    'Monthly Contract Intake: INR %s (%d calendar days). Net Payable: INR %s.',
                    number_format($monthlySalary, 2),
                    $daysInMonth,
                    number_format($payableSalary, 2)
                );
            }
        } else {
            $remarks = $employee?->salary_remarks ?: 'FTE Monthly Salary processed.';
        }

        if (!empty($employee?->salary_remarks) && $isContractLike) {
            $remarks = $employee->salary_remarks . ' | ' . $remarks;
        }

        // 11. Demographic and Identity Information
        if ($candidate) {
            $targetType = 'candidate';
            $employeeNo = $employee?->employee_no ?: ('CAN' . str_pad((string) $candidate->id, 3, '0', STR_PAD_LEFT));
            $employeeName = $candidate->candidate_name;
            $fatherName = $employee?->fathers_name ?: 'NA';
            $gender = $employee?->gender ? strtoupper(substr($employee->gender, 0, 1)) : 'NA';
            $designation = $candidate->jobRole?->job_role ?: ($employee?->designation ?: 'Contract Candidate');
            $pfNo = $employee?->bank_uan_pf_number ?: 'NA';
            $pfUan = $employee?->employee_uan_pf_number ?: 'NA';
            $esiNo = $employee?->employee_esi_number ?: 'NA';
            $location = $candidate->current_location ?: ($candidate->preferred_location ?: 'Bangalore');
        } elseif ($employee) {
            $targetType = 'employee';
            $fallbackId = $employee->id;
            $employeeNo = $employee->employee_no ?: ('SZ' . str_pad((string) $fallbackId, 3, '0', STR_PAD_LEFT));
            $employeeName = $employee->employee_name ?: ($user?->name ?: 'Employee');
            $fatherName = $employee->fathers_name ?: 'NA';
            $gender = $employee->gender ? strtoupper(substr($employee->gender, 0, 1)) : 'F';
            $designation = $employee->designation ?: ($user?->role?->name ?: 'Senior Recruiter');
            $pfNo = $employee->bank_uan_pf_number ?: ($employee->employee_uan_pf_number ?: 'NA');
            $pfUan = $employee->employee_uan_pf_number ?: 'NA';
            $esiNo = $employee->employee_esi_number ?: 'NA';
            $location = 'Bangalore';
        } else {
            $targetType = 'user';
            $fallbackId = $user?->id ?? 1;
            $employeeNo = 'SZ' . str_pad((string) $fallbackId, 3, '0', STR_PAD_LEFT);
            $employeeName = $user?->name ?: 'Employee';
            $fatherName = 'NA';
            $gender = 'F';
            $designation = $user?->role?->name ?: 'Staff';
            $pfNo = 'NA';
            $pfUan = 'NA';
            $esiNo = 'NA';
            $location = 'Bangalore';
        }

        // 12. Logo Base64 for DomPDF embedding
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
            'target_type' => $targetType,
            'target_id' => $target->id,
            'user' => $user,
            'employee' => $employee,
            'candidate' => $candidate,
            'contract_report' => $contractReport,
            'contract_report_synced' => ($contractReport !== null),
            'mode' => $mode,
            'mode_label' => $modeLabel,
            'is_contract_like' => $isContractLike,
            'is_hourly' => $isHourly,
            'hourly_salary' => $hourlySalary,
            'worked_hours' => $workedHours,
            'revenue_percentage' => $revenuePercentage,
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
                'present' => $presentDays,
                'half_day' => $halfDayCount,
                'absent' => $leaveDays,
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
            'ot_hours' => $isHourly ? number_format($workedHours, 2) : (float) $otHours,
            'lop' => $leaveDays,
            'present_days' => $presentDays,
            'absent_days' => $leaveDays,
            'effective_work_days' => $effectiveWorkDays,
            'contract_intake' => $grossIntake,
            'contract_revenue' => $revenueShare,
            'leave_deduction' => $leaveDeduction,
            'payable_salary' => $payableSalary,
            'earnings' => [
                'basic' => ['label' => 'Basic', 'full' => $basicFull, 'actual' => $basicActual],
                'hra' => ['label' => 'HRA', 'full' => $hraFull, 'actual' => $hraActual],
                'conveyance' => ['label' => 'Conveyance', 'full' => $conveyanceFull, 'actual' => $conveyanceActual],
                'medical' => ['label' => 'Medical Allow', 'full' => $medicalFull, 'actual' => $medicalActual],
                'special' => ['label' => $isHourly ? 'Hourly Intake Share' : 'Special Allow', 'full' => $specialFull, 'actual' => $specialActual],
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
     * Exact monthly contract calculation from ContractReportController.
     * Leave Deduction = (monthlySalary / daysInMonth) * leaveDays
     * Payable Salary = round(max(0, monthlySalary - leaveDeduction), 2)
     */
    public static function calculateContractMonthlySalary(float $monthlySalary, float $leaveDays, int $daysInMonth): array
    {
        $daysInMonth = max(1, $daysInMonth);
        $leaveDays = max(0.0, $leaveDays);
        $leaveDeduction = ($monthlySalary / $daysInMonth) * $leaveDays;
        $payableSalary = round(max(0.0, $monthlySalary - $leaveDeduction), 2);

        return [
            'monthly_salary' => round($monthlySalary, 2),
            'leave_days' => $leaveDays,
            'days_in_month' => $daysInMonth,
            'leave_deduction' => round($leaveDeduction, 2),
            'payable_salary' => $payableSalary,
        ];
    }

    /**
     * Exact hourly contract calculation from ContractReportController.
     * Gross Intake = hourlySalary * workedHours
     * Revenue Share = round(Gross Intake * revenuePercentage / 100, 2)
     * Payable Salary = round(Gross Intake - Revenue Share, 2)
     */
    public static function calculateContractHourlySalary(float $hourlySalary, float $workedHours, float $revenuePercentage = 0): array
    {
        $hourlySalary = max(0.0, $hourlySalary);
        $workedHours = max(0.0, $workedHours);
        $revenuePercentage = min(100.0, max(0.0, $revenuePercentage));

        $grossIntake = round($hourlySalary * $workedHours, 2);
        $revenueShare = round($grossIntake * $revenuePercentage / 100, 2);
        $candidateSalary = round($grossIntake - $revenueShare, 2);

        return [
            'hourly_salary' => round($hourlySalary, 2),
            'worked_hours' => round($workedHours, 2),
            'revenue_percentage' => round($revenuePercentage, 2),
            'gross_intake' => $grossIntake,
            'revenue_share' => $revenueShare,
            'payable_salary' => $candidateSalary,
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
