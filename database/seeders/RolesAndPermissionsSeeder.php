<?php

namespace Database\Seeders;

use App\Enums\Acl\PermissionType;
use App\Enums\Acl\RoleType;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (PermissionType::cases() as $permission) {
            Permission::firstOrCreate([
                'name' => $permission->value,
                'guard_name' => 'admin-api',
            ]);
        }

        Role::firstOrCreate([
            'name' => RoleType::SUPER_ADMIN->value,
            'guard_name' => 'admin-api',
        ])->givePermissionTo(PermissionType::values());

        Role::firstOrCreate([
            'name' => RoleType::ADMIN->value,
            'guard_name' => 'admin-api',
        ]);
    }
}
