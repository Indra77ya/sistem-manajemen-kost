<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create default roles
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $owner = Role::firstOrCreate(['name' => 'owner']);
        $admin = Role::firstOrCreate(['name' => 'admin_cabang']);
        $technician = Role::firstOrCreate(['name' => 'technician']);
        $tenant = Role::firstOrCreate(['name' => 'tenant']);

        // 2. Assign roles to existing users based on their 'role' column
        User::all()->each(function ($user) {
            switch ($user->role) {
                case User::ROLE_DEVELOPER:
                    $user->assignRole('super_admin');
                    break;
                case User::ROLE_OWNER:
                    $user->assignRole('owner');
                    break;
                case User::ROLE_ADMIN:
                    $user->assignRole('admin_cabang');
                    break;
                case User::ROLE_TECHNICIAN:
                    $user->assignRole('technician');
                    break;
                case User::ROLE_TENANT:
                    $user->assignRole('tenant');
                    break;
            }
        });

        // 3. Auto-assign all generated permissions to super_admin and owner
        $allPermissions = Permission::all();
        $superAdmin->syncPermissions($allPermissions);
        $owner->syncPermissions($allPermissions);
    }
}
