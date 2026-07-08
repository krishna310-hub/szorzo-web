<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class SpreadsheetRowsImport implements ToCollection, WithHeadingRow, WithMultipleSheets
{
    public Collection $rows;

    public function sheets(): array
    {
        return [0 => $this];
    }

    public function collection(Collection $rows): void
    {
        $this->rows = $rows;
    }
}
