<?php

namespace Reyhan\Core\Database\Seeders;

use Reyhan\Core\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'first_name' => 'کاربر',
            'last_name' => 'آزمایشی',
            'mobile' => '09123456789',
            'national_code' => '0012345678',
            'email' => 'test@reyhan.test',
        ]);

        $this->call([
            AdminRoleSeeder::class,
            CatalogSeeder::class,
            IranGeoSeeder::class,
            ShippingMethodSeeder::class,
            PageSeeder::class,
            FaqSeeder::class,
            BlogSeeder::class,
        ]);
    }
}
