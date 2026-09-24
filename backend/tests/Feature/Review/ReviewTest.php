<?php

declare(strict_types=1);

use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('public can view reviews and stats for product', function () {
    $category = Category::factory()->create([
        'name' => 'آرایشی تست',
        'slug' => 'cat-rev-'.uniqid(),
    ]);
    $brand = Brand::factory()->create([
        'name' => 'برند نقد',
        'slug' => 'brand-rev-'.uniqid(),
    ]);
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'name' => 'کرم پودر نقد',
        'slug' => 'prod-rev-'.uniqid(),
    ]);
    $user = User::factory()->create([
        'first_name' => 'مهسا',
        'last_name' => 'احمدی',
    ]);

    Review::factory()->approved()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'rating' => 5,
        'longevity_rating' => 4,
        'coverage_rating' => 5,
        'value_rating' => 4,
        'comment' => 'کیفیت ساخت بسیار عالی و ماندگاری بالا',
        'strengths' => ['ماندگاری بالا', 'بافت سبک'],
        'weaknesses' => ['قیمت نسبتا بالا'],
        'is_verified_purchase' => true,
    ]);

    $response = $this->getJson("/api/v1/products/{$product->id}/reviews");

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'data' => [
                'stats' => [
                    'total_reviews' => 1,
                    'average_rating' => 5.0,
                ],
            ],
        ])
        ->assertJsonCount(1, 'data.reviews.data');
});

test('user who purchased product gets verified buyer badge upon review submission', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create([
        'name' => 'دسته تست خرید',
        'slug' => 'cat-buy-'.uniqid(),
    ]);
    $brand = Brand::factory()->create([
        'name' => 'برند تست خرید',
        'slug' => 'brand-buy-'.uniqid(),
    ]);
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'name' => 'سرم ویتامین سی تست',
        'slug' => 'prod-buy-'.uniqid(),
    ]);
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'sku' => 'SKU-REV-'.uniqid(),
        'price' => 100000,
        'stock' => 10,
    ]);

    // Create a delivered order for user
    $order = Order::factory()->completed()->create([
        'user_id' => $user->id,
        'items_subtotal' => 100000,
        'final_payable' => 150000,
    ]);

    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'product_variant_id' => $variant->id,
        'product_name' => $product->name,
        'variant_title' => $variant->title ?? 'پیش‌فرض',
        'sku' => $variant->sku,
        'unit_price' => 100000,
        'discount_amount' => 0,
        'final_price' => 100000,
        'quantity' => 1,
        'total_price' => 100000,
    ]);

    Sanctum::actingAs($user);

    $response = $this->postJson("/api/v1/products/{$product->id}/reviews", [
        'rating' => 5,
        'longevity_rating' => 5,
        'coverage_rating' => 4,
        'value_rating' => 5,
        'comment' => 'محصول کاملاً اصل بود و بسته‌بندی عالی داشت.',
        'strengths' => ['اصالت کالا'],
        'weaknesses' => [],
    ]);

    $response->assertCreated()
        ->assertJson([
            'success' => true,
            'data' => [
                'is_verified_purchase' => true,
                'rating' => 5,
            ],
        ]);

    $this->assertDatabaseHas('reviews', [
        'user_id' => $user->id,
        'product_id' => $product->id,
        'is_verified_purchase' => true,
    ]);
});
