<?php

declare(strict_types=1);

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;

uses(DatabaseTransactions::class);

beforeEach(function (): void {
    Cache::flush();
});

it('returns paginated active products', function (): void {
    $category = Category::factory()->create([
        'name' => 'پوست',
        'slug' => 'skin',
    ]);

    $brand = Brand::factory()->create([
        'name' => 'برند تستی',
        'slug' => 'test-brand',
    ]);

    $product = Product::factory()->create([
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'name' => 'کرم مرطوب کننده تستی',
        'slug' => 'test-moisturizer',
        'published_at' => now()->subMinute(),
    ]);

    ProductVariant::factory()->create([
        'product_id' => $product->id,
        'sku' => 'TEST-MOI-01',
        'price' => 5000000,
        'stock' => 10,
    ]);

    $response = $this->getJson(route('products.index'));

    $response->assertOk()
        ->assertJson([
            'success' => true,
        ])
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'slug', 'price_range', 'is_in_stock'],
            ],
            'meta',
            'links',
            'success',
        ]);
});

it('filters products by category slug', function (): void {
    $cat1 = Category::factory()->create(['name' => 'دسته‌بندی یک', 'slug' => 'cat-one']);
    $cat2 = Category::factory()->create(['name' => 'دسته‌بندی دو', 'slug' => 'cat-two']);

    $p1 = Product::factory()->create([
        'category_id' => $cat1->id,
        'name' => 'کالای اول',
        'slug' => 'product-one',
        'published_at' => now()->subMinute(),
    ]);
    ProductVariant::factory()->create(['product_id' => $p1->id, 'sku' => 'SKU-001', 'price' => 1000, 'stock' => 5]);

    $p2 = Product::factory()->create([
        'category_id' => $cat2->id,
        'name' => 'کالای دوم',
        'slug' => 'product-two',
        'published_at' => now()->subMinute(),
    ]);
    ProductVariant::factory()->create(['product_id' => $p2->id, 'sku' => 'SKU-002', 'price' => 2000, 'stock' => 5]);

    $response = $this->getJson(route('products.index', ['category' => 'cat-one']));

    $response->assertOk();
    $data = $response->json('data');
    expect(collect($data)->pluck('slug'))->toContain('product-one')
        ->and(collect($data)->pluck('slug'))->not->toContain('product-two');
});

it('filters products by brand slug', function (): void {
    $cat = Category::factory()->create(['name' => 'دسته‌بندی', 'slug' => 'cat-filter-brand']);
    $b1 = Brand::factory()->create(['name' => 'برند الف', 'slug' => 'brand-a']);
    $b2 = Brand::factory()->create(['name' => 'برند ب', 'slug' => 'brand-b']);

    $p1 = Product::factory()->create([
        'category_id' => $cat->id,
        'brand_id' => $b1->id,
        'name' => 'کالای برند الف',
        'slug' => 'prod-brand-a',
        'published_at' => now()->subMinute(),
    ]);
    ProductVariant::factory()->create(['product_id' => $p1->id, 'sku' => 'SKU-A', 'price' => 1000, 'stock' => 5]);

    $p2 = Product::factory()->create([
        'category_id' => $cat->id,
        'brand_id' => $b2->id,
        'name' => 'کالای برند ب',
        'slug' => 'prod-brand-b',
        'published_at' => now()->subMinute(),
    ]);
    ProductVariant::factory()->create(['product_id' => $p2->id, 'sku' => 'SKU-B', 'price' => 1000, 'stock' => 5]);

    $response = $this->getJson(route('products.index', ['brand' => 'brand-a']));

    $response->assertOk();
    $data = $response->json('data');
    expect(collect($data)->pluck('slug'))->toContain('prod-brand-a')
        ->and(collect($data)->pluck('slug'))->not->toContain('prod-brand-b');
});

it('searches products with pg_trgm fuzzy matching', function (): void {
    $cat = Category::factory()->create(['name' => 'آرایشی', 'slug' => 'search-cat']);

    $p1 = Product::factory()->create([
        'category_id' => $cat->id,
        'name' => 'کرم‌پودر اینفالیبل لورآل',
        'slug' => 'loreal-infallible-search',
        'published_at' => now()->subMinute(),
    ]);
    ProductVariant::factory()->create(['product_id' => $p1->id, 'sku' => 'SKU-SEARCH-1', 'price' => 5000, 'stock' => 5]);

    $p2 = Product::factory()->create([
        'category_id' => $cat->id,
        'name' => 'ریمل حجم دهنده چشم',
        'slug' => 'mascara-search',
        'published_at' => now()->subMinute(),
    ]);
    ProductVariant::factory()->create(['product_id' => $p2->id, 'sku' => 'SKU-SEARCH-2', 'price' => 5000, 'stock' => 5]);

    // Persian query with slight typo: "کرم پودر اینفلیبل"
    $response = $this->getJson(route('products.index', ['search' => 'اینفالیبل']));

    $response->assertOk();
    $data = $response->json('data');
    expect(collect($data)->pluck('slug'))->toContain('loreal-infallible-search')
        ->and(collect($data)->pluck('slug'))->not->toContain('mascara-search');
});

it('returns product detail with available variants and scoped matrix', function (): void {
    $cat = Category::factory()->create(['name' => 'رژلب‌ها', 'slug' => 'lipsticks-detail']);

    $colorAttr = Attribute::factory()->color()->create([
        'name' => 'رنگ',
        'slug' => 'lipstick-color',
        'order' => 1,
    ]);

    $valRed = AttributeValue::factory()->color('#FF0000', 'قرمز آلبالویی')->create(['attribute_id' => $colorAttr->id]);
    $valNude = AttributeValue::factory()->color('#DEB887', 'کالباسی')->create(['attribute_id' => $colorAttr->id]);

    $cat->attributes()->attach($colorAttr->id, ['is_variant_maker' => true, 'is_filterable' => true, 'order' => 1]);

    $product = Product::factory()->create([
        'category_id' => $cat->id,
        'name' => 'رژلب مات هیدراته',
        'slug' => 'matte-lipstick-hydrate',
        'published_at' => now()->subMinute(),
    ]);

    $v1 = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'sku' => 'LIP-RED-01',
        'price' => 3500000,
        'compare_at_price' => 4200000,
        'stock' => 15,
    ]);
    $v1->attributeValues()->attach($valRed->id);

    $v2 = ProductVariant::factory()->outOfStock()->create([
        'product_id' => $product->id,
        'sku' => 'LIP-NUD-01',
        'price' => 3500000,
    ]);
    $v2->attributeValues()->attach($valNude->id);

    $response = $this->getJson(route('products.show', ['product' => 'matte-lipstick-hydrate']));

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'data' => [
                'name' => 'رژلب مات هیدراته',
                'slug' => 'matte-lipstick-hydrate',
            ],
        ])
        ->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'slug',
                'breadcrumbs',
                'variants',
                'variants_matrix',
            ],
        ]);

    $matrix = $response->json('data.variants_matrix');
    expect($matrix)->toHaveCount(1)
        ->and($matrix[0]['attribute']['name'])->toBe('رنگ')
        ->and($matrix[0]['values'])->toHaveCount(2);

    $redValue = collect($matrix[0]['values'])->firstWhere('value', 'قرمز آلبالویی');
    $nudeValue = collect($matrix[0]['values'])->firstWhere('value', 'کالباسی');

    expect($redValue['available'])->toBeTrue()
        ->and($nudeValue['available'])->toBeFalse();
});

it('excludes inactive products from public listing', function (): void {
    $cat = Category::factory()->create(['name' => 'تستی', 'slug' => 'inactive-test-cat']);

    $active = Product::factory()->create([
        'category_id' => $cat->id,
        'name' => 'کالای فعال',
        'slug' => 'active-product',
        'published_at' => now()->subMinute(),
    ]);
    ProductVariant::factory()->create(['product_id' => $active->id, 'sku' => 'SKU-ACT', 'price' => 1000, 'stock' => 5]);

    $inactive = Product::factory()->inactive()->create([
        'category_id' => $cat->id,
        'name' => 'کالای غیرفعال',
        'slug' => 'inactive-product',
        'published_at' => now()->subMinute(),
    ]);
    ProductVariant::factory()->create(['product_id' => $inactive->id, 'sku' => 'SKU-INACT', 'price' => 1000, 'stock' => 5]);

    $response = $this->getJson(route('products.index'));

    $response->assertOk();
    $data = $response->json('data');

    expect(collect($data)->pluck('slug'))->toContain('active-product')
        ->and(collect($data)->pluck('slug'))->not->toContain('inactive-product');
});
