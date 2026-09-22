<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Countries: " . \App\Models\Country::count() . "\n";
echo "States: " . \App\Models\State::count() . "\n";
echo "Cities: " . \App\Models\City::count() . "\n";
