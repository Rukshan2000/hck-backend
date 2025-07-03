<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            MenuSeeder::class,
            RoleMenuSeeder::class,
        ]);

        // Create a default admin user
        \App\Models\User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role_id' => 1, // Admin role
        ]);

        // Create some test users
        \App\Models\User::factory(10)->create();
    }
}
