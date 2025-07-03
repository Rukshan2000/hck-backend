<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\Role;
use App\Models\RoleMenu;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class RoleMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clean existing records
        Schema::disableForeignKeyConstraints();
        RoleMenu::truncate();
        Schema::enableForeignKeyConstraints();

        // Get role instances
        $adminRole = Role::where('name', 'admin')->first();
        $managerRole = Role::where('name', 'manager')->first();
        $lecturerRole = Role::where('name', 'lecturer')->first();
        $studentRole = Role::where('name', 'student')->first();
        $employerRole = Role::where('name', 'employer')->first();
        $viewerRole = Role::where('name', 'viewer')->first();

        // Admin gets access to all menus
        $allMenus = Menu::all();
        foreach ($allMenus as $menu) {
            RoleMenu::create([
                'role_id' => $adminRole->id,
                'menu_id' => $menu->id,
            ]);
        }

        // Manager gets access to most menus
        $managerMenus = Menu::whereNotIn('path', ['/admin/settings', '/system/logs'])->get();
        foreach ($managerMenus as $menu) {
            RoleMenu::create([
                'role_id' => $managerRole->id,
                'menu_id' => $menu->id,
            ]);
        }

        // Lecturer gets access to specific menus
        $lecturerMenus = Menu::whereIn('path', ['/dashboard', '/courses', '/assignments', '/grading', '/students', '/profile'])->get();
        foreach ($lecturerMenus as $menu) {
            RoleMenu::create([
                'role_id' => $lecturerRole->id,
                'menu_id' => $menu->id,
            ]);
        }

        // Student gets access to basic menus
        $studentMenus = Menu::whereIn('path', ['/dashboard', '/courses', '/assignments', '/jobs', '/cv', '/profile'])->get();
        foreach ($studentMenus as $menu) {
            RoleMenu::create([
                'role_id' => $studentRole->id,
                'menu_id' => $menu->id,
            ]);
        }

        // Employer gets access to employer-related menus
        $employerMenus = Menu::whereIn('path', ['/dashboard', '/job-posts', '/applicants', '/profile'])->get();
        foreach ($employerMenus as $menu) {
            RoleMenu::create([
                'role_id' => $employerRole->id,
                'menu_id' => $menu->id,
            ]);
        }

        // Viewer gets access to very limited menus
        $viewerMenus = Menu::whereIn('path', ['/dashboard', '/profile', '/reports/view'])->get();
        foreach ($viewerMenus as $menu) {
            RoleMenu::create([
                'role_id' => $viewerRole->id,
                'menu_id' => $menu->id,
            ]);
        }
    }
}
