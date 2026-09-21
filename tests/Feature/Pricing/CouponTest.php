<?php

declare(strict_types=1);

use App\Enums\CouponScope;
use App\Enums\CouponType;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;

uses(DatabaseTransactions::class);

beforeEach(function (): void {
    $this->categoryA = Category::create([
        'name' => 'پوست',
        'slug' => 'skin-'.Str::random(6),
        'is_active' => true,
    ]);

    $this->categoryB = Category::create([
        'name' => 'مو',
        'slug' => 'hair-'.Str::random(6),
        'is_active' => true,
    ]);

    $this->brand = Brand::create([
        'name' => 'سینره',
        'slug' => 'cinere-'.Str::random(6),
        'is_active' => true,
    ]);

    $this->productA = Product::create([
        'category_id' => $this->categoryA->id,
        'brand_id' => $this->brand->id,
        'name' => 'کرم ضد آفتاب سینره',
        'slug' => 'sunscreen-'.Str::random(6),
        'is_active' => true,
        'published_at' => now()->subMinute(),
    ]);

    $this->variantA = ProductVariant::create([
        'product_id' => $this->productA->id,
        'sku' => 'CIN-SUN-50',
        'price' => 20000000, // 2,000,000 Toman
        'stock' => 10,
        'is_active' => true,
    ]);

    $this->productB = Product::create([
        'category_id' => $this->categoryB->id,
        'brand_id' => $this->brand->id,
        'name' => 'شامپو تقویتی سینره',
        'slug' => 'shampoo-'.Str::random(6),
        'is_active' => true,
        'published_at' => now()->subMinute(),
    ]);

    $this->variantB = ProductVariant::create([
        'product_id' => $this->productB->id,
        'sku' => 'CIN-SHAM-250',
        'price' => 10000000, // 1,000,000 Toman
        'stock' => 10,
        'is_active' => true,
    ]);
});

it('applies percentage coupon with max discount cap', function (): void {
    $coupon = Coupon::create([
        'code' => 'DISCOUNT20',
        'title' => '۲۰ درصد تخفیف',
        'type' => CouponType::Percentage,
        'value' => 20,
        'scope' => CouponScope::All,
        'max_discount_amount' => 3000000, // Max 300,000 Toman discount
        'is_active' => true,
    ]);

    $sessionId = (string) Str::uuid();

    // Add variantA: 20,000,000 Rial. 20% would be 4,000,000 Rial, but capped at 3,000,000
    $this->withHeader('X-Cart-Session', $sessionId)
        ->postJson('/api/v1/cart/items', [
            'variant_id' => $this->variantA->id,
            'quantity' => 1,
        ]);

    $response = $this->withHeader('X-Cart-Session', $sessionId)
        ->postJson('/api/v1/cart/coupon', [
            'code' => 'discount20',
        ]);

    $response->assertOk()
        ->assertJsonPath('data.pricing.coupon_discount', 3000000)
        ->assertJsonPath('data.pricing.applied_coupon.code', 'DISCOUNT20');
});

it('rejects coupon when minimum order amount is not met', function (): void {
    Coupon::create([
        'code' => 'MIN50M',
        'title' => 'تخفیف خریدهای بالای ۵ میلیون تومان',
        'type' => CouponType::Fixed,
        'value' => 5000000,
        'scope' => CouponScope::All,
        'min_order_amount' => 50000000, // 5,000,000 Toman min order
        'is_active' => true,
    ]);

    $sessionId = (string) Str::uuid();

    // Subtotal is 20,000,000 (below 50,000,000)
    $this->withHeader('X-Cart-Session', $sessionId)
        ->postJson('/api/v1/cart/items', [
            'variant_id' => $this->variantA->id,
            'quantity' => 1,
        ]);

    $response = $this->withHeader('X-Cart-Session', $sessionId)
        ->postJson('/api/v1/cart/coupon', [
            'code' => 'MIN50M',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['code']);
});

it('applies category-scoped coupon only to items in that category', function (): void {
    $coupon = Coupon::create([
        'code' => 'SKINONLY',
        'title' => 'فقط محصولات پوست',
        'type' => CouponType::Percentage,
        'value' => 10,
        'scope' => CouponScope::Categories,
        'is_active' => true,
    ]);
    $coupon->categories()->attach($this->categoryA->id);

    $sessionId = (string) Str::uuid();

    // Add variantA (skin, 20M) and variantB (hair, 10M). Total subtotal = 30M.
    // Coupon is 10% on skin only = 10% of 20M = 2M.
    $this->withHeader('X-Cart-Session', $sessionId)
        ->postJson('/api/v1/cart/items', ['variant_id' => $this->variantA->id, 'quantity' => 1]);
    $this->withHeader('X-Cart-Session', $sessionId)
        ->postJson('/api/v1/cart/items', ['variant_id' => $this->variantB->id, 'quantity' => 1]);

    $response = $this->withHeader('X-Cart-Session', $sessionId)
        ->postJson('/api/v1/cart/coupon', ['code' => 'SKINONLY']);

    $response->assertOk()
        ->assertJsonPath('data.pricing.items_subtotal', 30000000)
        ->assertJsonPath('data.pricing.coupon_discount', 2000000);
});

it('can remove an applied coupon', function (): void {
    Coupon::create([
        'code' => 'REMOVEME',
        'type' => CouponType::Fixed,
        'value' => 1000000,
        'scope' => CouponScope::All,
        'is_active' => true,
    ]);

    $sessionId = (string) Str::uuid();

    $this->withHeader('X-Cart-Session', $sessionId)
        ->postJson('/api/v1/cart/items', ['variant_id' => $this->variantA->id, 'quantity' => 1]);

    $this->withHeader('X-Cart-Session', $sessionId)
        ->postJson('/api/v1/cart/coupon', ['code' => 'REMOVEME'])
        ->assertOk();

    $response = $this->withHeader('X-Cart-Session', $sessionId)
        ->deleteJson('/api/v1/cart/coupon');

    $response->assertOk()
        ->assertJsonPath('data.pricing.coupon_discount', 0)
        ->assertJsonPath('data.pricing.applied_coupon', null);
});
