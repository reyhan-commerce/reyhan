<?php

declare(strict_types=1);

use Reyhan\Core\Models\Brand;
use Reyhan\Core\Models\Category;
use Reyhan\Core\Models\Product;
use Reyhan\Core\Models\ProductSpecification;
use Reyhan\Core\Models\ProductVariant;
use Reyhan\Core\Models\Specification;
use Reyhan\Core\Models\SpecificationGroup;

beforeEach(function (): void {
    $this->category = Category::factory()->create(['name' => 'لپ‌تاپ و اولترابوک', 'slug' => 'laptops']);
    $this->brand = Brand::factory()->create(['name' => 'ایسوس', 'slug' => 'asus']);

    $this->group = SpecificationGroup::factory()->create(['name' => 'مشخصات سخت‌افزاری', 'order' => 1]);
    $this->specRam = Specification::factory()->create([
        'specification_group_id' => $this->group->id,
        'name' => 'حافظه رم',
        'unit' => 'گیگابایت',
        'type' => 'number',
        'is_filterable' => true,
        'order' => 1,
    ]);
    $this->specCpu = Specification::factory()->create([
        'specification_group_id' => $this->group->id,
        'name' => 'پردازنده',
        'type' => 'text',
        'is_filterable' => true,
        'order' => 2,
    ]);

    $this->product1 = Product::factory()->create([
        'category_id' => $this->category->id,
        'brand_id' => $this->brand->id,
        'name' => 'لپ‌تاپ ایسوس زن‌بوک ۱۴',
        'slug' => 'asus-zenbook-14',
        'is_active' => true,
    ]);

    ProductVariant::factory()->create([
        'product_id' => $this->product1->id,
        'price' => 50000000,
        'stock' => 5,
        'is_active' => true,
    ]);

    ProductSpecification::factory()->create([
        'product_id' => $this->product1->id,
        'specification_id' => $this->specRam->id,
        'value' => '۱۶',
    ]);
    ProductSpecification::factory()->create([
        'product_id' => $this->product1->id,
        'specification_id' => $this->specCpu->id,
        'value' => 'Intel Core Ultra 7',
    ]);

    $this->product2 = Product::factory()->create([
        'category_id' => $this->category->id,
        'brand_id' => $this->brand->id,
        'name' => 'لپ‌تاپ ایسوس راگ زفیروس',
        'slug' => 'asus-rog-zephyrus',
        'is_active' => true,
    ]);

    ProductVariant::factory()->create([
        'product_id' => $this->product2->id,
        'price' => 90000000,
        'stock' => 0,
        'is_active' => true,
    ]);

    ProductSpecification::factory()->create([
        'product_id' => $this->product2->id,
        'specification_id' => $this->specRam->id,
        'value' => '۳۲',
    ]);
    ProductSpecification::factory()->create([
        'product_id' => $this->product2->id,
        'specification_id' => $this->specCpu->id,
        'value' => 'AMD Ryzen 9',
    ]);
});

test('product detail API returns structured specifications grouped by group', function (): void {
    $response = $this->getJson("/api/v1/products/{$this->product1->slug}");

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'slug',
                'specifications' => [
                    '*' => [
                        'group_id',
                        'group_name',
                        'items' => [
                            '*' => ['id', 'name', 'value', 'unit'],
                        ],
                    ],
                ],
            ],
        ]);

    $specs = $response->json('data.specifications');
    expect($specs)->toHaveCount(1)
        ->and($specs[0]['group_name'])->toBe('مشخصات سخت‌افزاری')
        ->and($specs[0]['items'])->toHaveCount(2);
});

test('compare products API builds comparison matrix for selected items', function (): void {
    $response = $this->postJson('/api/v1/products/compare', [
        'products' => [$this->product1->slug, $this->product2->slug],
    ]);

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonStructure([
            'data' => [
                'products' => [
                    '*' => ['id', 'name', 'slug', 'thumbnail', 'has_stock'],
                ],
                'specification_groups' => [
                    '*' => [
                        'id',
                        'name',
                        'items' => [
                            '*' => ['id', 'name', 'unit', 'values'],
                        ],
                    ],
                ],
            ],
        ]);

    $data = $response->json('data');
    expect($data['products'])->toHaveCount(2)
        ->and($data['specification_groups'])->toHaveCount(1);

    $items = $data['specification_groups'][0]['items'];
    $ramItem = collect($items)->firstWhere('name', 'حافظه رم');
    expect($ramItem['values'][(string) $this->product1->id])->toBe('۱۶ گیگابایت')
        ->and($ramItem['values'][(string) $this->product2->id])->toBe('۳۲ گیگابایت');
});

test('subscribes mobile number to stock alerts for out-of-stock variant', function (): void {
    $outOfStockVariant = $this->product2->activeVariants->first();

    $response = $this->postJson('/api/v1/catalog/stock-alerts', [
        'variant_id' => $outOfStockVariant->id,
        'mobile' => '09123456789',
    ]);

    $response->assertOk()
        ->assertJsonPath('success', true);

    $this->assertDatabaseHas('stock_alerts', [
        'product_variant_id' => $outOfStockVariant->id,
        'mobile' => '09123456789',
        'status' => 'pending',
    ]);
});

test('catalog filters endpoint returns dynamic brands, attributes and price bounds', function (): void {
    $response = $this->getJson("/api/v1/catalog/filters?category={$this->category->slug}");

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonStructure([
            'data' => [
                'brands',
                'attributes',
                'price_bounds' => ['min', 'max'],
            ],
        ]);

    $brands = $response->json('data.brands');
    expect($brands)->toHaveCount(1)
        ->and($brands[0]['slug'])->toBe('asus');
});

test('related products endpoint returns products from same category or brand', function (): void {
    $response = $this->getJson("/api/v1/products/{$this->product1->slug}/related");

    $response->assertOk()
        ->assertJsonPath('success', true);

    $related = $response->json('data');
    expect($related)->toHaveCount(1)
        ->and($related[0]['slug'])->toBe($this->product2->slug);
});
