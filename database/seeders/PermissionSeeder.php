<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'view_users',
            'edit_users',
            'view_roles',
            'edit_roles',
            'edit_projects',
            'view_projects',
            'edit_artifacts',
            'view_artifacts',
            'edit_modules',
            'view_modules',
            'view_audit'
        ];
        $roles = [
            'admin',
            'pm',
            'engineer',
            'viewer'
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }
        foreach ($roles as $role) {
            Role::create(['name' => $role]);
        }

        $adminRole = Role::findByName('admin');
        $adminRole->syncPermissions($permissions);

        $pmRole = Role::findByName('pm');
        $pmRole->syncPermissions([
            'edit_projects',
            'view_projects',
            'edit_artifacts',
            'view_artifacts',
            'edit_modules',
            'view_modules',
            'view_audit'
        ]);
        $engineerRole = Role::findByName('engineer');
        $engineerRole->syncPermissions([
            'view_projects',
            'view_artifacts',
            'view_modules',
            'edit_modules',
        ]);
        $viewerRole = Role::findByName('viewer');
        $viewerRole->syncPermissions([
            'view_projects',
            'view_artifacts',
            'view_modules',
        ]);

        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('admin')
        ]);
        $admin->assignRole($adminRole);
    }
}
