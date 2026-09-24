<?php

declare(strict_types=1);

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;

test('search suggestions returns empty arrays when query is too short', function () {
    $response = $this->getJson('/api/v1/search/suggestions?q=a');

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'data' => [
                'query' => 'a',
                'products' => [],
                'categories' => [],
                'brands' => [],
            ],
        ]);
});

test('search suggestions finds products, categories, and brands', function () {
    $category = Category::factory()->create([
        'name' => 'گوشی هوشمند سامسونگ',
        'slug' => 'test-cat-'.uniqid(),
    ]);

    $brand = Brand::factory()->create([
        'name' => 'سامسونگ تست',
        'name_en' => 'Samsung Test',
        'slug' => 'test-brand-'.uniqid(),
    ]);

    $product = Product::factory()->create([
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'name' => 'گوشی سامسونگ تست الترا',
        'slug' => 'test-galaxy-'.uniqid(),
        'published_at' => now()->subDay(),
    ]);

    ProductVariant::factory()->create([
        'product_id' => $product->id,
        'sku' => 'TEST-SKU-'.uniqid(),
        'price' => 50000000,
        'stock' => 10,
    ]);

    $response = $this->getJson('/api/v1/search/suggestions?q=سامسونگ');

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.query', 'سامسونگ');

    expect($response->json('data.products'))->not->toBeEmpty()
        ->and($response->json('data.brands'))->not->toBeEmpty()
        ->and($response->json('data.categories'))->not->toBeEmpty();
});
