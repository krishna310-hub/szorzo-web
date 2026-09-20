<?php

namespace App\Exports;

use App\Models\BusinessIntelligence;
use Maatwebsite\Excel\Concerns\FromCollection;

class BusinessIntelligenceExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return BusinessIntelligence::all();
    }
}
