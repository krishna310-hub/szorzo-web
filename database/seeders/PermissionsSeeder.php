<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            
            ['name'=>'Read',      'page'=>'dashboard'],

            ['name'=>'Read',      'page'=>'profile'],
            ['name'=>'Edit',      'page'=>'profile'],

            ['name'=>'Read',      'page'=>'role'],
            ['name'=>'Create',    'page'=>'role'],
            ['name'=>'Edit',      'page'=>'role'],
            ['name'=>'Delete',    'page'=>'role'],

            ['name'=>'Read',      'page'=>'user'],
            ['name'=>'Create',    'page'=>'user'],
            ['name'=>'Edit',      'page'=>'user'],
            ['name'=>'Delete',    'page'=>'user'],

            ['name'=>'General setting',      'page'=>'settings'],
            ['name'=>'Email setting',    'page'=>'settings'],
            ['name'=>'Social Media setting',      'page'=>'settings'],
        ];
        
        foreach ($permissions as $permission) {
            Permission::firstOrCreate($permission);
        }
    }
}
