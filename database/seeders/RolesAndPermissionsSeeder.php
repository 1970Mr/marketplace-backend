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
            Permission::query()->firstOrCreate(['name' => $permission->value]);
        }

        Role::query()->firstOrCreate(['name' => RoleType::SUPER_ADMIN->value])
            ->givePermissionTo(PermissionType::values());

        Role::query()->firstOrCreate(['name' => RoleType::ADMIN->value])
            ->givePermissionTo(PermissionType::values());

        Role::query()->firstOrCreate(['name' => RoleType::NORMAL->value]);
    }
}
