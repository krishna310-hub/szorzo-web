<?php

$files = [
    'ClientProfileController' => [
        'path' => 'app/Http/Controllers/backend/ClientProfileController.php',
        'model' => '\App\Models\ClientProfile',
        'fields' => [
            'Account ID' => 'account_id',
            'Client ID' => 'client_id',
            'Service' => 'service_id',
            'Legal Entity Name' => 'legal_entity_name',
            'Account Name (Display)' => 'account_name',
            'Industry' => 'industry',
            'Sub Industry' => 'sub_industry',
            'Website URL' => 'website_url',
            'Country of Origin' => 'country_of_origin',
            'Region' => 'region',
            'State' => 'state',
            'City' => 'city',
            'PIN Code' => 'pin_code',
            'Registered Address' => 'registered_address',
            'Ownership Type' => 'ownership_type',
            'Registration / Entity ID' => 'registration_id',
            'GSTIN / Tax ID' => 'gstin',
            'Account / Lead Source' => 'account_source',
            'Customer Domain' => 'customer_domain',
            'Account Owner' => 'account_owner',
            'Relationship Manager' => 'relationship_manager',
            'Customer Since' => 'customer_since',
            'Account Created Date' => 'account_created_date',
            'Last Updated Date' => 'last_updated_date',
            'Relationship Status' => 'relationship_status',
            'Primary Contact Name & Designation' => 'primary_contact_name_designation',
            'Primary Email' => 'primary_email',
            'Primary Contact Number' => 'primary_contact_number',
            'Status' => 'status',
        ],
        'dropdowns' => [
            'Industry' => '\App\Models\Division::where("status", 1)->pluck("name")->toArray()',
            'Service' => '\App\Models\ServiceOffered::where("status", 1)->pluck("service_name")->toArray()',
            'Country of Origin' => '\App\Models\Country::pluck("name")->toArray()',
            'State' => '\App\Models\State::pluck("name")->toArray()',
            'City' => '\App\Models\City::pluck("name")->toArray()',
            'Status' => '["Active", "Inactive"]',
        ]
    ],
    'LeadGenerationController' => [
        'path' => 'app/Http/Controllers/backend/LeadGenerationController.php',
        'model' => '\App\Models\LeadGeneration',
        'fields' => [
            'Account ID' => 'account_id',
            'Account / Lead Source' => 'account_source',
            'Account Name (Display)' => 'account_name',
            'Industry' => 'industry',
            'Sub Industry' => 'sub_industry',
            'Website URL' => 'website_url',
            'Country of Origin' => 'country_of_origin',
            'Region' => 'region',
            'State' => 'state',
            'City' => 'city',
            'PIN Code' => 'pin_code',
            'Registered Address' => 'registered_address',
            'Ownership Type' => 'ownership_type',
            'Registration / Entity ID' => 'registration_id',
            'GSTIN / Tax ID' => 'gstin',
            'Account Owner' => 'account_owner',
            'Relationship Manager' => 'relationship_manager',
            'Customer Since' => 'customer_since',
            'Account Created Date' => 'account_created_date',
            'Last Updated Date' => 'last_updated_date',
            'Status' => 'status',
        ],
        'dropdowns' => [
            'Industry' => '\App\Models\Division::where("status", 1)->pluck("name")->toArray()',
            'Country of Origin' => '\App\Models\Country::pluck("name")->toArray()',
            'State' => '\App\Models\State::pluck("name")->toArray()',
            'City' => '\App\Models\City::pluck("name")->toArray()',
            'Status' => '["Active", "Inactive"]',
        ]
    ],
    'BusinessIntelligenceController' => [
        'path' => 'app/Http/Controllers/backend/BusinessIntelligenceController.php',
        'model' => '\App\Models\BusinessIntelligence',
        'fields' => [
            'Contact ID' => 'contact_id',
            'Account ID' => 'account_id',
            'Account Name' => 'account_name',
            'Contact Name' => 'contact_name',
            'Designation' => 'designation',
            'Department' => 'department',
            'Mobile Number' => 'mobile_number',
            'Alternate Contact' => 'alternate_contact',
            'Email ID' => 'email_id',
            'Contact Type' => 'contact_type',
            'Last Contacted Date' => 'last_contacted_date',
            'Next Follow-up Date' => 'next_follow_up_date',
            'Contact Notes' => 'contact_notes',
            'Status' => 'status',
        ],
        'dropdowns' => [
            'Status' => '["Active", "Inactive"]',
        ]
    ],
    'ServiceOfferedController' => [
        'path' => 'app/Http/Controllers/backend/ServiceOfferedController.php',
        'model' => '\App\Models\ServiceOffered',
        'fields' => [
            'Service Name' => 'service_name',
            'Service Code' => 'service_code',
            'Category' => 'category',
            'Description' => 'description',
            'Display Order' => 'display_order',
            'Status' => 'status',
        ],
        'dropdowns' => [
            'Status' => '["Active", "Inactive"]',
        ]
    ],
];

foreach ($files as $name => $config) {
    $content = file_get_contents($config['path']);
    
    $headings = array_keys($config['fields']);
    $headingsExport = var_export($headings, true);
    
    // Generate data mapping
    $mapping = [];
    foreach ($config['fields'] as $heading => $col) {
        if ($col === 'status') {
            $mapping[] = '$row->status ? "Active" : "Inactive"';
        } elseif (str_contains($col, 'date') || $col === 'customer_since') {
            $mapping[] = '$row->' . $col . ' ? \Carbon\Carbon::parse($row->' . $col . ')->format("Y-m-d") : null';
        } elseif ($col === 'service_id' && $name === 'ClientProfileController') {
            $mapping[] = '$row->service_id ? \App\Models\ServiceOffered::find($row->service_id)?->service_name : null';
        } else {
            $mapping[] = '$row->' . $col;
        }
    }
    $mappingCode = implode(",\n                ", $mapping);
    
    // Generate dropdowns
    $dropdownCode = "[\n";
    foreach ($config['dropdowns'] as $heading => $dcode) {
        $dropdownCode .= "            '$heading' => $dcode,\n";
    }
    $dropdownCode .= "        ]";
    
    $exportMethod = "public function export()
    {
        \$data = {$config['model']}::all()->map(function (\$row) {
            return [
                $mappingCode
            ];
        });
        
        \$dropdowns = $dropdownCode;

        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\MasterDataExport(
            $headingsExport,
            \$data->toArray(),
            \$dropdowns
        ), '{$name}-export.xlsx');
    }";

    $importTemplateMethod = "public function importTemplate()
    {
        \$dropdowns = $dropdownCode;

        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\MasterDataExport(
            $headingsExport,
            [],
            \$dropdowns
        ), '{$name}-template.xlsx');
    }";
    
    // Replace the old export and importTemplate methods
    // We will use regex to replace them
    $pattern = '/public function export\(\).*?public function importTemplate\(\).*?public function import\(Request \$request\)/s';
    $replacement = $exportMethod . "\n\n    " . $importTemplateMethod . "\n\n    public function import(Request \$request)";
    
    $newContent = preg_replace($pattern, $replacement, $content);
    if ($newContent !== null) {
        file_put_contents($config['path'], $newContent);
    }
}
echo "Done";
