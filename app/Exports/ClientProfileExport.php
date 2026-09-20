<?php

namespace App\Exports;

use App\Models\ClientProfile;
use Maatwebsite\Excel\Concerns\FromCollection;

class ClientProfileExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return ClientProfile::all();
    }
}
