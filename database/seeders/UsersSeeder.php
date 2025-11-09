<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Code Ninja',
                'email' => 'admin@gmail.com',
                'resource_type' => 'super_admin',
                'password' => bcrypt('123456'),
                'role_id' => 1,
                'is_active' => 1,

            ],
        ];

        foreach ($users as $key => $user) {
            User::create($user);
        }
    }
}
