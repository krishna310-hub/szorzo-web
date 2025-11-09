<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles  = [
            ['name' => 'Super Admin', 'access_level' => 'super_admin'],
        ];
        foreach ($roles as $key => $list_of_role) {
            Role::firstOrCreate($list_of_role);
        }
    }
}
