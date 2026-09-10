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
     * Generate complete payslip data for a user for a specified month & year.
     */
    public function getPayslipData(User $user, int $month, int $year, array $overrides = []): array
    {
        $start = CarbonImmutable::create($year, $month, 1);
        $daysInMonth = $start->daysInMonth;
        $monthName = $start->format('M');
        $fullMonthName = $start->format('F');

        // Retrieve linked employee record if available
        $employee = null;
        try {
            $employee = Employee::where(function ($query) use ($user) {
                $query->whereRaw('LOWER(official_mail) = ?', [mb_strtolower($user->email)])
                    ->orWhereRaw('LOWER(personal_mail) = ?', [mb_strtolower($user->email)]);
            })->first();
        } catch (\Throwable $e) {
            $employee = null;
        }

        // Attendance stats for this user in this month
        $attendances = collect();
        try {
            $attendances = Attendance::where('user_id', $user->id)
                ->whereBetween('attendance_date', [$start->toDateString(), $start->endOfMonth()->toDateString()])
                ->get();
        } catch (\Throwable $e) {
            $attendances = collect();
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
            $unpaidLeaveCount = LeaveRequest::whereIn('id', $leaveRequestIds)
                ->where('leave_type', 'Unpaid Leave')
                ->count();
        }

        // LOP (Loss of Pay):
        // 1 full day for each absent, 0.5 day for each half-day, 1 day for unpaid leave
        $autoLop = $absentCount + ($halfDayCount * 0.5) + $unpaidLeaveCount;

        // If attendance was not marked at all yet for this user/month, default to 0 (or allow override)
        if ($attendances->isEmpty() && !isset($overrides['lop'])) {
            $lop = 0.0;
        } else {
            $lop = isset($overrides['lop']) && is_numeric($overrides['lop'])
                ? (float) $overrides['lop']
                : (float) $autoLop;
        }

        $effectiveWorkDays = max(0, $daysInMonth - $lop);

        $otHours = isset($overrides['ot_hours']) && is_numeric($overrides['ot_hours'])
            ? (float) $overrides['ot_hours']
            : 0.0;

        // Ratio of effective work days to total days in the month
        $ratio = $daysInMonth > 0 ? ($effectiveWorkDays / $daysInMonth) : 1;

        // Salary structure: from employee attributes or standard 38,050 INR breakdown
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

        // Identity and demographic fields
        $employeeNo = $employee?->employee_no ?: ('SZ' . str_pad((string) $user->id, 3, '0', STR_PAD_LEFT));
        $employeeName = $employee?->employee_name ?: $user->name;
        $fatherName = $employee?->fathers_name ?: 'Padmanabha';
        $gender = $employee?->gender ? strtoupper(substr($employee->gender, 0, 1)) : 'F';
        $designation = $employee?->designation ?: ($user->role?->name ?: 'Senior Recruiter');
        $pfNo = $employee?->bank_uan_pf_number ?: ($employee?->employee_uan_pf_number ?: 'BGMRD36445920000010003');
        $pfUan = $employee?->employee_uan_pf_number ?: '102311451956';
        $esiNo = $employee?->employee_esi_number ?: 'NA';
        $location = 'Bangalore';
        $remarks = $employee?->salary_remarks ?: 'NA';

        // Logo base64 encode for reliable DomPDF embedding
        $logoPath = public_path('frontend/images/rhino-logo.png');
        $logoBase64 = null;
        if (file_exists($logoPath)) {
            $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
        }

        return [
            'user' => $user,
            'employee' => $employee,
            'month' => $month,
            'year' => $year,
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
