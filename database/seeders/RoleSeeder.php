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
        $roles = [
            [
                'name' => 'admin',
                'description' => 'System Administrator with full access to all features',
            ],
            [
                'name' => 'manager',
                'description' => 'Manager with access to team management and reporting features',
            ],
            [
                'name' => 'employee',
                'description' => 'Regular employee with basic access to tasks and profile management',
            ],
            [
                'name' => 'viewer',
                'description' => 'Read-only access to assigned tasks and reports',
            ],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}
