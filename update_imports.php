<?php

$files = [
    'ClientProfileController' => [
        'path' => 'app/Http/Controllers/backend/ClientProfileController.php',
        'model' => '\App\Models\ClientProfile',
        'fields' => [
            'Account ID' => 'account_id',
            'Client ID' => 'client_id',
            'Service' => 'service_id', // Note: they will select service name! We should map it to ID. But wait, service_id column is string? In ClientProfile it was updated to save ID in UI, but in excel they select name? Actually, if they select name in Excel, they might want to store the name. Wait, the DB column is string. If it's string, we can store the name. 
            // In my update_exports script, I exported service name. 
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
    ],
];

function slugify($text) {
    // replace non letter or digits by _
    $text = preg_replace('~[^\pL\d]+~u', '_', $text);
    return strtolower(trim($text, '_'));
}

foreach ($files as $name => $config) {
    $content = file_get_contents($config['path']);
    
    $mapping = [];
    foreach ($config['fields'] as $heading => $col) {
        $key = slugify($heading);
        if ($col === 'status') {
            $mapping[] = "'status' => strtolower(\$row['$key'] ?? '') === 'active' ? 1 : 0";
        } elseif ($col === 'service_id' && $name === 'ClientProfileController') {
            // Need to lookup service ID by name
            $mapping[] = "'service_id' => \$row['$key'] ? \App\Models\ServiceOffered::where('service_name', \$row['$key'])->value('id') : null";
        } elseif (str_contains($col, 'date') && $col !== 'customer_since') {
            $mapping[] = "'$col' => !empty(\$row['$key']) ? \Carbon\Carbon::parse(\$row['$key'])->format('Y-m-d') : null";
        } else {
            $mapping[] = "'$col' => \$row['$key'] ?? null";
        }
    }
    
    $mappingStr = implode(",\n                ", $mapping);

    $importMethod = "public function import(Request \$request)
    {
        \$request->validate(['import_file' => 'required|file|mimes:xlsx,xls,csv']);
        \$rows = \App\Support\MasterDataSpreadsheet::rows(\$request->file('import_file'));
        
        if (\$rows->isEmpty()) return back()->with('error', 'Empty file');
        
        \$validRows = [];
        foreach (\$rows as \$row) {
            \$validRows[] = [
                $mappingStr
            ];
        }
        
        foreach (\$validRows as \$data) {
            {$config['model']}::create(\$data);
        }
        
        return back()->with('success', 'Imported successfully');
    }";

    $pattern = '/public function import\(Request \$request\).*?private function validatedData/s';
    $replacement = $importMethod . "\n\n    private function validatedData";
    
    $newContent = preg_replace($pattern, $replacement, $content);
    if ($newContent !== null) {
        file_put_contents($config['path'], $newContent);
    }
}
echo "Imports Updated";
