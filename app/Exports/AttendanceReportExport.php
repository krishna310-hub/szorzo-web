<?php
namespace App\Exports;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
class AttendanceReportExport implements FromCollection, WithHeadings, WithMapping {
    public function __construct(private Collection $records) {}
    public function collection(): Collection { return $this->records; }
    public function headings(): array
    {
        return [
            'Date',
            'Employee ID',
            'Employee / User',
            'Email',
            'Attendance Status',
            'Timer Status',
            'Check In',
            'Check Out',
            'Working Hours',
            'Break Time (Tea)',
            'Lunch Time',
            'Total Break Time',
            'Leave Type',
            'Remarks',
        ];
    }

    public function map($row): array
    {
        return [
            $row->attendance_date ? $row->attendance_date->format('Y-m-d') : '',
            $row->employee_no ?? '—',
            $row->display_name,
            $row->user?->email ?? $row->employee?->official_mail ?? '—',
            str_replace('_', ' ', ucwords((string) $row->status, '_')),
            str_replace('_', ' ', ucwords((string) ($row->timer_status ?? 'not_started'), '_')),
            $row->check_in ? substr((string) $row->check_in, 0, 5) : '—',
            $row->check_out ? substr((string) $row->check_out, 0, 5) : '—',
            $row->formatted_work_time,
            $row->formatted_break_time,
            $row->formatted_lunch_time,
            $row->formatted_total_break_time,
            $row->leaveRequest?->leave_type ?? '—',
            $row->remarks ?? '',
        ];
    }
}
