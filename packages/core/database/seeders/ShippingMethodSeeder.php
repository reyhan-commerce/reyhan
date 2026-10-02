<?php

declare(strict_types=1);

namespace Reyhan\Core\Database\Seeders;

use Reyhan\Core\Models\ShippingMethod;
use Illuminate\Database\Seeder;

class ShippingMethodSeeder extends Seeder
{
    public function run(): void
    {
        $methods = [
            [
                'name' => 'پست پیشتاز سراسری',
                'slug' => 'pishtaz',
                'description' => 'ارسال با پست پیشتاز شرکت ملی پست به سراسر ایران با رهگیری آنلاین لحظه‌ای',
                'icon' => 'i-lucide-truck',
                'base_cost' => 650000, // 65,000 Toman
                'cost_per_kg' => 100000,
                'free_shipping_threshold' => null, // inherits general setting
                'estimated_delivery_days' => '۲ تا ۴ روز کاری',
                'requires_time_slot' => false,
                'supported_provinces' => null,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'پیک اکسپرس موتوری (تحویل فوری)',
                'slug' => 'express_courier',
                'description' => 'ویژه استان تهران و البرز با امکان انتخاب بازه زمانی تحویل',
                'icon' => 'i-lucide-zap',
                'base_cost' => 950000, // 95,000 Toman
                'cost_per_kg' => 0,
                'free_shipping_threshold' => null,
                'estimated_delivery_days' => 'تحویل در همان روز یا روز کاری بعد',
                'requires_time_slot' => true,
                'supported_provinces' => [1, 5], // تهران و البرز
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'تیپاکس اکسپرس درب‌به‌درب',
                'slug' => 'tipax',
                'description' => 'تحویل سریع درب به درب توسط شبکه توزیع تیپاکس همراه با پیامک تحویل',
                'icon' => 'i-lucide-package-check',
                'base_cost' => 850000, // 85,000 Toman
                'cost_per_kg' => 150000,
                'free_shipping_threshold' => null,
                'estimated_delivery_days' => '۱ تا ۲ روز کاری',
                'requires_time_slot' => false,
                'supported_provinces' => null,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'باربری و تحویل بار حجیم/سنگین',
                'slug' => 'freight',
                'description' => 'مخصوص لوازم خانگی و مرسولات با ابعاد بزرگ تا درب انبار باربری شهر مقصد',
                'icon' => 'i-lucide-container',
                'base_cost' => 1800000, // 180,000 Toman
                'cost_per_kg' => 80000,
                'free_shipping_threshold' => null,
                'estimated_delivery_days' => '۳ تا ۵ روز کاری',
                'requires_time_slot' => false,
                'supported_provinces' => null,
                'is_active' => true,
                'sort_order' => 4,
            ],
        ];

        foreach ($methods as $data) {
            ShippingMethod::updateOrCreate(
                ['slug' => $data['slug']],
                $data
            );
        }
    }
}
