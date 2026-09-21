<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            'super_admin',
            'SuperAdmin',
            'ShopManager',
            'InventorySpecialist',
            'CustomerSupport',
        ];

        foreach ($roles as $roleName) {
            Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'admin',
            ]);
        }

        // Provision default SuperAdmin
        $admin = Admin::firstOrCreate(
            ['email' => 'admin@easyshop.local'],
            [
                'name' => 'مدیر ارشد سامانه',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );

        $admin->syncRoles(['super_admin', 'SuperAdmin']);

    }
}
