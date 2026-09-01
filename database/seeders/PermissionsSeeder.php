<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [

            ['name' => 'Read',      'page' => 'dashboard'],

            ['name' => 'Read',      'page' => 'profile'],
            ['name' => 'Edit',      'page' => 'profile'],

            ['name' => 'Read',      'page' => 'role'],
            ['name' => 'Create',    'page' => 'role'],
            ['name' => 'Edit',      'page' => 'role'],
            ['name' => 'Delete',    'page' => 'role'],

            ['name' => 'Read',      'page' => 'user'],
            ['name' => 'Create',    'page' => 'user'],
            ['name' => 'Edit',      'page' => 'user'],
            ['name' => 'Delete',    'page' => 'user'],

            ['name' => 'General setting',      'page' => 'settings'],
            ['name' => 'Email setting',    'page' => 'settings'],
            ['name' => 'Social Media setting',      'page' => 'settings'],

            ['name' => 'Read',      'page' => 'landing_page'],
            ['name' => 'Create',    'page' => 'landing_page'],
            ['name' => 'Edit',      'page' => 'landing_page'],
            ['name' => 'Delete',    'page' => 'landing_page'],

            ['name' => 'Read',      'page' => 'enquiry'],
            ['name' => 'Edit',      'page' => 'enquiry'],
            ['name' => 'Delete',    'page' => 'enquiry'],

            ['name' => 'Sitemap & Robots',      'page' => 'settings'],

            ['name' => 'Read',      'page' => 'client'],
            ['name' => 'Create',    'page' => 'client'],
            ['name' => 'Edit',      'page' => 'client'],
            ['name' => 'Delete',    'page' => 'client'],

            ['name' => 'Read',      'page' => 'interview_level'],
            ['name' => 'Create',    'page' => 'interview_level'],
            ['name' => 'Edit',      'page' => 'interview_level'],
            ['name' => 'Delete',    'page' => 'interview_level'],

            ['name' => 'Read',      'page' => 'location'],
            ['name' => 'Create',    'page' => 'location'],
            ['name' => 'Edit',      'page' => 'location'],
            ['name' => 'Delete',    'page' => 'location'],

            ['name' => 'Read',      'page' => 'division'],
            ['name' => 'Create',    'page' => 'division'],
            ['name' => 'Edit',      'page' => 'division'],
            ['name' => 'Delete',    'page' => 'division'],

            ['name' => 'Read',      'page' => 'recruiter'],
            ['name' => 'Create',    'page' => 'recruiter'],
            ['name' => 'Edit',      'page' => 'recruiter'],
            ['name' => 'Delete',    'page' => 'recruiter'],

            ['name' => 'Read',      'page' => 'job_role'],
            ['name' => 'Create',    'page' => 'job_role'],
            ['name' => 'Edit',      'page' => 'job_role'],
            ['name' => 'Delete',    'page' => 'job_role'],

            ['name' => 'Read',      'page' => 'mode'],
            ['name' => 'Create',    'page' => 'mode'],
            ['name' => 'Edit',      'page' => 'mode'],
            ['name' => 'Delete',    'page' => 'mode'],

            ['name' => 'Read',      'page' => 'client_job_role'],
            ['name' => 'Create',    'page' => 'client_job_role'],
            ['name' => 'Edit',      'page' => 'client_job_role'],
            ['name' => 'Delete',    'page' => 'client_job_role'],

            ['name' => 'Read',      'page' => 'client_requirement'],
            ['name' => 'Create',    'page' => 'client_requirement'],
            ['name' => 'Edit',      'page' => 'client_requirement'],
            ['name' => 'Delete',    'page' => 'client_requirement'],

            ['name' => 'Read',      'page' => 'candidate'],
            ['name' => 'Create',    'page' => 'candidate'],
            ['name' => 'Edit',      'page' => 'candidate'],
            ['name' => 'Delete',    'page' => 'candidate'],

            ['name' => 'Read',      'page' => 'profile_sourced'],
            ['name' => 'Create',    'page' => 'profile_sourced'],
            ['name' => 'Edit',      'page' => 'profile_sourced'],
            ['name' => 'Delete',    'page' => 'profile_sourced'],

            ['name' => 'Read',      'page' => 'billing'],
            ['name' => 'Create',    'page' => 'billing'],
            ['name' => 'Edit',      'page' => 'billing'],
            ['name' => 'Delete',    'page' => 'billing'],

            ['name' => 'Read',      'page' => 'employee'],
            ['name' => 'Create',    'page' => 'employee'],
            ['name' => 'Edit',      'page' => 'employee'],
            ['name' => 'Delete',    'page' => 'employee'],

            ['name' => 'Read',      'page' => 'interview_mode'],
            ['name' => 'Create',    'page' => 'interview_mode'],
            ['name' => 'Edit',      'page' => 'interview_mode'],
            ['name' => 'Delete',    'page' => 'interview_mode'],

            ['name' => 'Read',      'page' => 'target'],
            ['name' => 'Create',    'page' => 'target'],
            ['name' => 'Edit',      'page' => 'target'],
            ['name' => 'Delete',    'page' => 'target'],

            ['name' => 'Read',      'page' => 'reports'],
            ['name' => 'Export',    'page' => 'reports'],

            ['name' => 'Read',      'page' => 'contract_report'],
            ['name' => 'Export',    'page' => 'contract_report'],

            ['name' => 'Read',      'page' => 'revenue'],
            ['name' => 'Create',    'page' => 'revenue'],
            ['name' => 'Edit',      'page' => 'revenue'],
            ['name' => 'Download',  'page' => 'revenue'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate($permission);
        }
    }
}
