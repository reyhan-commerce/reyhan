<?php

declare(strict_types=1);

namespace Reyhan\Core\Database\Seeders;

use Reyhan\Core\Models\Attribute;
use Reyhan\Core\Models\AttributeValue;
use Reyhan\Core\Models\Brand;
use Reyhan\Core\Models\Category;
use Reyhan\Core\Models\Product;
use Reyhan\Core\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DigitalPresetSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Brands
        $brands = [
            'apple' => Brand::create(['name' => 'اپل', 'slug' => 'apple', 'is_active' => true]),
            'samsung' => Brand::create(['name' => 'سامسونگ', 'slug' => 'samsung', 'is_active' => true]),
            'xiaomi' => Brand::create(['name' => 'شیائومی', 'slug' => 'xiaomi', 'is_active' => true]),
            'sony' => Brand::create(['name' => 'سونی', 'slug' => 'sony', 'is_active' => true]),
        ];

        // 2. Create Categories
        $phones = Category::create(['name' => 'کالای دیجیتال و موبایل', 'slug' => 'digital-phones', 'is_active' => true, 'order' => 1]);
        $smartphones = Category::create(['name' => 'گوشی هوشمند', 'slug' => 'smartphones', 'parent_id' => $phones->id, 'is_active' => true, 'order' => 1]);
        $accessories = Category::create(['name' => 'لوازم جانبی و صوتی', 'slug' => 'digital-accessories', 'parent_id' => $phones->id, 'is_active' => true, 'order' => 2]);

        // 3. Create Attributes & Values
        $storageAttr = Attribute::create(['name' => 'حافظه داخلی', 'type' => 'select']);
        $storages = [
            '128GB' => AttributeValue::create(['attribute_id' => $storageAttr->id, 'value' => '128GB', 'label' => '۱۲۸ گیگابایت']),
            '256GB' => AttributeValue::create(['attribute_id' => $storageAttr->id, 'value' => '256GB', 'label' => '۲۵۶ گیگابایت']),
            '512GB' => AttributeValue::create(['attribute_id' => $storageAttr->id, 'value' => '512GB', 'label' => '۵۱۲ گیگابایت']),
        ];

        $colorAttr = Attribute::create(['name' => 'رنگ', 'type' => 'color']);
        $colors = [
            'black' => AttributeValue::create(['attribute_id' => $colorAttr->id, 'value' => '#1C1C1E', 'label' => 'مشکی فضایی']),
            'titanium' => AttributeValue::create(['attribute_id' => $colorAttr->id, 'value' => '#8E8E93', 'label' => 'تیتانیوم طبیعی']),
            'blue' => AttributeValue::create(['attribute_id' => $colorAttr->id, 'value' => '#0A84FF', 'label' => 'آبی اقیانوسی']),
        ];

        $warrantyAttr = Attribute::create(['name' => 'گارانتی', 'type' => 'select']);
        $warranties = [
            '18m' => AttributeValue::create(['attribute_id' => $warrantyAttr->id, 'value' => '18m-official', 'label' => '۱۸ ماه گارانتی شرکتی']),
        ];

        // Link category attributes
        $smartphones->attributes()->attach([
            $storageAttr->id => ['is_variant_maker' => true, 'order' => 1],
            $colorAttr->id => ['is_variant_maker' => true, 'order' => 2],
            $warrantyAttr->id => ['is_variant_maker' => false, 'order' => 3],
        ]);

        // 4. Create Sample Tech Products
        $p1 = Product::create([
            'category_id' => $smartphones->id,
            'brand_id' => $brands['samsung']->id,
            'name' => 'گوشی موبایل گلکسی S24 اولترا',
            'slug' => 'samsung-galaxy-s24-ultra',
            'description' => 'پرچمدار سامسونگ با دوربین ۲۰۰ مگاپیکسلی، هوش مصنوعی Galaxy AI و پردازنده اسنپدراگون.',
            'short_description' => 'گوشی پرچمدار با حافظه ۵۱۲ گیگ و دوربین ۲۰۰ مگاپیکسلی',
            'is_active' => true,
            'is_featured' => true,
            'published_at' => now(),
        ]);

        foreach (['256GB', '512GB'] as $st) {
            foreach (['black', 'titanium'] as $c) {
                $extraPrice = $st === '512GB' ? 80000000 : 0;
                $sku = 'DIG-S24U-'.Str::upper($st).'-'.Str::upper($c);
                $variant = ProductVariant::create([
                    'product_id' => $p1->id,
                    'sku' => $sku,
                    'price' => 640000000 + $extraPrice,
                    'stock' => 8,
                    'is_active' => true,
                ]);
                $variant->attributeValues()->attach([$storages[$st]->id, $colors[$c]->id]);
            }
        }

        $p2 = Product::create([
            'category_id' => $accessories->id,
            'brand_id' => $brands['sony']->id,
            'name' => 'هدفون بی‌سیم نویز کنسلینگ سونی WH-1000XM5',
            'slug' => 'sony-wh-1000xm5-headphones',
            'description' => 'بهترین هدفون حذف نویز اکتیو دنیا با شارژدهی ۳۰ ساعته و کیفیت صدای بی‌نظیر های-رزولوشن.',
            'short_description' => 'هدفون بی‌سیم پرچمدار سونی با شارژدهی ۳۰ ساعته و نویز کنسلینگ پیشرفته',
            'is_active' => true,
            'is_featured' => true,
            'published_at' => now(),
        ]);

        foreach (['black', 'blue'] as $c) {
            $sku = 'DIG-SONY-XM5-'.Str::upper($c);
            $variant = ProductVariant::create([
                'product_id' => $p2->id,
                'sku' => $sku,
                'price' => 175000000,
                'stock' => 12,
                'is_active' => true,
            ]);
            $variant->attributeValues()->attach([$colors[$c]->id]);
        }
    }
}
