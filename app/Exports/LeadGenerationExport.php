<?php

namespace App\Exports;

use App\Models\LeadGeneration;
use Maatwebsite\Excel\Concerns\FromCollection;

class LeadGenerationExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return LeadGeneration::all();
    }
}
