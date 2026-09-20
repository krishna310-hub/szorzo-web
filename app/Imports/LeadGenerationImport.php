<?php

namespace App\Imports;

use App\Models\LeadGeneration;
use Maatwebsite\Excel\Concerns\ToModel;

class LeadGenerationImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new LeadGeneration([
            //
        ]);
    }
}
