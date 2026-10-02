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

class ApparelPresetSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Brands
        $brands = [
            'zara' => Brand::create(['name' => 'زارا', 'slug' => 'zara', 'is_active' => true]),
            'mango' => Brand::create(['name' => 'مانگو', 'slug' => 'mango', 'is_active' => true]),
            'lc-waikiki' => Brand::create(['name' => 'ال سی وایکیکی', 'slug' => 'lc-waikiki', 'is_active' => true]),
            'h-m' => Brand::create(['name' => 'اچ اند ام', 'slug' => 'h-m', 'is_active' => true]),
        ];

        // 2. Create Categories
        $men = Category::create(['name' => 'پوشاک مردانه', 'slug' => 'men-apparel', 'is_active' => true, 'order' => 1]);
        $menShirts = Category::create(['name' => 'پیراهن و تیشرت مردانه', 'slug' => 'men-shirts', 'parent_id' => $men->id, 'is_active' => true, 'order' => 1]);
        $menPants = Category::create(['name' => 'شلوار مردانه', 'slug' => 'men-pants', 'parent_id' => $men->id, 'is_active' => true, 'order' => 2]);

        $women = Category::create(['name' => 'پوشاک زنانه', 'slug' => 'women-apparel', 'is_active' => true, 'order' => 2]);
        $womenDresses = Category::create(['name' => 'مانتو و لباس زنانه', 'slug' => 'women-dresses', 'parent_id' => $women->id, 'is_active' => true, 'order' => 1]);
        $womenPants = Category::create(['name' => 'شلوار زنانه', 'slug' => 'women-pants', 'parent_id' => $women->id, 'is_active' => true, 'order' => 2]);

        // 3. Create Attributes & Values
        $sizeAttr = Attribute::create(['name' => 'سایز', 'type' => 'select']);
        $sizes = [
            'S' => AttributeValue::create(['attribute_id' => $sizeAttr->id, 'value' => 'Small', 'label' => 'S']),
            'M' => AttributeValue::create(['attribute_id' => $sizeAttr->id, 'value' => 'Medium', 'label' => 'M']),
            'L' => AttributeValue::create(['attribute_id' => $sizeAttr->id, 'value' => 'Large', 'label' => 'L']),
            'XL' => AttributeValue::create(['attribute_id' => $sizeAttr->id, 'value' => 'X-Large', 'label' => 'XL']),
        ];

        $colorAttr = Attribute::create(['name' => 'رنگ', 'type' => 'color']);
        $colors = [
            'black' => AttributeValue::create(['attribute_id' => $colorAttr->id, 'value' => '#000000', 'label' => 'مشکی']),
            'white' => AttributeValue::create(['attribute_id' => $colorAttr->id, 'value' => '#FFFFFF', 'label' => 'سفید']),
            'navy' => AttributeValue::create(['attribute_id' => $colorAttr->id, 'value' => '#000080', 'label' => 'سرمه‌ای']),
        ];

        $fabricAttr = Attribute::create(['name' => 'جنس پارچه', 'type' => 'select']);
        $fabrics = [
            'cotton' => AttributeValue::create(['attribute_id' => $fabricAttr->id, 'value' => 'cotton', 'label' => 'نخی ۱۰۰٪']),
            'linen' => AttributeValue::create(['attribute_id' => $fabricAttr->id, 'value' => 'linen', 'label' => 'کتان درجه یک']),
        ];

        // Link categories with attributes
        $menShirts->attributes()->attach([
            $sizeAttr->id => ['is_variant_maker' => true, 'order' => 1],
            $colorAttr->id => ['is_variant_maker' => true, 'order' => 2],
            $fabricAttr->id => ['is_variant_maker' => false, 'order' => 3],
        ]);

        // 4. Create Sample Products
        $p1 = Product::create([
            'category_id' => $menShirts->id,
            'brand_id' => $brands['zara']->id,
            'name' => 'تیشرت نخی آستین کوتاه اسلیم فیت',
            'slug' => 'slim-fit-cotton-tshirt',
            'description' => 'تیشرت باکیفیت پنبه‌ای سوپر شانه شده مناسب فصل گرما با دوام شستشوی بالا.',
            'short_description' => 'تیشرت نخی باکیفیت پنبه‌ای مناسب مصرف روزمره',
            'is_active' => true,
            'is_featured' => true,
            'published_at' => now(),
        ]);

        foreach (['M', 'L'] as $s) {
            foreach (['black', 'navy'] as $c) {
                $sku = 'APP-TSHIRT-'.Str::upper($s).'-'.Str::upper($c);
                $variant = ProductVariant::create([
                    'product_id' => $p1->id,
                    'sku' => $sku,
                    'price' => 4500000,
                    'stock' => 15,
                    'is_active' => true,
                ]);
                $variant->attributeValues()->attach([$sizes[$s]->id, $colors[$c]->id]);
            }
        }

        $p2 = Product::create([
            'category_id' => $menPants->id,
            'brand_id' => $brands['mango']->id,
            'name' => 'شلوار کتان کش کلاسیک مردانه',
            'slug' => 'classic-stretch-chino-pants',
            'description' => 'شلوار کتان راحت با ایستایی شیک برای استایل روزمره و اداری.',
            'short_description' => 'شلوار کتان کلاسیک مردانه مناسب استفاده رسمی و کژوال',
            'is_active' => true,
            'is_featured' => true,
            'published_at' => now(),
        ]);

        foreach (['M', 'L', 'XL'] as $s) {
            $sku = 'APP-PANTS-'.Str::upper($s).'-BLK';
            $variant = ProductVariant::create([
                'product_id' => $p2->id,
                'sku' => $sku,
                'price' => 9800000,
                'stock' => 10,
                'is_active' => true,
            ]);
            $variant->attributeValues()->attach([$sizes[$s]->id, $colors['black']->id]);
        }
    }
}
