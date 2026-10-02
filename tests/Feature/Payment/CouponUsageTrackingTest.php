<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Reyhan\Core\Enums\CouponType;
use Reyhan\Core\Enums\OrderStatus;
use Reyhan\Core\Enums\PaymentGateway;
use Reyhan\Core\Enums\PaymentStatus;
use Reyhan\Core\Enums\ShippingMethod;
use Reyhan\Core\Models\Brand;
use Reyhan\Core\Models\Category;
use Reyhan\Core\Models\Coupon;
use Reyhan\Core\Models\CouponUsage;
use Reyhan\Core\Models\Order;
use Reyhan\Core\Models\OrderItem;
use Reyhan\Core\Models\Payment;
use Reyhan\Core\Models\Product;
use Reyhan\Core\Models\ProductVariant;
use Reyhan\Core\Models\User;

uses(DatabaseTransactions::class);

beforeEach(function (): void {
    $this->user = User::factory()->create([
        'mobile' => '09121112233',
        'is_active' => true,
    ]);

    $category = Category::factory()->create();
    $brand = Brand::factory()->create();

    $product = Product::factory()->create([
        'category_id' => $category->id,
        'brand_id' => $brand->id,
    ]);

    $this->variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'price' => 1000000,
        'stock' => 10,
    ]);

    $this->coupon = Coupon::factory()->create([
        'code' => 'TESTCOUPON',
        'type' => CouponType::Fixed,
        'value' => 200000,
        'usage_limit' => 1,
        'usage_limit_per_user' => 1,
        'used_count' => 0,
        'is_active' => true,
    ]);

    $this->order = Order::factory()->create([
        'user_id' => $this->user->id,
        'status' => OrderStatus::PendingPayment,
        'shipping_method' => ShippingMethod::Pishtaz,
        'items_subtotal' => 1000000,
        'coupon_discount' => 200000,
        'coupon_code' => 'TESTCOUPON',
        'final_payable' => 850000,
        'reservation_id' => 'res_coupon_test_123',
    ]);

    OrderItem::factory()->create([
        'order_id' => $this->order->id,
        'product_id' => $product->id,
        'product_variant_id' => $this->variant->id,
        'product_name' => $product->name,
        'unit_price' => 1000000,
        'final_price' => 1000000,
        'quantity' => 1,
        'total_price' => 1000000,
    ]);

    $this->authority = 'SB-COUPON-'.uniqid();

    $this->payment = Payment::factory()->create([
        'order_id' => $this->order->id,
        'user_id' => $this->user->id,
        'gateway' => PaymentGateway::Sandbox,
        'status' => PaymentStatus::Pending,
        'amount' => 850000,
        'authority' => $this->authority,
    ]);
});

it('records CouponUsage and increments used_count on successful payment verification', function (): void {
    expect($this->coupon->fresh()->used_count)->toBe(0);
    expect(CouponUsage::where('order_id', $this->order->id)->count())->toBe(0);

    $response = $this->postJson(route('payment.verify'), [
        'Authority' => $this->authority,
        'Status' => 'OK',
    ]);

    $response->assertOk()
        ->assertJsonPath('success', true);

    // Assert Coupon usage count increased
    expect($this->coupon->fresh()->used_count)->toBe(1);

    // Assert CouponUsage record was created
    $usage = CouponUsage::where('order_id', $this->order->id)->first();
    expect($usage)->not->toBeNull();
    expect($usage->coupon_id)->toBe($this->coupon->id);
    expect($usage->user_id)->toBe($this->user->id);
    expect($usage->discount_amount)->toBe(200000);

    // Assert coupon is now invalid due to usage_limit = 1
    expect($this->coupon->fresh()->isValidFor(1000000, $this->user))->toBeFalse();
});
