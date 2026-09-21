<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $json = file_get_contents(database_path('data/locations.json'));
        $countries = json_decode($json, true);

        foreach ($countries as $countryData) {
            $country = \App\Models\Country::firstOrCreate(['name' => $countryData['name']]);
            foreach ($countryData['states'] as $stateData) {
                $state = \App\Models\State::firstOrCreate([
                    'country_id' => $country->id,
                    'name' => $stateData['name']
                ]);
                foreach ($stateData['cities'] as $cityData) {
                    \App\Models\City::firstOrCreate([
                        'state_id' => $state->id,
                        'name' => $cityData['name']
                    ]);
                }
            }
        }
    }
}
