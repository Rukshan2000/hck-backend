<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $menus = [
            [
                'name' => 'Dashboard',
                'path' => '/dashboard',
                'icon' => 'dashboard',
                'sort_order' => 1,
            ],
            [
                'name' => 'Users Management',
                'path' => '/users',
                'icon' => 'users',
                'sort_order' => 2,
            ],
            [
                'name' => 'Roles Management',
                'path' => '/roles',
                'icon' => 'shield',
                'sort_order' => 3,
            ],
            [
                'name' => 'Tasks',
                'path' => '/tasks',
                'icon' => 'tasks',
                'sort_order' => 4,
            ],
            [
                'name' => 'My Tasks',
                'path' => '/my-tasks',
                'icon' => 'task',
                'sort_order' => 5,
            ],
            [
                'name' => 'Reports',
                'path' => '/reports',
                'icon' => 'chart',
                'sort_order' => 6,
            ],
            [
                'name' => 'Settings',
                'path' => '/settings',
                'icon' => 'settings',
                'sort_order' => 7,
            ],
            [
                'name' => 'Profile',
                'path' => '/profile',
                'icon' => 'user',
                'sort_order' => 8,
            ],
        ];

        foreach ($menus as $menu) {
            Menu::create($menu);
        }
    }
}
