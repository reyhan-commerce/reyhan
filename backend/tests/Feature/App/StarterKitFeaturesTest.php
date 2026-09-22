<?php

declare(strict_types=1);

use App\Enums\ReviewStatus;
use App\Models\Admin;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;

test('public features endpoint returns active feature flags', function () {
    $response = $this->getJson(route('app.features'));

    $response->assertOk()
        ->assertJsonStructure([
            'success',
            'data' => [
                'reviews',
                'coupons',
                'wishlist',
                'brands',
                'stock_alerts',
                'comparison',
                'blog',
                'loyalty',
            ],
        ]);

    expect($response->json('data.reviews'))->toBeTrue();
});

test('public theme endpoint returns theme design tokens', function () {
    $response = $this->getJson(route('app.theme'));

    $response->assertOk()
        ->assertJsonStructure([
            'success',
            'data' => [
                'primary_color',
                'secondary_color',
                'border_radius',
                'spacing_scale',
                'shadow_scale',
                'blur_scale',
                'font_family',
                'font_scale',
            ],
        ]);
});

test('reviews support dynamic criteria ratings and compute averages', function () {
    $user = User::factory()->create();
    $category = Category::create(['name' => 'تست', 'slug' => 'test-cat', 'is_active' => true]);
    $brand = Brand::create(['name' => 'برند تست', 'slug' => 'test-brand', 'is_active' => true]);
    $product = Product::create([
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'name' => 'کالای تست',
        'slug' => 'test-product-criteria',
        'base_price' => 1000000,
        'is_active' => true,
    ]);

    $review = Review::create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'rating' => 5,
        'criteria_ratings' => [
            'quality' => 5,
            'durability' => 4,
            'value' => 5,
        ],
        'comment' => 'عالی و باکیفیت',
        'status' => ReviewStatus::Approved,
    ]);

    expect($review->criteria_ratings['quality'])->toBe(5);

    $stats = $product->getReviewStats();
    expect($stats['average_rating'])->toBe(5.0)
        ->and($stats['criteria_averages']['quality'])->toBe(5.0)
        ->and($stats['criteria_averages']['durability'])->toBe(4.0);
});

test('shop preset command scaffolds vertical correctly and provisions initial admin', function () {
    $this->artisan('shop:preset apparel --no-interaction')
        ->assertSuccessful();

    $categoryCount = Category::count();
    $productCount = Product::count();
    $admin = Admin::where('email', 'admin@easyshop.local')->first();

    expect($categoryCount)->toBeGreaterThan(0)
        ->and($productCount)->toBeGreaterThan(0)
        ->and($admin)->not->toBeNull()
        ->and($admin?->name)->toBe('Super Admin');
});
