<?php
require 'vendor/autoload.php';
require 'bootstrap/app.php';

$headings = ['Country of Origin', 'State', 'City', 'Status'];
$dropdowns = [
    'Country of Origin' => ['USA', 'India'],
    'State' => ['NY', 'CA', 'Delhi'],
    'City' => ['NYC', 'LA', 'New Delhi'],
    'Status' => ['Active', 'Inactive']
];

$export = new \App\Exports\MasterDataExport($headings, [], $dropdowns);
\Maatwebsite\Excel\Facades\Excel::store($export, 'test_export.xlsx');
echo "Stored test_export.xlsx\n";
