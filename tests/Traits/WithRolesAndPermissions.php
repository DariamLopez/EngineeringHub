<?php

namespace Tests\Traits;

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

trait WithRolesAndPermissions
{
    protected function seedRolesAndPermissions(): void
    {
        $permissions = [
            'view_users', 'edit_users', 'view_roles', 'edit_roles',
            'edit_projects', 'view_projects', 'edit_artifacts',
            'view_artifacts', 'edit_modules', 'view_modules', 'view_audit'
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions($permissions);

        $pm = Role::firstOrCreate(['name' => 'pm', 'guard_name' => 'web']);
        $pm->syncPermissions([
            'edit_projects', 'view_projects', 'edit_artifacts',
            'view_artifacts', 'edit_modules', 'view_modules', 'view_audit'
        ]);

        $engineer = Role::firstOrCreate(['name' => 'engineer', 'guard_name' => 'web']);
        $engineer->syncPermissions(['view_projects', 'view_artifacts', 'view_modules', 'edit_modules']);

        $viewer = Role::firstOrCreate(['name' => 'viewer', 'guard_name' => 'web']);
        $viewer->syncPermissions(['view_projects', 'view_artifacts', 'view_modules']);
    }

    protected function createAdmin(): User
    {
        $this->seedRolesAndPermissions();
        $user = User::factory()->create();
        $user->assignRole('admin');
        return $user;
    }

    protected function createPm(): User
    {
        $this->seedRolesAndPermissions();
        $user = User::factory()->create();
        $user->assignRole('pm');
        return $user;
    }

    protected function createEngineer(): User
    {
        $this->seedRolesAndPermissions();
        $user = User::factory()->create();
        $user->assignRole('engineer');
        return $user;
    }

    protected function createViewer(): User
    {
        $this->seedRolesAndPermissions();
        $user = User::factory()->create();
        $user->assignRole('viewer');
        return $user;
    }
}
