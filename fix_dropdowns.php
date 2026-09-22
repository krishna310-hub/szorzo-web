<?php

$files = [
    'app/Http/Controllers/backend/ClientProfileController.php',
    'app/Http/Controllers/backend/LeadGenerationController.php'
];

foreach ($files as $path) {
    $content = file_get_contents($path);
    
    // Replace the Country, State, City dropdowns to include a fallback
    $content = str_replace(
        '\App\Models\Country::pluck("name")->toArray()',
        '\App\Models\Country::pluck("name")->toArray() ?: ["-- No Data --"]',
        $content
    );
    
    $content = str_replace(
        '\App\Models\State::pluck("name")->toArray()',
        '\App\Models\State::pluck("name")->toArray() ?: ["-- No Data --"]',
        $content
    );
    
    $content = str_replace(
        '\App\Models\City::pluck("name")->toArray()',
        '\App\Models\City::pluck("name")->toArray() ?: ["-- No Data --"]',
        $content
    );
    
    $content = str_replace(
        '\App\Models\Division::where("status", 1)->pluck("name")->toArray()',
        '\App\Models\Division::where("status", 1)->pluck("name")->toArray() ?: ["-- No Data --"]',
        $content
    );
    
    $content = str_replace(
        '\App\Models\ServiceOffered::where("status", 1)->pluck("service_name")->toArray()',
        '\App\Models\ServiceOffered::where("status", 1)->pluck("service_name")->toArray() ?: ["-- No Data --"]',
        $content
    );
    
    file_put_contents($path, $content);
}

echo "Dropdown fallbacks added.";
