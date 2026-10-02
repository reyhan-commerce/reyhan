<?php

declare(strict_types=1);

namespace Reyhan\Core\Database\Seeders;

use Reyhan\Core\Enums\AttributeType;
use Reyhan\Core\Models\Attribute;
use Reyhan\Core\Models\AttributeValue;
use Reyhan\Core\Models\Brand;
use Reyhan\Core\Models\Category;
use Reyhan\Core\Models\Product;
use Reyhan\Core\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            // 1. Root & Child Categories
            $skincare = Category::firstOrCreate(
                ['slug' => 'skincare'],
                ['name' => 'مراقبت پوست', 'order' => 1, 'is_active' => true]
            );
            $sunscreen = Category::firstOrCreate(
                ['slug' => 'sunscreen'],
                ['parent_id' => $skincare->id, 'name' => 'کرم ضدآفتاب', 'order' => 1, 'is_active' => true]
            );
            $moisturizer = Category::firstOrCreate(
                ['slug' => 'moisturizer'],
                ['parent_id' => $skincare->id, 'name' => 'مرطوب‌کننده', 'order' => 2, 'is_active' => true]
            );
            $serum = Category::firstOrCreate(
                ['slug' => 'serum'],
                ['parent_id' => $skincare->id, 'name' => 'سرم و اسنس', 'order' => 3, 'is_active' => true]
            );
            Category::firstOrCreate(
                ['slug' => 'cleanser'],
                ['parent_id' => $skincare->id, 'name' => 'پاک‌کننده', 'order' => 4, 'is_active' => true]
            );

            $makeup = Category::firstOrCreate(
                ['slug' => 'makeup'],
                ['name' => 'آرایشی', 'order' => 2, 'is_active' => true]
            );
            $lipstick = Category::firstOrCreate(
                ['slug' => 'lipstick'],
                ['parent_id' => $makeup->id, 'name' => 'رژلب', 'order' => 1, 'is_active' => true]
            );
            $mascara = Category::firstOrCreate(
                ['slug' => 'mascara'],
                ['parent_id' => $makeup->id, 'name' => 'ریمل', 'order' => 2, 'is_active' => true]
            );
            $foundation = Category::firstOrCreate(
                ['slug' => 'foundation'],
                ['parent_id' => $makeup->id, 'name' => 'کرم‌پودر', 'order' => 3, 'is_active' => true]
            );
            $eyeshadow = Category::firstOrCreate(
                ['slug' => 'eyeshadow'],
                ['parent_id' => $makeup->id, 'name' => 'سایه چشم', 'order' => 4, 'is_active' => true]
            );

            $haircare = Category::firstOrCreate(
                ['slug' => 'haircare'],
                ['name' => 'مراقبت مو', 'order' => 3, 'is_active' => true]
            );
            $shampoo = Category::firstOrCreate(
                ['slug' => 'shampoo'],
                ['parent_id' => $haircare->id, 'name' => 'شامپو', 'order' => 1, 'is_active' => true]
            );
            Category::firstOrCreate(
                ['slug' => 'hair-mask'],
                ['parent_id' => $haircare->id, 'name' => 'ماسک مو', 'order' => 2, 'is_active' => true]
            );
            Category::firstOrCreate(
                ['slug' => 'hair-oil'],
                ['parent_id' => $haircare->id, 'name' => 'روغن مو', 'order' => 3, 'is_active' => true]
            );

            $fragrance = Category::firstOrCreate(
                ['slug' => 'fragrance'],
                ['name' => 'عطر و ادکلن', 'order' => 4, 'is_active' => true]
            );
            Category::firstOrCreate(
                ['slug' => 'women-perfume'],
                ['parent_id' => $fragrance->id, 'name' => 'عطر زنانه', 'order' => 1, 'is_active' => true]
            );
            Category::firstOrCreate(
                ['slug' => 'men-perfume'],
                ['parent_id' => $fragrance->id, 'name' => 'عطر مردانه', 'order' => 2, 'is_active' => true]
            );

            // 2. Attributes & Values
            $colorAttr = Attribute::firstOrCreate(
                ['slug' => 'color'],
                ['name' => 'رنگ', 'type' => AttributeType::Color, 'order' => 1]
            );
            $valRed = AttributeValue::firstOrCreate(
                ['attribute_id' => $colorAttr->id, 'value' => 'قرمز'],
                ['label' => 'قرمز آتشین', 'hex_code' => '#FF0000', 'order' => 1]
            );
            $valPink = AttributeValue::firstOrCreate(
                ['attribute_id' => $colorAttr->id, 'value' => 'صورتی'],
                ['label' => 'صورتی ملایم', 'hex_code' => '#FF69B4', 'order' => 2]
            );
            $valNude = AttributeValue::firstOrCreate(
                ['attribute_id' => $colorAttr->id, 'value' => 'نود'],
                ['label' => 'کالباسی نود', 'hex_code' => '#DEB887', 'order' => 3]
            );
            $valBeige = AttributeValue::firstOrCreate(
                ['attribute_id' => $colorAttr->id, 'value' => 'بژ'],
                ['label' => 'بژ طبیعی', 'hex_code' => '#F5DEB3', 'order' => 4]
            );

            $volumeAttr = Attribute::firstOrCreate(
                ['slug' => 'volume'],
                ['name' => 'حجم', 'type' => AttributeType::Number, 'order' => 2]
            );
            $val30ml = AttributeValue::firstOrCreate(
                ['attribute_id' => $volumeAttr->id, 'value' => '30ml'],
                ['label' => '۳۰ میلی‌لیتر', 'order' => 1]
            );
            $val50ml = AttributeValue::firstOrCreate(
                ['attribute_id' => $volumeAttr->id, 'value' => '50ml'],
                ['label' => '۵۰ میلی‌لیتر', 'order' => 2]
            );
            $val100ml = AttributeValue::firstOrCreate(
                ['attribute_id' => $volumeAttr->id, 'value' => '100ml'],
                ['label' => '۱۰۰ میلی‌لیتر', 'order' => 3]
            );
            $val200ml = AttributeValue::firstOrCreate(
                ['attribute_id' => $volumeAttr->id, 'value' => '200ml'],
                ['label' => '۲۰۰ میلی‌لیتر', 'order' => 4]
            );

            $spfAttr = Attribute::firstOrCreate(
                ['slug' => 'spf'],
                ['name' => 'SPF', 'type' => AttributeType::Number, 'order' => 3]
            );
            $valSpf30 = AttributeValue::firstOrCreate(
                ['attribute_id' => $spfAttr->id, 'value' => 'SPF30'],
                ['label' => 'SPF 30', 'order' => 1]
            );
            $valSpf50 = AttributeValue::firstOrCreate(
                ['attribute_id' => $spfAttr->id, 'value' => 'SPF50'],
                ['label' => 'SPF 50', 'order' => 2]
            );

            $coverageAttr = Attribute::firstOrCreate(
                ['slug' => 'coverage'],
                ['name' => 'پوشش', 'type' => AttributeType::Select, 'order' => 4]
            );
            $valCovMed = AttributeValue::firstOrCreate(
                ['attribute_id' => $coverageAttr->id, 'value' => 'متوسط'],
                ['label' => 'پوشش متوسط', 'order' => 1]
            );
            $valCovFull = AttributeValue::firstOrCreate(
                ['attribute_id' => $coverageAttr->id, 'value' => 'کامل'],
                ['label' => 'پوشش کامل و مات', 'order' => 2]
            );

            // 3. Category Attributes Pivot
            $sunscreen->attributes()->syncWithoutDetaching([
                $spfAttr->id => ['is_variant_maker' => true, 'is_filterable' => true, 'order' => 1],
                $volumeAttr->id => ['is_variant_maker' => true, 'is_filterable' => true, 'order' => 2],
            ]);
            $foundation->attributes()->syncWithoutDetaching([
                $colorAttr->id => ['is_variant_maker' => true, 'is_filterable' => true, 'order' => 1],
                $coverageAttr->id => ['is_variant_maker' => true, 'is_filterable' => true, 'order' => 2],
                $volumeAttr->id => ['is_variant_maker' => false, 'is_filterable' => true, 'order' => 3],
            ]);
            $lipstick->attributes()->syncWithoutDetaching([
                $colorAttr->id => ['is_variant_maker' => true, 'is_filterable' => true, 'order' => 1],
            ]);
            $moisturizer->attributes()->syncWithoutDetaching([
                $volumeAttr->id => ['is_variant_maker' => true, 'is_filterable' => true, 'order' => 1],
            ]);
            $serum->attributes()->syncWithoutDetaching([
                $volumeAttr->id => ['is_variant_maker' => true, 'is_filterable' => true, 'order' => 1],
            ]);
            $shampoo->attributes()->syncWithoutDetaching([
                $volumeAttr->id => ['is_variant_maker' => true, 'is_filterable' => true, 'order' => 1],
            ]);

            // 4. Brands
            $loreal = Brand::firstOrCreate(
                ['slug' => 'loreal'],
                ['name' => 'لورآل', 'name_en' => "L'Oréal", 'is_active' => true, 'order' => 1]
            );
            $nivea = Brand::firstOrCreate(
                ['slug' => 'nivea'],
                ['name' => 'نیوآ', 'name_en' => 'Nivea', 'is_active' => true, 'order' => 2]
            );
            $my = Brand::firstOrCreate(
                ['slug' => 'my'],
                ['name' => 'مای', 'name_en' => 'My', 'is_active' => true, 'order' => 3]
            );
            $cinere = Brand::firstOrCreate(
                ['slug' => 'cinere'],
                ['name' => 'سینره', 'name_en' => 'Cinere', 'is_active' => true, 'order' => 4]
            );
            $isadora = Brand::firstOrCreate(
                ['slug' => 'isadora'],
                ['name' => 'ایزادورا', 'name_en' => 'IsaDora', 'is_active' => true, 'order' => 5]
            );

            // 5. Products & Variants

            // Product 1: کرم ضدآفتاب نیوآ SPF50
            $p1 = Product::firstOrCreate(
                ['slug' => 'nivea-sunscreen-spf50'],
                [
                    'category_id' => $sunscreen->id,
                    'brand_id' => $nivea->id,
                    'name' => 'کرم ضدآفتاب نیوآ SPF50',
                    'short_description' => 'محافظت قدرتمند و ماندگار در برابر اشعه‌های UVA و UVB مناسب مصرف روزانه.',
                    'description' => 'کرم ضد آفتاب نیوآ با فرمولاسیون فاقد چربی و سبک به سرعت جذب پوست شده و از ایجاد لک‌های پوستی ناشی از تابش نور خورشید جلوگیری می‌نماید.',
                    'is_active' => true,
                    'is_featured' => true,
                    'published_at' => now(),
                ]
            );
            $v1_1 = ProductVariant::firstOrCreate(
                ['sku' => 'SUN-NIV-50-30'],
                [
                    'product_id' => $p1->id,
                    'price' => 8500000,
                    'compare_at_price' => 9500000,
                    'stock' => 25,
                    'is_active' => true,
                    'order' => 1,
                ]
            );
            $v1_1->attributeValues()->syncWithoutDetaching([$valSpf50->id, $val30ml->id]);

            $v1_2 = ProductVariant::firstOrCreate(
                ['sku' => 'SUN-NIV-50-50'],
                [
                    'product_id' => $p1->id,
                    'price' => 12000000,
                    'compare_at_price' => null,
                    'stock' => 18,
                    'is_active' => true,
                    'order' => 2,
                ]
            );
            $v1_2->attributeValues()->syncWithoutDetaching([$valSpf50->id, $val50ml->id]);

            $v1_3 = ProductVariant::firstOrCreate(
                ['sku' => 'SUN-NIV-30-50'],
                [
                    'product_id' => $p1->id,
                    'price' => 9500000,
                    'compare_at_price' => 10500000,
                    'stock' => 30,
                    'is_active' => true,
                    'order' => 3,
                ]
            );
            $v1_3->attributeValues()->syncWithoutDetaching([$valSpf30->id, $val50ml->id]);

            // Product 2: کرم‌پودر لورآل اینفالیبل
            $p2 = Product::firstOrCreate(
                ['slug' => 'loreal-infallible-foundation'],
                [
                    'category_id' => $foundation->id,
                    'brand_id' => $loreal->id,
                    'name' => 'کرم‌پودر لورآل اینفالیبل',
                    'short_description' => 'ماندگاری ۳۲ ساعته با پوشش طبیعی مات و بافت تنفس‌پذیر.',
                    'description' => 'کرم پودر اینفالیبل لورآل با فرمولاسیون حاوی ویتامین C، پوشش کامل و یکدست به پوست بخشیده و در برابر تعریق و رطوبت کاملاً مقاوم است.',
                    'is_active' => true,
                    'is_featured' => true,
                    'published_at' => now(),
                ]
            );
            $v2_1 = ProductVariant::firstOrCreate(
                ['sku' => 'FND-LOR-BEJ-MED'],
                [
                    'product_id' => $p2->id,
                    'price' => 15500000,
                    'compare_at_price' => 18000000,
                    'stock' => 12,
                    'is_active' => true,
                    'order' => 1,
                ]
            );
            $v2_1->attributeValues()->syncWithoutDetaching([$valBeige->id, $valCovMed->id]);

            $v2_2 = ProductVariant::firstOrCreate(
                ['sku' => 'FND-LOR-BEJ-FUL'],
                [
                    'product_id' => $p2->id,
                    'price' => 16500000,
                    'compare_at_price' => 18000000,
                    'stock' => 8,
                    'is_active' => true,
                    'order' => 2,
                ]
            );
            $v2_2->attributeValues()->syncWithoutDetaching([$valBeige->id, $valCovFull->id]);

            $v2_3 = ProductVariant::firstOrCreate(
                ['sku' => 'FND-LOR-NUD-MED'],
                [
                    'product_id' => $p2->id,
                    'price' => 15500000,
                    'compare_at_price' => null,
                    'stock' => 20,
                    'is_active' => true,
                    'order' => 3,
                ]
            );
            $v2_3->attributeValues()->syncWithoutDetaching([$valNude->id, $valCovMed->id]);

            $v2_4 = ProductVariant::firstOrCreate(
                ['sku' => 'FND-LOR-NUD-FUL'],
                [
                    'product_id' => $p2->id,
                    'price' => 16500000,
                    'compare_at_price' => 19000000,
                    'stock' => 0, // out of stock
                    'is_active' => true,
                    'order' => 4,
                ]
            );
            $v2_4->attributeValues()->syncWithoutDetaching([$valNude->id, $valCovFull->id]);

            // Product 3: رژلب مای شماره 24
            $p3 = Product::firstOrCreate(
                ['slug' => 'my-lipstick-collection'],
                [
                    'category_id' => $lipstick->id,
                    'brand_id' => $my->id,
                    'name' => 'رژلب مات بادوام مای',
                    'short_description' => 'پیگمنت بالا، بدون ایجاد خشکی با بافت مخملی.',
                    'description' => 'رژ لب مات مای غنی شده با روغن جوجوبا و ویتامین E رطوبت لب‌ها را حفظ کرده و نمایی مخملی و یکنواخت به ارمغان می‌آورد.',
                    'is_active' => true,
                    'is_featured' => false,
                    'published_at' => now(),
                ]
            );
            $v3_1 = ProductVariant::firstOrCreate(
                ['sku' => 'LIP-MY-RED'],
                ['product_id' => $p3->id, 'price' => 3200000, 'stock' => 45, 'is_active' => true, 'order' => 1]
            );
            $v3_1->attributeValues()->syncWithoutDetaching([$valRed->id]);

            $v3_2 = ProductVariant::firstOrCreate(
                ['sku' => 'LIP-MY-PNK'],
                ['product_id' => $p3->id, 'price' => 3200000, 'stock' => 38, 'is_active' => true, 'order' => 2]
            );
            $v3_2->attributeValues()->syncWithoutDetaching([$valPink->id]);

            $v3_3 = ProductVariant::firstOrCreate(
                ['sku' => 'LIP-MY-NUD'],
                ['product_id' => $p3->id, 'price' => 3200000, 'compare_at_price' => 4000000, 'stock' => 52, 'is_active' => true, 'order' => 3]
            );
            $v3_3->attributeValues()->syncWithoutDetaching([$valNude->id]);

            // Product 4: سرم ویتامین C سینره
            $p4 = Product::firstOrCreate(
                ['slug' => 'cinere-vitamin-c-serum'],
                [
                    'category_id' => $serum->id,
                    'brand_id' => $cinere->id,
                    'name' => 'سرم ویتامین C روشن‌کننده سینره',
                    'short_description' => 'روشن‌کننده پوست، کلاژن‌ساز و ضد لک قوی.',
                    'description' => 'سرم ویتامین C سینره با بهره‌گیری از فرم پایدار ویتامین C، پوست را شفاف کرده و روند پیری و اکسیداسیون سلولی را کند می‌کند.',
                    'is_active' => true,
                    'is_featured' => true,
                    'published_at' => now(),
                ]
            );
            $v4_1 = ProductVariant::firstOrCreate(
                ['sku' => 'SRM-CIN-30'],
                ['product_id' => $p4->id, 'price' => 22000000, 'stock' => 15, 'is_active' => true, 'order' => 1]
            );
            $v4_1->attributeValues()->syncWithoutDetaching([$val30ml->id]);

            $v4_2 = ProductVariant::firstOrCreate(
                ['sku' => 'SRM-CIN-50'],
                ['product_id' => $p4->id, 'price' => 32000000, 'compare_at_price' => 38000000, 'stock' => 7, 'is_active' => true, 'order' => 2]
            );
            $v4_2->attributeValues()->syncWithoutDetaching([$val50ml->id]);

            // Product 5: مرطوب‌کننده نیوآ سافت
            $p5 = Product::firstOrCreate(
                ['slug' => 'nivea-soft-moisturizing-cream'],
                [
                    'category_id' => $moisturizer->id,
                    'brand_id' => $nivea->id,
                    'name' => 'کرم مرطوب‌کننده نیوآ سافت',
                    'short_description' => 'آبرسان فوق‌العاده سریع با روغن جوجوبا و ویتامین E.',
                    'description' => 'کرم نیوآ سافت مناسب دست، صورت و بدن با فرمول سبک و جذب سریع برای نرمی و طراوت ۲۴ ساعته پوست طراحی شده است.',
                    'is_active' => true,
                    'is_featured' => false,
                    'published_at' => now(),
                ]
            );
            $v5_1 = ProductVariant::firstOrCreate(
                ['sku' => 'MOI-NIV-50'],
                ['product_id' => $p5->id, 'price' => 6800000, 'stock' => 40, 'is_active' => true, 'order' => 1]
            );
            $v5_1->attributeValues()->syncWithoutDetaching([$val50ml->id]);

            $v5_2 = ProductVariant::firstOrCreate(
                ['sku' => 'MOI-NIV-100'],
                ['product_id' => $p5->id, 'price' => 11500000, 'stock' => 22, 'is_active' => true, 'order' => 2]
            );
            $v5_2->attributeValues()->syncWithoutDetaching([$val100ml->id]);

            $v5_3 = ProductVariant::firstOrCreate(
                ['sku' => 'MOI-NIV-200'],
                ['product_id' => $p5->id, 'price' => 18000000, 'compare_at_price' => 21000000, 'stock' => 10, 'is_active' => true, 'order' => 3]
            );
            $v5_3->attributeValues()->syncWithoutDetaching([$val200ml->id]);

            // Product 6: ریمل حجم‌دهنده ایزادورا
            $p6 = Product::firstOrCreate(
                ['slug' => 'isadora-big-bold-mascara'],
                [
                    'category_id' => $mascara->id,
                    'brand_id' => $isadora->id,
                    'name' => 'ریمل حجم‌دهنده بیگ بولد ایزادورا',
                    'short_description' => 'برس بزرگ با بافت کرمی کربنی برای بیشترین حجم‌دهی بدون ریزش.',
                    'description' => 'ریمل بیلد بولد ایزادورا فاقد اسانس، ضد حساسیت و مناسب چشم‌های حساس و استفاده‌کنندگان از لنز می‌باشد.',
                    'is_active' => true,
                    'is_featured' => true,
                    'published_at' => now(),
                ]
            );
            ProductVariant::firstOrCreate(
                ['sku' => 'MSC-ISA-001'],
                ['product_id' => $p6->id, 'price' => 9200000, 'stock' => 30, 'is_active' => true, 'order' => 1]
            );

            // Product 7: شامپو ترمیمی لورآل
            $p7 = Product::firstOrCreate(
                ['slug' => 'loreal-elvive-total-repair-shampoo'],
                [
                    'category_id' => $shampoo->id,
                    'brand_id' => $loreal->id,
                    'name' => 'شامپو ترمیم‌کننده السو لورآل',
                    'short_description' => 'حاوی سرامید و پروکراتین جهت احیای تارهای آسیب‌دیده مو.',
                    'description' => 'شامپو توتال ریپیر ۵ لورآل با بازسازی ساختار فیبر مو، موهای ضعیف و شکننده را تغذیه کرده و درخشش طبیعی به آن‌ها بازمی‌گرداند.',
                    'is_active' => true,
                    'is_featured' => false,
                    'published_at' => now(),
                ]
            );
            $v7_1 = ProductVariant::firstOrCreate(
                ['sku' => 'SHP-LOR-200'],
                ['product_id' => $p7->id, 'price' => 14000000, 'stock' => 35, 'is_active' => true, 'order' => 1]
            );
            $v7_1->attributeValues()->syncWithoutDetaching([$val200ml->id]);

            $v7_2 = ProductVariant::firstOrCreate(
                ['sku' => 'SHP-LOR-100'],
                ['product_id' => $p7->id, 'price' => 8500000, 'stock' => 50, 'is_active' => true, 'order' => 2]
            );
            $v7_2->attributeValues()->syncWithoutDetaching([$val100ml->id]);

            // Product 8: سایه چشم مای پالت 8 رنگ
            $p8 = Product::firstOrCreate(
                ['slug' => 'my-eyeshadow-palette-8color'],
                [
                    'category_id' => $eyeshadow->id,
                    'brand_id' => $my->id,
                    'name' => 'پالت سایه چشم ۸ رنگ مای',
                    'short_description' => 'ترکیبی از رنگ‌های مات و شاین با فید آسان و پیگمنت قوی.',
                    'description' => 'پالت سایه مای مناسب آرایش‌های روز و شب با طیف رنگی گرم و ماندگاری مناسب بدون جمع‌شدگی پشت پلک.',
                    'is_active' => true,
                    'is_featured' => true,
                    'published_at' => now(),
                ]
            );
            ProductVariant::firstOrCreate(
                ['sku' => 'EYS-MY-PLT8'],
                [
                    'product_id' => $p8->id,
                    'price' => 7500000,
                    'compare_at_price' => 9000000,
                    'stock' => 25,
                    'is_active' => true,
                    'order' => 1,
                ]
            );

            // 6. Product Images (Gallery)
            $productImages = [
                'nivea-sunscreen-spf50' => 'sunscreen.jpg',
                'loreal-infallible-foundation' => 'foundation.jpg',
                'my-lipstick-collection' => 'lipstick.jpg',
                'cinere-vitamin-c-serum' => 'serum.jpg',
                'nivea-soft-moisturizing-cream' => 'moisturizer.jpg',
                'isadora-big-bold-mascara' => 'mascara.jpg',
                'loreal-elvive-total-repair-shampoo' => 'shampoo.jpg',
                'my-eyeshadow-palette-8color' => 'eyeshadow.jpg',
            ];

            foreach ($productImages as $slug => $imageFile) {
                $imagePath = database_path("seeders/images/{$imageFile}");
                if (! file_exists($imagePath)) {
                    continue;
                }

                $product = Product::where('slug', $slug)->first();
                if ($product && $product->getFirstMedia('gallery') === null) {
                    $product->addMedia($imagePath)
                        ->preservingOriginal()
                        ->toMediaCollection('gallery');
                }
            }
        });
    }
}
