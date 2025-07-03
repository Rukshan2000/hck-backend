<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Menu;
use App\Models\RoleMenu;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all roles and menus
        $adminRole = Role::where('name', 'admin')->first();
        $managerRole = Role::where('name', 'manager')->first();
        $employeeRole = Role::where('name', 'employee')->first();
        $viewerRole = Role::where('name', 'viewer')->first();

        $allMenus = Menu::all();

        // Admin gets access to all menus
        foreach ($allMenus as $menu) {
            RoleMenu::create([
                'role_id' => $adminRole->id,
                'menu_id' => $menu->id,
            ]);
        }

        // Manager gets access to most menus except user/role management
        $managerMenus = Menu::whereNotIn('path', ['/users', '/roles'])->get();
        foreach ($managerMenus as $menu) {
            RoleMenu::create([
                'role_id' => $managerRole->id,
                'menu_id' => $menu->id,
            ]);
        }

        // Employee gets access to basic menus
        $employeeMenus = Menu::whereIn('path', ['/dashboard', '/my-tasks', '/profile'])->get();
        foreach ($employeeMenus as $menu) {
            RoleMenu::create([
                'role_id' => $employeeRole->id,
                'menu_id' => $menu->id,
            ]);
        }

        // Viewer gets access to read-only menus
        $viewerMenus = Menu::whereIn('path', ['/dashboard', '/my-tasks', '/reports', '/profile'])->get();
        foreach ($viewerMenus as $menu) {
            RoleMenu::create([
                'role_id' => $viewerRole->id,
                'menu_id' => $menu->id,
            ]);
        }
    }
}
