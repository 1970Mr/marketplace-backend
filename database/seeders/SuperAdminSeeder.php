<?php

namespace Database\Seeders;

use App\Enums\Acl\RoleType;
use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superAdmin = Admin::query()->updateOrCreate(
            ['email' => config('app.super_admin.email')],
            [
                'name' => config('app.super_admin.name'),
                'password' => Hash::make(config('app.super_admin.password')),
            ]
        );

        $superAdmin->assignRole(RoleType::SUPER_ADMIN->value);
    }
}
