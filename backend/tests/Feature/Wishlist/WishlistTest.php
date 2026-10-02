<?php

declare(strict_types=1);

use Reyhan\Core\Models\Brand;
use Reyhan\Core\Models\Category;
use Reyhan\Core\Models\Product;
use Reyhan\Core\Models\User;
use Reyhan\Core\Models\Wishlist;
use Laravel\Sanctum\Sanctum;

test('authenticated user can toggle a product in wishlist', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create([
        'name' => 'دسته تست',
        'slug' => 'cat-wishlist-'.uniqid(),
    ]);
    $brand = Brand::factory()->create([
        'name' => 'برند تست',
        'slug' => 'brand-wishlist-'.uniqid(),
    ]);

    $product = Product::factory()->create([
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'name' => 'محصول علاقه‌مندی',
        'slug' => 'wishlist-prod-'.uniqid(),
    ]);

    Sanctum::actingAs($user);

    // 1. Add to wishlist
    $responseAdd = $this->postJson("/api/v1/wishlist/{$product->id}/toggle");
    $responseAdd->assertOk()
        ->assertJson([
            'success' => true,
            'in_wishlist' => true,
        ]);

    $this->assertDatabaseHas('wishlists', [
        'user_id' => $user->id,
        'product_id' => $product->id,
    ]);

    // 2. Query ids
    $responseIds = $this->getJson('/api/v1/wishlist/ids');
    $responseIds->assertOk()
        ->assertJson([
            'success' => true,
            'data' => [
                'ids' => [$product->id],
            ],
        ]);

    // 3. Remove from wishlist
    $responseRemove = $this->postJson("/api/v1/wishlist/{$product->id}/toggle");
    $responseRemove->assertOk()
        ->assertJson([
            'success' => true,
            'in_wishlist' => false,
        ]);

    $this->assertDatabaseMissing('wishlists', [
        'user_id' => $user->id,
        'product_id' => $product->id,
    ]);
});

test('authenticated user can list wishlist items', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create([
        'name' => 'دسته لیست',
        'slug' => 'cat-list-'.uniqid(),
    ]);
    $brand = Brand::factory()->create([
        'name' => 'برند لیست',
        'slug' => 'brand-list-'.uniqid(),
    ]);

    $product = Product::factory()->create([
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'name' => 'محصول لیست تست',
        'slug' => 'list-prod-'.uniqid(),
    ]);

    Wishlist::factory()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
    ]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/wishlist');

    $response->assertOk()
        ->assertJson([
            'success' => true,
        ])
        ->assertJsonCount(1, 'data.data');
});
