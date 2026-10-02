<?php

declare(strict_types=1);

namespace Reyhan\Core\Database\Factories;

use Reyhan\Core\Enums\BannerPosition;
use Reyhan\Core\Models\Banner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Banner>
 */
class BannerFactory extends Factory
{
    protected $model = Banner::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'subtitle' => fake()->sentence(5),
            'image_url' => fake()->imageUrl(1200, 400),
            'mobile_image_url' => null,
            'link_url' => '/products',
            'position' => BannerPosition::HomeSlider,
            'order' => 1,
            'is_active' => true,
            'starts_at' => null,
            'ends_at' => null,
        ];
    }
}
