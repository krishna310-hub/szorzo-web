<?php

namespace App\Imports;

use App\Models\ServiceOffered;
use Maatwebsite\Excel\Concerns\ToModel;

class ServiceOfferedImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new ServiceOffered([
            //
        ]);
    }
}
