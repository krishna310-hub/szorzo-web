<?php

namespace App\Imports;

use App\Models\ClientProfile;
use Maatwebsite\Excel\Concerns\ToModel;

class ClientProfileImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new ClientProfile([
            //
        ]);
    }
}
