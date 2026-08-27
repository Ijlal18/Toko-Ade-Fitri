<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'view-dashboard',

            'view-products',
            'create-products',
            'edit-products',
            'delete-products',

            'view-categories',
            'create-categories',
            'edit-categories',
            'delete-categories',

            'view-suppliers',
            'create-suppliers',
            'edit-suppliers',
            'delete-suppliers',

            'view-supplies',
            'create-supplies',

            'view-customers',
            'create-customers',
            'edit-customers',
            'delete-customers',

            'create-sales',
            'view-transactions',
            'delete-transactions',
            'print-invoices',

            'view-reports',

            'view-finance',
            'manage-finance',

            'manage-users',
            'manage-settings',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $owner = Role::firstOrCreate([
            'name' => 'owner',
            'guard_name' => 'web',
        ]);

        $admin = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        $karyawan = Role::firstOrCreate([
            'name' => 'karyawan',
            'guard_name' => 'web',
        ]);

        $owner->syncPermissions(
            Permission::all()
        );

        $admin->syncPermissions([
            'view-dashboard',

            'view-products',
            'create-products',
            'edit-products',
            'delete-products',

            'view-categories',
            'create-categories',
            'edit-categories',
            'delete-categories',

            'view-suppliers',
            'create-suppliers',
            'edit-suppliers',
            'delete-suppliers',

            'view-supplies',
            'create-supplies',

            'view-customers',
            'create-customers',
            'edit-customers',
            'delete-customers',

            'create-sales',
            'view-transactions',
            'delete-transactions',
            'print-invoices',

            'view-reports',

            'view-finance',
            'manage-finance',

            'manage-settings',
        ]);

        $karyawan->syncPermissions([
            'view-dashboard',

            'view-products',

            'view-categories',

            'view-customers',

            'create-sales',
            'view-transactions',
            'print-invoices',
        ]);
    }
}
