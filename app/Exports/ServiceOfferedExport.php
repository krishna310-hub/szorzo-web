<?php

namespace App\Exports;

use App\Models\ServiceOffered;
use Maatwebsite\Excel\Concerns\FromCollection;

class ServiceOfferedExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return ServiceOffered::all();
    }
}
