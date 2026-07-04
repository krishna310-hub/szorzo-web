<?php

namespace App\Support;

use App\Imports\SpreadsheetRowsImport;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class MasterDataSpreadsheet
{
    public static function rows(UploadedFile $file): Collection
    {
        $import = new SpreadsheetRowsImport;
        Excel::import($import, $file);

        return ($import->rows ?? collect())->filter(
            fn ($row) => collect($row)->contains(fn ($value) => $value !== null && trim((string) $value) !== '')
        )->values();
    }

    public static function lookup(string $model, string $column, mixed $value): ?int
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        /** @var Model $model */
        return $model::query()
            ->whereRaw('LOWER('.$column.') = ?', [mb_strtolower(trim((string) $value))])
            ->value('id');
    }

    public static function status(mixed $value): ?int
    {
        if ($value === null || trim((string) $value) === '') {
            return 1;
        }

        return match (mb_strtolower(trim((string) $value))) {
            '1', 'active', 'yes', 'true' => 1,
            '0', 'inactive', 'no', 'false' => 0,
            default => null,
        };
    }

    public static function date(mixed $value): ?string
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        try {
            if (is_numeric($value)) {
                return Carbon::instance(ExcelDate::excelToDateTimeObject((float) $value))->format('Y-m-d');
            }

            return Carbon::parse(trim((string) $value))->format('Y-m-d');
        } catch (\Throwable) {
            return trim((string) $value);
        }
    }

    public static function cleanPercent(mixed $value): mixed
    {
        return is_string($value) ? trim(str_replace('%', '', $value)) : $value;
    }

    public static function errors(array $errors, int $limit = 10): string
    {
        $visible = array_slice($errors, 0, $limit);
        $message = implode(' | ', $visible);

        if (count($errors) > $limit) {
            $message .= ' | +'.(count($errors) - $limit).' more error(s).';
        }

        return $message;
    }
}
