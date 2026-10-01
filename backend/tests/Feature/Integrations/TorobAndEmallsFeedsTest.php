<?php

declare(strict_types=1);

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

beforeEach(function (): void {
    $this->category = Category::factory()->create(['name' => 'لوازم دیجیتال']);
    $this->brand = Brand::factory()->create(['name' => 'سامسونگ']);

    $this->product = Product::factory()->create([
        'category_id' => $this->category->id,
        'brand_id' => $this->brand->id,
        'name' => 'گوشی موبایل گلکسی',
        'slug' => 'galaxy-phone-'.uniqid(),
        'is_active' => true,
    ]);

    $this->variant = ProductVariant::factory()->create([
        'product_id' => $this->product->id,
        'price' => 300000000, // 30,000,000 Tomans (300,000,000 Rials)
        'compare_at_price' => 320000000,
        'stock' => 5,
        'is_active' => true,
    ]);
});

it('serves valid Torob standard product feed with Toman pricing and availability', function (): void {
    $response = $this->getJson(route('integrations.torob.products'));

    $response->assertOk()
        ->assertJsonStructure([
            'count',
            'max_pages',
            'current_page',
            'products' => [
                '*' => [
                    'product_id',
                    'page_unique_code',
                    'title',
                    'page_url',
                    'price',
                    'old_price',
                    'availability',
                    'category_name',
                    'brand_name',
                ],
            ],
        ]);

    $data = $response->json();
    expect($data['count'])->toBeGreaterThanOrEqual(1);

    $target = collect($data['products'])->firstWhere('product_id', (string) $this->product->id);
    expect($target)->not->toBeNull();
    expect($target['price'])->toBe(30000000); // 30,000,000 Tomans
    expect($target['old_price'])->toBe(32000000);
    expect($target['availability'])->toBe('instock');
});

it('serves valid Emalls standard product feed with pagination and availability', function (): void {
    $response = $this->getJson(route('integrations.emalls.products'));

    $response->assertOk()
        ->assertJsonStructure([
            'total',
            'pages',
            'page',
            'items' => [
                '*' => [
                    'id',
                    'title',
                    'subtitle',
                    'url',
                    'price',
                    'old_price',
                    'is_available',
                ],
            ],
        ]);

    $data = $response->json();
    expect($data['total'])->toBeGreaterThanOrEqual(1);

    $target = collect($data['items'])->firstWhere('id', (string) $this->product->id);
    expect($target)->not->toBeNull();
    expect($target['price'])->toBe(30000000);
    expect($target['is_available'])->toBeTrue();
});
