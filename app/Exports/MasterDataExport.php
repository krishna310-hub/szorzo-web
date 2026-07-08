<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\NamedRange;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MasterDataExport implements FromCollection, ShouldAutoSize, WithEvents, WithHeadings, WithStyles
{
    public function __construct(
        private readonly array $headings,
        private readonly array $rows = [],
        private readonly array $dropdowns = [],
    ) {}

    public function collection(): Collection
    {
        return collect($this->rows);
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->freezePane('A2');
        $sheet->setAutoFilter($sheet->calculateWorksheetDimension());

        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => 'solid',
                    'startColor' => ['rgb' => '405189'],
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                if ($this->dropdowns === []) {
                    return;
                }

                $sheet = $event->sheet->getDelegate();
                $spreadsheet = $sheet->getParent();
                $optionsSheet = $spreadsheet->createSheet();
                $optionsSheet->setTitle('Options');
                $optionsSheet->setSheetState(Worksheet::SHEETSTATE_VERYHIDDEN);

                $optionColumn = 1;

                foreach ($this->dropdowns as $heading => $values) {
                    $columnIndex = array_search($heading, $this->headings, true);
                    $values = $this->cleanDropdownValues($values);

                    if ($columnIndex === false || $values === []) {
                        continue;
                    }

                    $optionColumnLetter = Coordinate::stringFromColumnIndex($optionColumn);
                    $optionsSheet->setCellValue($optionColumnLetter.'1', $heading);

                    foreach ($values as $rowIndex => $value) {
                        $optionsSheet->setCellValue($optionColumnLetter.($rowIndex + 2), $value);
                    }

                    $rangeName = $this->rangeName($heading, $optionColumn);
                    $lastRow = count($values) + 1;
                    $spreadsheet->addNamedRange(new NamedRange(
                        $rangeName,
                        $optionsSheet,
                        '$'.$optionColumnLetter.'$2:$'.$optionColumnLetter.'$'.$lastRow
                    ));

                    $this->applyDropdownValidation(
                        $sheet,
                        Coordinate::stringFromColumnIndex($columnIndex + 1),
                        $rangeName,
                        $heading
                    );

                    $optionColumn++;
                }

                $optionsSheet->getProtection()->setSheet(true);
                $optionsSheet->getProtection()->setPassword(bin2hex(random_bytes(8)));
                $optionsSheet->getProtection()->setSelectLockedCells(false);
            },
        ];
    }

    private function cleanDropdownValues(array|Collection $values): array
    {
        return collect($values)
            ->map(fn ($value) => trim((string) $value))
            ->filter(fn ($value) => $value !== '')
            ->unique()
            ->values()
            ->all();
    }

    private function rangeName(string $heading, int $optionColumn): string
    {
        $name = preg_replace('/[^A-Za-z0-9_]/', '_', $heading);
        $name = trim((string) $name, '_');

        if ($name === '' || is_numeric($name[0])) {
            $name = 'List_'.$optionColumn;
        }

        return $name.'_Options';
    }

    private function applyDropdownValidation(Worksheet $sheet, string $column, string $rangeName, string $heading): void
    {
        $validation = $sheet->getCell($column.'2')->getDataValidation();
        $validation->setType(DataValidation::TYPE_LIST);
        $validation->setErrorStyle(DataValidation::STYLE_STOP);
        $validation->setAllowBlank(true);
        $validation->setShowInputMessage(true);
        $validation->setShowErrorMessage(true);
        $validation->setShowDropDown(true);
        $validation->setErrorTitle('Invalid option');
        $validation->setError('Please choose a value from the dropdown list.');
        $validation->setPromptTitle('Select '.$heading);
        $validation->setPrompt('Choose a value from the list.');
        $validation->setFormula1('='.$rangeName);

        for ($row = 2; $row <= 1001; $row++) {
            $sheet->getCell($column.$row)->setDataValidation(clone $validation);
        }
    }
}
