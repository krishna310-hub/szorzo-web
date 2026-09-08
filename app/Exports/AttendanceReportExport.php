<?php
namespace App\Exports;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
class AttendanceReportExport implements FromCollection, WithHeadings, WithMapping {
    public function __construct(private Collection $records) {}
    public function collection(): Collection { return $this->records; }
    public function headings(): array { return ['Date','User','Email','Status','Check In','Check Out','Working Hours','Leave Type','Remarks']; }
    public function map($row): array { return [$row->attendance_date->format('Y-m-d'),$row->user->name,$row->user->email,str_replace('_',' ',ucwords($row->status,'_')),$row->check_in,$row->check_out,sprintf('%d:%02d',intdiv($row->working_minutes,60),$row->working_minutes%60),$row->leaveRequest?->leave_type,$row->remarks]; }
}
