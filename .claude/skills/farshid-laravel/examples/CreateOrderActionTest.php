<?php

declare(strict_types=1);

use App\Actions\Orders\CreateOrderAction;
use App\Data\Orders\CreateOrderData;
use App\Enums\OrderStatusEnum;
use App\Events\OrderCreatedEvent;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

it('creates an order with items and calculates correct subtotal', function () {
    // Arrange
    Event::fake([OrderCreatedEvent::class]);

    $user = User::factory()->create();
    $productA = Product::factory()->create(['price' => 1500]);
    $productB = Product::factory()->create(['price' => 2500]);

    $data = new CreateOrderData(
        userId: $user->id,
        items: [
            ['product_id' => $productA->id, 'quantity' => 2, 'unit_price' => 1500],
            ['product_id' => $productB->id, 'quantity' => 1, 'unit_price' => 2500],
        ],
        paymentMethod: 'credit_card',
        couponCode: 'WELCOME10',
        notes: 'Please leave at door.',
    );

    $action = app(CreateOrderAction::class);

    // Act
    $order = $action->execute($user, $data);

    // Assert
    expect($order)
        ->toBeInstanceOf(Order::class)
        ->user_id->toBe($user->id)
        ->status->toBe(OrderStatusEnum::PENDING)
        ->total_amount->toBe(5500); // (2 * 1500) + (1 * 2500)

    $this->assertDatabaseHas('orders', [
        'id' => $order->id,
        'user_id' => $user->id,
        'status' => OrderStatusEnum::PENDING->value,
        'total_amount' => 5500,
    ]);

    $this->assertDatabaseCount('order_items', 2);

    Event::assertDispatched(OrderCreatedEvent::class, function (OrderCreatedEvent $event) use ($order) {
        return $event->order->id === $order->id;
    });
});
