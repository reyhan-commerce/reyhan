<?php

declare(strict_types=1);

use App\Enums\OrderStatus;
use App\Enums\ShippingMethod;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\Inventory\StockReservationService;
use App\Services\Wallet\WalletService;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

beforeEach(function (): void {
    $this->user = User::factory()->create([
        'wallet_balance' => 0,
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
        'stock' => 5,
    ]);

    $this->reservationId = 'order_res_expired_'.uniqid();

    // Make an active reservation in Redis
    app(StockReservationService::class)->reserve(
        variantId: $this->variant->id,
        quantity: 2,
        reservationId: $this->reservationId
    );

    // Create an expired pending order with partial wallet deduction
    $this->order = Order::factory()->create([
        'user_id' => $this->user->id,
        'status' => OrderStatus::PendingPayment,
        'shipping_method' => ShippingMethod::Pishtaz,
        'items_subtotal' => 2000000,
        'wallet_paid_amount' => 500000,
        'final_payable' => 2000000,
        'reservation_id' => $this->reservationId,
        'created_at' => now()->subMinutes(45),
    ]);

    OrderItem::factory()->create([
        'order_id' => $this->order->id,
        'product_id' => $product->id,
        'product_variant_id' => $this->variant->id,
        'product_name' => $product->name,
        'unit_price' => 1000000,
        'final_price' => 1000000,
        'quantity' => 2,
        'total_price' => 2000000,
    ]);
});

it('cancels expired pending orders, releases inventory reservations, and refunds wallet balances', function (): void {
    $stockService = app(StockReservationService::class);
    $walletService = app(WalletService::class);

    // Before running command: 2 items reserved in Redis (available = 5 - 2 = 3)
    expect($stockService->getAvailableStock($this->variant))->toBe(3);
    expect($walletService->getBalance($this->user))->toBe(0);

    // Run artisan command with 30-minute threshold
    $this->artisan('orders:cancel-expired --minutes=30')
        ->assertSuccessful();

    // Assert order status updated to cancelled
    expect($this->order->fresh()->status)->toBe(OrderStatus::Cancelled);
    expect($this->order->fresh()->cancelled_at)->not->toBeNull();

    // Assert Redis stock reservation was released (available = 5)
    expect($stockService->getAvailableStock($this->variant))->toBe(5);

    // Assert wallet deduction was refunded back to user
    expect($walletService->getBalance($this->user))->toBe(500000);
});
