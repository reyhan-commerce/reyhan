<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Reyhan\Core\Enums\CouponType;
use Reyhan\Core\Models\Brand;
use Reyhan\Core\Models\Cart;
use Reyhan\Core\Models\Category;
use Reyhan\Core\Models\Coupon;
use Reyhan\Core\Models\Product;
use Reyhan\Core\Models\ProductVariant;
use Reyhan\Core\Models\User;
use Reyhan\Core\Services\Pricing\PricingService;

uses(DatabaseTransactions::class);

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->category = Category::factory()->create();
    $this->brand = Brand::factory()->create();

    // 1. Taxable product (e.g. Electronics)
    $this->taxableProduct = Product::factory()->create([
        'category_id' => $this->category->id,
        'brand_id' => $this->brand->id,
        'is_tax_exempt' => false,
    ]);
    $this->taxableVariant = ProductVariant::factory()->create([
        'product_id' => $this->taxableProduct->id,
        'price' => 1000000, // 100,000 Tomans
        'stock' => 10,
    ]);

    // 2. Tax-exempt product (e.g. Book / Food - Article 9 VAT Law)
    $this->exemptProduct = Product::factory()->create([
        'category_id' => $this->category->id,
        'brand_id' => $this->brand->id,
        'is_tax_exempt' => true,
    ]);
    $this->exemptVariant = ProductVariant::factory()->create([
        'product_id' => $this->exemptProduct->id,
        'price' => 500000, // 50,000 Tomans
        'stock' => 10,
    ]);

    $this->pricingService = app(PricingService::class);
});

it('calculates 10% VAT only on taxable items and exempts Article 9 goods in exclusive mode', function (): void {
    config([
        'reyhan.store.tax_rate_percent' => 10,
        'reyhan.store.tax_mode' => 'exclusive',
    ]);

    $cart = Cart::factory()->create(['user_id' => $this->user->id]);
    $cart->items()->create([
        'product_variant_id' => $this->taxableVariant->id,
        'quantity' => 1, // 1,000,000 Rial taxable
    ]);
    $cart->items()->create([
        'product_variant_id' => $this->exemptVariant->id,
        'quantity' => 1, // 500,000 Rial exempt
    ]);

    $pricing = $this->pricingService->calculateCart($cart);

    expect($pricing->itemsSubtotal)->toBe(1500000);
    // 10% on 1,000,000 taxable = 100,000 Rial
    expect($pricing->taxAmount)->toBe(100000);
    // Final payable = 1,500,000 + 100,000 (tax) + shipping
    expect($pricing->finalPayable)->toBe(1500000 + 100000 + $pricing->shippingFee);
});

it('extracts VAT from taxable items without adding surcharges in inclusive mode', function (): void {
    config([
        'reyhan.store.tax_rate_percent' => 10,
        'reyhan.store.tax_mode' => 'inclusive',
    ]);

    $cart = Cart::factory()->create(['user_id' => $this->user->id]);
    $cart->items()->create([
        'product_variant_id' => $this->taxableVariant->id,
        'quantity' => 1, // 1,000,000 Rial inclusive
    ]);

    $pricing = $this->pricingService->calculateCart($cart);

    expect($pricing->itemsSubtotal)->toBe(1000000);
    // 1,000,000 * (10 / 110) = 90909
    expect($pricing->taxAmount)->toBe(90909);
    // In inclusive mode, final payable does not add tax on top
    expect($pricing->finalPayable)->toBe(1000000 + $pricing->shippingFee);
});

it('correctly proportions tax discount when coupon is applied across mixed carts', function (): void {
    config([
        'reyhan.store.tax_rate_percent' => 10,
        'reyhan.store.tax_mode' => 'exclusive',
    ]);

    $cart = Cart::factory()->create(['user_id' => $this->user->id]);
    $cart->items()->create([
        'product_variant_id' => $this->taxableVariant->id,
        'quantity' => 1, // 1,000,000 Rial
    ]);
    $cart->items()->create([
        'product_variant_id' => $this->exemptVariant->id,
        'quantity' => 1, // 1,000,000 Rial
    ]);

    // Apply 500,000 Rial coupon (25% total cart discount)
    $coupon = Coupon::factory()->create([
        'code' => 'DISCOUNT50',
        'type' => CouponType::Fixed,
        'value' => 500000,
        'is_active' => true,
    ]);
    $cart->update(['coupon_id' => $coupon->id]);

    $pricing = $this->pricingService->calculateCart($cart);

    expect($pricing->itemsSubtotal)->toBe(1500000); // 1,000,000 + 500,000 = 1,500,000
    expect($pricing->couponDiscount)->toBe(500000);

    // Taxable base was 1,000,000 (2/3 of cart). After pro-rata 500,000 coupon (1/3 off), taxable base is ~666,667
    // 10% tax on 666,667 is 66,667
    expect($pricing->taxAmount)->toBe(66667);
});
