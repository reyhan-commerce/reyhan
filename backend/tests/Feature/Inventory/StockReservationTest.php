<?php

declare(strict_types=1);

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\Inventory\StockReservationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

uses(DatabaseTransactions::class);

beforeEach(function (): void {
    Redis::flushdb();

    $category = Category::factory()->create([
        'name' => 'تست انبار',
        'slug' => 'inv-cat-'.Str::random(6),
    ]);

    $brand = Brand::factory()->create([
        'name' => 'تست برند',
        'slug' => 'inv-brand-'.Str::random(6),
    ]);

    $product = Product::factory()->create([
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'name' => 'محصول انبارداری',
        'slug' => 'inv-prod-'.Str::random(6),
        'published_at' => now()->subMinute(),
    ]);

    $this->variant = ProductVariant::factory()->inStock(5)->create([
        'product_id' => $product->id,
        'sku' => 'INV-TEST-01',
        'price' => 1000000,
    ]);

    $this->inventoryService = app(StockReservationService::class);
});

it('reserves stock atomically when available and prevents overselling', function (): void {
    $reservationA = (string) Str::uuid();
    $reservationB = (string) Str::uuid();

    // Reserve 3 of 5 -> should succeed
    $successA = $this->inventoryService->reserve($this->variant->id, 3, $reservationA);
    expect($successA)->toBeTrue();
    expect($this->inventoryService->getAvailableStock($this->variant))->toBe(2);

    // Try to reserve 3 more (only 2 left) -> should fail atomically
    $successB = $this->inventoryService->reserve($this->variant->id, 3, $reservationB);
    expect($successB)->toBeFalse();
    expect($this->inventoryService->getAvailableStock($this->variant))->toBe(2);

    // Reserve 2 -> should succeed
    $successC = $this->inventoryService->reserve($this->variant->id, 2, $reservationB);
    expect($successC)->toBeTrue();
    expect($this->inventoryService->getAvailableStock($this->variant))->toBe(0);
});

it('releases reservation properly and restores available stock', function (): void {
    $reservation = (string) Str::uuid();

    $this->inventoryService->reserve($this->variant->id, 4, $reservation);
    expect($this->inventoryService->getAvailableStock($this->variant))->toBe(1);

    // Release reservation
    $this->inventoryService->release($this->variant->id, 4, $reservation);
    expect($this->inventoryService->getAvailableStock($this->variant))->toBe(5);
});
