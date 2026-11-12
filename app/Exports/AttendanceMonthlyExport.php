<?php

namespace App\Exports;

use App\Models\Attendance;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AttendanceMonthlyExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    private int $rowIndex = 0;

    public function __construct(
        private Collection $users,
        private array $days,
        private int $month,
        private int $year
    ) {}

    public function collection(): Collection
    {
        return $this->users;
    }

    public function headings(): array
    {
        $headings = [
            'S.No',
            'Employee ID',
            'Employee Name',
            'Email',
        ];

        foreach ($this->days as $day) {
            $headings[] = (string) $day;
        }

        $headings[] = 'Present (P)';
        $headings[] = 'Absent (A)';
        $headings[] = 'Half Day (HD)';
        $headings[] = 'Leave (L)';
        $headings[] = 'Holiday (H)';
        $headings[] = 'Week Off (WO)';
        $headings[] = 'Unmarked (UM)';
        $headings[] = 'Total Working Days';
        $headings[] = 'Total Work Hours (Worked / Required)';

        return $headings;
    }

    public function map($user): array
    {
        $this->rowIndex++;
        $map = $user->attendances->keyBy(fn ($a) => (int) $a->attendance_date->day);
        $totals = array_fill_keys(Attendance::STATUSES, 0);

        $row = [
            $this->rowIndex,
            $user->employee?->employee_no ?? '—',
            $user->name,
            $user->email,
        ];

        $statusCodeMap = [
            'present' => 'P',
            'absent' => 'A',
            'half_day' => 'HD',
            'on_leave' => 'L',
            'holiday' => 'H',
            'week_off' => 'WO',
            'unmarked' => '—',
        ];

        foreach ($this->days as $day) {
            $att = $map->get($day);
            $st = $att?->status ?? 'unmarked';
            $totals[$st] = ($totals[$st] ?? 0) + 1;
            $row[] = $statusCodeMap[$st] ?? '—';
        }

        $row[] = $totals['present'] ?? 0;
        $row[] = $totals['absent'] ?? 0;
        $row[] = $totals['half_day'] ?? 0;
        $row[] = $totals['on_leave'] ?? 0;
        $row[] = $totals['holiday'] ?? 0;
        $row[] = $totals['week_off'] ?? 0;
        $row[] = $totals['unmarked'] ?? 0;
        $row[] = ($totals['present'] ?? 0) + (($totals['half_day'] ?? 0) * 0.5);
        $totalWorkSecs = $user->attendances->sum(fn ($a) => $a->current_work_seconds);
        $uFirstDate = $user->attendances->filter(fn ($a) => !empty($a->check_in) || in_array($a->status, ['present', 'half_day'], true) || $a->current_work_seconds > 0)->min(fn ($a) => $a->attendance_date?->toDateString()) ?? ($user->employee?->date_of_joining ? \Carbon\Carbon::parse($user->employee->date_of_joining)->toDateString() : null);
        $totalReqSecs = $user->attendances->sum(function ($a) use ($uFirstDate) {
            $aDate = $a->attendance_date?->toDateString();
            if ($uFirstDate && $aDate && $aDate < $uFirstDate) return 0;
            if (in_array($a->status, ['week_off', 'holiday', 'on_leave'], true) || !empty($a->leave_request_id)) return 0;
            return $a->status === 'half_day' ? 14400 : 28800;
        });
        $row[] = Attendance::formatSecondsHuman($totalWorkSecs) . ' / ' . Attendance::formatSecondsHuman($totalReqSecs);

        return $row;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}

