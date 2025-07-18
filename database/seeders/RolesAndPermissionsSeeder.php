<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // User permissions
            'view users',
            'create users',
            'edit users',
            'delete users',
            'view available jobs',
            'view applied history',
            'create profile',
            'view applied resume job posts',

            // Role permissions
            'view roles',
            'create roles',
            'edit roles',
            'delete roles',

            // Permission permissions
            'view permissions',
            'create permissions',
            'edit permissions',
            'delete permissions',

            // Dashboard permission
            'access dashboard',

            // HR specific permissions
            'view job posts',
            'create job posts',
            'edit job posts',
            'delete job posts',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // // Create user role and assign permissions
        // $userRole = Role::create(['name' => 'user']);
        // $userRole->givePermissionTo([
        //     'access dashboard',
        // ]);
        $jobseekerRole = Role::create(['name' => 'job_seeker']);
        $jobseekerRole->givePermissionTo([
            'access dashboard',
            'view available jobs',
            'view applied history',
            'create profile',
            'view applied resume job posts',
        ]);

        // Create admin role and assign permissions
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        $hrRole = Role::create(['name' => 'hr']);
        $hrRole->givePermissionTo([
            'access dashboard',
            'view job posts',
            'create job posts',
            'edit job posts',
            'delete job posts',
        ]);

    }
}
