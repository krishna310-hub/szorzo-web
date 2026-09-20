<?php

namespace App\Imports;

use App\Models\BusinessIntelligence;
use Maatwebsite\Excel\Concerns\ToModel;

class BusinessIntelligenceImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new BusinessIntelligence([
            //
        ]);
    }
}
