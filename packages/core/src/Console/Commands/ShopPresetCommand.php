<?php

declare(strict_types=1);

namespace Reyhan\Core\Console\Commands;

use Reyhan\Core\Models\Admin;
use Reyhan\Core\Models\Category;
use Reyhan\Core\Models\Product;
use Reyhan\Core\Models\ProductVariant;
use Reyhan\Core\Settings\GeneralSettings;
use Reyhan\Core\Settings\ThemeSettings;
use Reyhan\Core\Database\Seeders\AdminRoleSeeder;
use Reyhan\Core\Database\Seeders\ApparelPresetSeeder;
use Reyhan\Core\Database\Seeders\BlogSeeder;
use Reyhan\Core\Database\Seeders\CatalogSeeder;
use Reyhan\Core\Database\Seeders\DigitalPresetSeeder;
use Reyhan\Core\Database\Seeders\FaqSeeder;
use Reyhan\Core\Database\Seeders\PageSeeder;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\note;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\select;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\table;
use function Laravel\Prompts\text;

#[Signature('shop:preset {vertical? : Target industry vertical (apparel|digital|cosmetics|blank)} {--admin-name= : Initial admin full name} {--admin-email= : Initial admin email address} {--admin-password= : Initial admin password}')]
#[Description('Interactive TUI starter kit and store scaffolder for e-commerce verticals')]
class ShopPresetCommand extends Command
{
    public function handle(GeneralSettings $generalSettings, ThemeSettings $themeSettings): int
    {
        intro('⚡ Reyhan — High-Performance E-Commerce Starter Kit & Store Scaffolder');

        $isInteractive = $this->input->isInteractive();

        // 1. Interactive vertical selection if not provided
        $vertical = $this->argument('vertical');
        if (! $vertical) {
            $vertical = select(
                label: 'Select your target store vertical and catalog type:',
                options: [
                    'apparel' => '👔 Fashion & Apparel (Sizes, Colors, Fabrics, Zara & Mango brands)',
                    'digital' => '📱 Digital & Electronics (Phones, Laptops, Storage, RAM, Warranty)',
                    'cosmetics' => '💄 Beauty & Cosmetics (Skincare routines, Shades, Volume, Authentic barcodes)',
                    'blank' => '🧹 Clean & Blank Slate (Zero demo catalog; ready for production entries)',
                ],
                default: 'apparel'
            );
        }

        $vertical = strtolower((string) $vertical);

        // Map defaults based on vertical
        $defaultConfig = match ($vertical) {
            'apparel' => [
                'name' => 'Aura Fashion & Apparel',
                'slogan' => 'Trendy collections & high quality garments',
                'color' => '#0f172a',
                'radius' => '0.125rem',
            ],
            'digital' => [
                'name' => 'TechnoLand Store',
                'slogan' => 'Premium smartphones, laptops and tech accessories',
                'color' => '#0284c7',
                'radius' => '0.25rem',
            ],
            'cosmetics' => [
                'name' => 'Glow Beauty Store',
                'slogan' => 'Original cosmetics, skincare and wellness essentials',
                'color' => '#e11d48',
                'radius' => '0.375rem',
            ],
            default => [
                'name' => 'My Online Shop',
                'slogan' => 'Premium products delivered to your door',
                'color' => '#3b82f6',
                'radius' => '0.25rem',
            ],
        };

        // 2. Interactive custom inputs (or fallback to defaults if non-interactive)
        $storeName = (string) ($isInteractive ? text(
            label: 'Store name:',
            default: $defaultConfig['name'],
            required: true
        ) : $defaultConfig['name']);

        $storeSlogan = (string) ($isInteractive ? text(
            label: 'Store slogan / tagline:',
            default: $defaultConfig['slogan']
        ) : $defaultConfig['slogan']);

        $colorPreset = (string) ($isInteractive ? select(
            label: 'Primary brand theme color:',
            options: [
                '#0f172a' => '🖤 Monochrome & Slate (Luxury, Fashion, Modern) [#0f172a]',
                '#0284c7' => '💙 Ocean Blue & Tech (Digital, Gadgets, Clean) [#0284c7]',
                '#e11d48' => '💖 Rose & Crimson (Cosmetics, Beauty, Lifestyle) [#e11d48]',
                '#059669' => '💚 Emerald & Organic (Natural goods, Health, Bio) [#059669]',
                '#d97706' => '☕ Amber & Roast (Coffee, Bakery, Gourmet) [#d97706]',
                '#7c3aed' => '💜 Violet & Modern (Creative, Toys, Fantasy) [#7c3aed]',
            ],
            default: $defaultConfig['color']
        ) : $defaultConfig['color']);

        $borderRadius = (string) ($isInteractive ? select(
            label: 'Geometry & Border Radius style:',
            options: [
                '0px' => '📐 Sharp & Flat (0px - Brutalist)',
                '0.125rem' => '🔲 Subtle & Minimal (2px - Sleek)',
                '0.25rem' => '🔲 Standard Nuxt UI (4px - Default Clean)',
                '0.375rem' => '🔘 Smooth & Soft (6px - Friendly)',
                '0.5rem' => '🫧 Rounded & Modern (8px - Warm & Organic)',
                '0.75rem' => '🎯 High Rounded (12px - Expressive)',
            ],
            default: $defaultConfig['radius']
        ) : $defaultConfig['radius']);

        // 3. Initial Super Admin credentials (with friendly defaults)
        $defaultAdminName = (string) ($this->option('admin-name') ?: 'Super Admin');
        $defaultAdminEmail = (string) ($this->option('admin-email') ?: 'admin@reyhan.local');
        $defaultAdminPassword = (string) ($this->option('admin-password') ?: 'password');

        $adminName = (string) ($isInteractive ? text(
            label: 'Initial Super Admin full name:',
            default: $defaultAdminName,
            required: true
        ) : $defaultAdminName);

        $adminEmail = (string) ($isInteractive ? text(
            label: 'Initial Super Admin email (for Filament panel login):',
            default: $defaultAdminEmail,
            required: true
        ) : $defaultAdminEmail);

        $adminPassword = (string) ($isInteractive ? text(
            label: 'Initial Super Admin password:',
            default: $defaultAdminPassword,
            required: true
        ) : $defaultAdminPassword);

        // Confirmation guard
        if ($isInteractive && ! confirm('Are you sure you want to reset the catalog and apply this preset? (Existing users & settings will be preserved)', default: true)) {
            note('Preset scaffolding cancelled by user.');

            return self::SUCCESS;
        }

        // 4. Animated Execution with Spinner
        spin(function () use ($vertical, $storeName, $storeSlogan, $colorPreset, $borderRadius, $adminName, $adminEmail, $adminPassword, $generalSettings, $themeSettings) {
            // Clean catalog records
            DB::statement('TRUNCATE TABLE product_variant_values, product_variants, category_attributes, products, attribute_values, attributes, categories, brands, reviews CASCADE');

            // Seed appropriate vertical
            match ($vertical) {
                'apparel' => (new ApparelPresetSeeder)->run(),
                'digital' => (new DigitalPresetSeeder)->run(),
                'cosmetics' => (new CatalogSeeder)->run(),
                'blank' => null,
                default => null,
            };

            // Save Settings
            $generalSettings->store_name = $storeName;
            $generalSettings->store_slogan = $storeSlogan;
            $generalSettings->save();

            $themeSettings->primary_color = $colorPreset;
            $themeSettings->border_radius = $borderRadius;
            $themeSettings->save();

            // Ensure roles and content exist
            $this->callSilent(AdminRoleSeeder::class);
            $this->callSilent(PageSeeder::class);
            $this->callSilent(FaqSeeder::class);
            $this->callSilent(BlogSeeder::class);

            // Create or update Super Admin
            $admin = Admin::updateOrCreate(
                ['email' => $adminEmail],
                [
                    'name' => $adminName,
                    'password' => Hash::make($adminPassword),
                    'is_active' => true,
                ]
            );
            $admin->syncRoles(['super_admin', 'SuperAdmin']);

            Cache::flush();
        }, 'Scaffolding catalog, seeding taxonomies, configuring theme tokens, and provisioning super admin...');

        // 5. Output Summary Table
        note('Store Configuration Summary:');
        table(
            headers: ['Configuration Parameter', 'Value'],
            rows: [
                ['Store Vertical / Industry', $vertical],
                ['Store Name', $generalSettings->store_name],
                ['Store Tagline', $generalSettings->store_slogan ?? '-'],
                ['Primary Theme Color', $themeSettings->primary_color],
                ['Border Radius Style', $themeSettings->border_radius],
                ['Super Admin Email', $adminEmail],
                ['Super Admin Password', $adminPassword],
                ['Total Categories Count', (string) Category::count()],
                ['Active Products Count', (string) Product::count()],
                ['Orderable SKUs (Variants)', (string) ProductVariant::count()],
            ]
        );

        outro('🎉 Congratulations! Your store preset has been successfully configured. You can now login to the admin panel at /admin.');

        return self::SUCCESS;
    }
}
