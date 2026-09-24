<?php

declare(strict_types=1);

use App\Enums\OrderStatus;
use App\Enums\PaymentGateway;
use App\Enums\PaymentStatus;
use App\Enums\ShippingMethod;
use App\Models\Brand;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

beforeEach(function (): void {
    $this->user = User::factory()->create([
        'mobile' => '09129998877',
        'is_active' => true,
    ]);

    $category = Category::factory()->create([
        'name' => 'زیبایی',
        'slug' => 'beauty-'.uniqid(),
    ]);

    $brand = Brand::factory()->create([
        'name' => 'برند تایید',
        'slug' => 'brand-v-'.uniqid(),
    ]);

    $product = Product::factory()->create([
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'name' => 'محصول پرداخت تستی',
        'slug' => 'prod-v-'.uniqid(),
    ]);

    $this->variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'sku' => 'SKU-V-'.strtoupper(uniqid()),
        'price' => 2000000,
        'stock' => 10,
    ]);

    $this->order = Order::factory()->create([
        'user_id' => $this->user->id,
        'status' => OrderStatus::PendingPayment,
        'shipping_method' => ShippingMethod::Pishtaz,
        'items_subtotal' => 2000000,
        'final_payable' => 2650000,
    ]);

    $this->orderItem = OrderItem::factory()->create([
        'order_id' => $this->order->id,
        'product_id' => $product->id,
        'product_variant_id' => $this->variant->id,
        'product_name' => $product->name,
        'sku' => $this->variant->sku,
        'unit_price' => 2000000,
        'final_price' => 2000000,
        'quantity' => 1,
        'total_price' => 2000000,
    ]);

    $this->authority = 'SB-'.uniqid();

    $this->payment = Payment::factory()->create([
        'order_id' => $this->order->id,
        'user_id' => $this->user->id,
        'gateway' => PaymentGateway::Sandbox,
        'status' => PaymentStatus::Pending,
        'amount' => 2650000,
        'authority' => $this->authority,
    ]);
});

it('verifies sandbox payment successfully, executes Tier 2 stock decrement and updates order to processing', function (): void {
    // Populate user cart
    $cart = Cart::factory()->create(['user_id' => $this->user->id]);
    $cart->items()->create([
        'product_variant_id' => $this->variant->id,
        'quantity' => 1,
    ]);

    $initialStock = $this->variant->stock;

    $response = $this->postJson(route('payment.verify'), [
        'Authority' => $this->authority,
        'Status' => 'OK',
    ]);

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonStructure([
            'data' => [
                'order_number',
                'tracking_code',
                'reference_id',
            ],
        ]);

    // Verify Payment updated
    expect($this->payment->fresh()->status)->toBe(PaymentStatus::Success);
    expect($this->payment->fresh()->reference_id)->not->toBeNull();

    // Verify Order updated
    expect($this->order->fresh()->status)->toBe(OrderStatus::Processing);
    expect($this->order->fresh()->paid_at)->not->toBeNull();

    // Verify Tier 2 stock decrement
    expect($this->variant->fresh()->stock)->toBe($initialStock - 1);

    // Verify Cart cleared
    expect($cart->fresh()->items)->toBeEmpty();
});

it('handles failed payment callback gracefully without altering stock', function (): void {
    $initialStock = $this->variant->stock;

    $response = $this->postJson(route('payment.verify'), [
        'Authority' => $this->authority,
        'Status' => 'NOK',
    ]);

    $response->assertStatus(400)
        ->assertJsonPath('success', false);

    expect($this->payment->fresh()->status)->toBe(PaymentStatus::Failed);
    expect($this->order->fresh()->status)->toBe(OrderStatus::PendingPayment);
    expect($this->variant->fresh()->stock)->toBe($initialStock);
});
