<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clean up existing roles before seeding
        Schema::disableForeignKeyConstraints();
        DB::table('roles')->truncate();
        Schema::enableForeignKeyConstraints();
        
        $this->command->info('Roles table cleared successfully.');
        
        $roles = [
            // University roles
            [
                'name' => 'admin',
                'description' => 'System Administrator with full access to all features and settings',
            ],
            [
                'name' => 'manager',
                'description' => 'University Manager who can approve jobs, register students and lecturers, manage system settings',
            ],
            [
                'name' => 'lecturer',
                'description' => 'University Lecturer who can assign tasks, post learning materials, and grade student work',
            ],
            
            // Student role
            [
                'name' => 'student',
                'description' => 'Student who can submit CVs, view lessons, apply for jobs, edit profile, view submission status',
            ],
            
            // Third party roles
            [
                'name' => 'employer',
                'description' => 'Third-party employer who can post jobs, view job approval status, view applicant summaries',
            ],
            
            // Additional role for system viewers if needed
            [
                'name' => 'viewer',
                'description' => 'Read-only access to assigned content and reports',
            ],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
        
        $this->command->info('Roles seeded successfully: ' . count($roles) . ' roles created.');
    }
}
