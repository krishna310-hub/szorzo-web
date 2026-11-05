<?php

namespace App\Exports;

use App\Models\Employee;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EmployeeExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    private int $rowIndex = 0;

    public function __construct(private Collection $employees) {}

    public function collection(): Collection
    {
        return $this->employees;
    }

    public function headings(): array
    {
        return [
            'S.No',
            'Employee ID',
            'Employee Name',
            'Client',
            'Job Role',
            'Date of Joining (DOJ)',
            'Date of Relieving (DOR)',
            'TDS (₹)',
            'PF (₹)',
            'TDS or PF Summary',
            'Mode / Employment Type (Contract or FTE)',
            'Salary Per Month (₹)',
            'Annual CTC (₹)',
            'Status',
        ];
    }

    public function map($employee): array
    {
        $this->rowIndex++;

        // Candidate match for fallback
        $candidate = $employee->relationLoaded('candidate')
            ? $employee->candidate
            : $employee->getMatchingCandidate();

        // Salary calculations
        $salData = $employee->getContractReportSalary();
        $salary = (float) ($salData['salary'] > 0
            ? $salData['salary']
            : ($employee->monthly_gross > 0 ? $employee->monthly_gross : $employee->monthly_salary));

        // TDS & PF
        $tds = (float) $employee->income_tax;
        if ($tds <= 0 && $salary > 0) {
            $tds = round($salary * 0.10, 2);
        }
        $pf = (float) $employee->pf_deduction;

        // Mode / Employment Type (Contract / FTE)
        $mode = $employee->mode?->mode
            ?: ($employee->employment_type
                ?: ($candidate?->mode?->mode ?: 'Contract'));

        // Client & Job Role
        $client = $employee->client?->client
            ?: ($candidate?->client?->client ?: '—');
        $jobRole = $employee->designation
            ?: ($candidate?->jobRole?->job_role ?: 'Staff');

        // Dates
        $doj = $employee->date_of_joining
            ? $employee->date_of_joining->format('d-m-Y')
            : ($employee->contract_from_date ? $employee->contract_from_date->format('d-m-Y') : '—');

        $dor = '-';
        if (!empty($employee->relieving_date)) {
            if ($employee->relieving_date instanceof \DateTimeInterface) {
                $dor = $employee->relieving_date->format('d-m-Y');
            } elseif (is_string($employee->relieving_date) && trim($employee->relieving_date) !== '' && trim($employee->relieving_date) !== '0000-00-00') {
                try {
                    $dor = \Carbon\Carbon::parse($employee->relieving_date)->format('d-m-Y');
                } catch (\Throwable $e) {
                    $dor = '-';
                }
            }
        }

        // CTC
        $ctc = (float) ($candidate?->onboarding_ctc > 0
            ? $candidate->onboarding_ctc
            : ($candidate?->current_ctc > 0 ? $candidate->current_ctc : round($salary * 12, 2)));

        return [
            $this->rowIndex,
            $employee->employee_no ?: ('SZ' . str_pad($employee->id, 3, '0', STR_PAD_LEFT)),
            $employee->employee_name,
            $client,
            $jobRole,
            $doj,
            $dor,
            number_format($tds, 2, '.', ''),
            number_format($pf, 2, '.', ''),
            'TDS: ₹' . number_format($tds, 2) . ' | PF: ₹' . number_format($pf, 2),
            $mode,
            number_format($salary, 2, '.', ''),
            number_format($ctc, 2, '.', ''),
            $employee->status ? 'Active' : 'Inactive',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}

