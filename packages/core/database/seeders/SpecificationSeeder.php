<?php

declare(strict_types=1);

namespace Reyhan\Core\Database\Seeders;

use Reyhan\Core\Models\Category;
use Reyhan\Core\Models\Product;
use Reyhan\Core\Models\ProductSpecification;
use Reyhan\Core\Models\Specification;
use Reyhan\Core\Models\SpecificationGroup;
use Illuminate\Database\Seeder;

class SpecificationSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Groups
        $physicalGroup = SpecificationGroup::firstOrCreate(['name' => 'مشخصات فیزیکی و طراحی'], ['order' => 1]);
        $hardwareGroup = SpecificationGroup::firstOrCreate(['name' => 'پردازنده و سخت‌افزار'], ['order' => 2]);
        $displayGroup = SpecificationGroup::firstOrCreate(['name' => 'صفحه نمایش'], ['order' => 3]);
        $cameraGroup = SpecificationGroup::firstOrCreate(['name' => 'دوربین و باتری'], ['order' => 4]);
        $featuresGroup = SpecificationGroup::firstOrCreate(['name' => 'ارتباطات و ویژگی‌های خاص'], ['order' => 5]);

        // 2. Specifications
        $specs = [
            // Physical
            'weight' => Specification::firstOrCreate(
                ['specification_group_id' => $physicalGroup->id, 'name' => 'وزن'],
                ['unit' => 'گرم', 'type' => 'number', 'is_filterable' => true, 'order' => 1]
            ),
            'dimensions' => Specification::firstOrCreate(
                ['specification_group_id' => $physicalGroup->id, 'name' => 'ابعاد'],
                ['unit' => 'میلی‌متر', 'type' => 'text', 'order' => 2]
            ),
            'body_material' => Specification::firstOrCreate(
                ['specification_group_id' => $physicalGroup->id, 'name' => 'جنس بدنه'],
                ['type' => 'text', 'is_filterable' => true, 'order' => 3]
            ),

            // Hardware
            'chipset' => Specification::firstOrCreate(
                ['specification_group_id' => $hardwareGroup->id, 'name' => 'تراشه (Chipset)'],
                ['type' => 'text', 'is_filterable' => true, 'order' => 1]
            ),
            'ram' => Specification::firstOrCreate(
                ['specification_group_id' => $hardwareGroup->id, 'name' => 'حافظه رم (RAM)'],
                ['unit' => 'گیگابایت', 'type' => 'select', 'is_filterable' => true, 'order' => 2]
            ),

            // Display
            'screen_size' => Specification::firstOrCreate(
                ['specification_group_id' => $displayGroup->id, 'name' => 'اندازه صفحه نمایش'],
                ['unit' => 'اینچ', 'type' => 'number', 'is_filterable' => true, 'order' => 1]
            ),
            'refresh_rate' => Specification::firstOrCreate(
                ['specification_group_id' => $displayGroup->id, 'name' => 'نرخ نوسازی تصویر'],
                ['unit' => 'هرتز', 'type' => 'select', 'is_filterable' => true, 'order' => 2]
            ),

            // Camera / Battery
            'battery' => Specification::firstOrCreate(
                ['specification_group_id' => $cameraGroup->id, 'name' => 'ظرفیت باتری'],
                ['unit' => 'میلی‌آمپر ساعت', 'type' => 'number', 'is_filterable' => true, 'order' => 1]
            ),
            'fast_charging' => Specification::firstOrCreate(
                ['specification_group_id' => $cameraGroup->id, 'name' => 'توان شارژ سریع'],
                ['unit' => 'وات', 'type' => 'number', 'order' => 2]
            ),

            // Features
            'os' => Specification::firstOrCreate(
                ['specification_group_id' => $featuresGroup->id, 'name' => 'سیستم عامل'],
                ['type' => 'text', 'is_filterable' => true, 'order' => 1]
            ),
            'warranty' => Specification::firstOrCreate(
                ['specification_group_id' => $featuresGroup->id, 'name' => 'گارانتی و خدمات'],
                ['type' => 'text', 'order' => 2]
            ),
        ];

        // 3. Attach to all categories
        $categories = Category::all();
        $specIds = array_map(fn ($s) => $s->id, $specs);
        foreach ($categories as $cat) {
            $cat->specifications()->syncWithoutDetaching($specIds);
        }

        // 4. Attach values to existing products
        $products = Product::all();
        foreach ($products as $product) {
            ProductSpecification::firstOrCreate(
                ['product_id' => $product->id, 'specification_id' => $specs['weight']->id],
                ['value' => '۱۸۵']
            );
            ProductSpecification::firstOrCreate(
                ['product_id' => $product->id, 'specification_id' => $specs['dimensions']->id],
                ['value' => '۱۶۱.۴ × ۷۵.۳ × ۸.۵']
            );
            ProductSpecification::firstOrCreate(
                ['product_id' => $product->id, 'specification_id' => $specs['body_material']->id],
                ['value' => 'فریم آلومینیومی با پنل شیشه‌ای گوریلا گلس']
            );
            ProductSpecification::firstOrCreate(
                ['product_id' => $product->id, 'specification_id' => $specs['chipset']->id],
                ['value' => 'هشت هسته‌ای با فرکانس ۳.۲ گیگاهرتز']
            );
            ProductSpecification::firstOrCreate(
                ['product_id' => $product->id, 'specification_id' => $specs['ram']->id],
                ['value' => '۸']
            );
            ProductSpecification::firstOrCreate(
                ['product_id' => $product->id, 'specification_id' => $specs['screen_size']->id],
                ['value' => '۶.۷']
            );
            ProductSpecification::firstOrCreate(
                ['product_id' => $product->id, 'specification_id' => $specs['refresh_rate']->id],
                ['value' => '۱۲۰']
            );
            ProductSpecification::firstOrCreate(
                ['product_id' => $product->id, 'specification_id' => $specs['battery']->id],
                ['value' => '۵۰۰۰']
            );
            ProductSpecification::firstOrCreate(
                ['product_id' => $product->id, 'specification_id' => $specs['fast_charging']->id],
                ['value' => '۶۷']
            );
            ProductSpecification::firstOrCreate(
                ['product_id' => $product->id, 'specification_id' => $specs['warranty']->id],
                ['value' => '۱۸ ماه گارانتی معتبر شرکتی + ۷ روز مهلت تست و بازگشت کالا']
            );
        }
    }
}
