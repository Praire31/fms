<?php

// database/seeders/RolePermissionSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $perms = [
            // users
            'users.view','users.create','users.update','users.delete',
            // departments
            'departments.view','departments.create','departments.update','departments.delete',
            // attendance
            'attendance.view','attendance.create','attendance.update','attendance.delete',
            // audits
            'audits.view',
        ];

        foreach ($perms as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        $super = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $admin = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $user  = Role::firstOrCreate(['name' => 'User', 'guard_name' => 'web']);

        // Super Admin gets everything
        $super->syncPermissions(Permission::all());

        // Admin gets almost everything
        $admin->syncPermissions([
            'users.view','users.create','users.update','users.delete',
            'departments.view','departments.create','departments.update','departments.delete',
            'attendance.view','attendance.update','attendance.delete',
            'audits.view',
        ]);

        // User gets only what they need
        $user->syncPermissions([
            'attendance.view','attendance.create',
        ]);
    }
}

