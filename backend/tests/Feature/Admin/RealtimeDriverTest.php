<?php

declare(strict_types=1);

use Reyhan\Core\Enums\OrderStatus;
use Reyhan\Core\Enums\ShippingMethod;
use Reyhan\Core\Filament\Widgets\LatestOrdersWidget;
use Reyhan\Core\Models\Admin;
use Reyhan\Core\Models\Order;
use Reyhan\Core\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Marcusvbda\FilamentRealtimeDriver\FilamentRealtimeDriverPlugin;
use Marcusvbda\FilamentRealtimeDriver\RealtimeEvent;
use Marcusvbda\FilamentRealtimeDriver\Tables\TableSocketRegistry;
use Spatie\Permission\Models\Role;

uses(DatabaseTransactions::class);

beforeEach(function (): void {
    $role = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'admin']);
    $this->admin = Admin::factory()->create();
    $this->admin->assignRole($role);
});

test('filament realtime driver plugin is registered in admin panel', function (): void {
    $panel = Filament::getPanel('admin');

    expect($panel->hasPlugin('filament-realtime-driver'))->toBeTrue();

    $plugin = $panel->getPlugin('filament-realtime-driver');
    expect($plugin)->toBeInstanceOf(FilamentRealtimeDriverPlugin::class);
});

test('order model dispatches realtime event upon creation', function (): void {
    Event::fake([RealtimeEvent::class]);

    $user = User::factory()->create();

    $order = Order::factory()->create([
        'order_number' => Order::generateOrderNumber(),
        'user_id' => $user->id,
        'status' => OrderStatus::PendingPayment,
        'shipping_method' => ShippingMethod::Express,
        'shipping_address' => ['city' => 'تهران'],
        'items_subtotal' => 100000,
        'discount_amount' => 0,
        'coupon_discount' => 0,
        'shipping_fee' => 25000,
        'final_payable' => 125000,
    ]);

    Event::assertDispatched(RealtimeEvent::class, function (RealtimeEvent $event) use ($order): bool {
        return $event->channel === 'orders'
            && $event->event === 'OrderCreated'
            && $event->data['id'] === $order->id;
    });
});

test('order model dispatches realtime event upon status update', function (): void {
    $user = User::factory()->create();

    $order = Order::factory()->create([
        'order_number' => Order::generateOrderNumber(),
        'user_id' => $user->id,
        'status' => OrderStatus::PendingPayment,
        'shipping_method' => ShippingMethod::Express,
        'shipping_address' => ['city' => 'تهران'],
        'items_subtotal' => 100000,
        'discount_amount' => 0,
        'coupon_discount' => 0,
        'shipping_fee' => 25000,
        'final_payable' => 125000,
    ]);

    Event::fake([RealtimeEvent::class]);

    $order->update(['status' => OrderStatus::Processing]);

    Event::assertDispatched(RealtimeEvent::class, function (RealtimeEvent $event): bool {
        return $event->channel === 'orders'
            && $event->event === 'OrderUpdated'
            && $event->data['status'] === OrderStatus::Processing->value;
    });
});

use Reyhan\Core\Filament\Resources\Orders\Pages\ListOrders;
use Livewire\Livewire;

test('order resource table has socket configured for orders channel', function (): void {
    $component = Livewire::actingAs($this->admin, 'admin')->test(ListOrders::class);

    $socket = TableSocketRegistry::get($component->instance()->getTable());
    expect($socket)->not->toBeNull()
        ->and($socket['channel'])->toBe('orders')
        ->and($socket['event'])->toBe('OrderUpdated');
});

test('latest orders widget table has socket configured for orders channel', function (): void {
    $component = Livewire::actingAs($this->admin, 'admin')->test(LatestOrdersWidget::class);

    $socket = TableSocketRegistry::get($component->instance()->getTable());
    expect($socket)->not->toBeNull()
        ->and($socket['channel'])->toBe('orders')
        ->and($socket['event'])->toBe('OrderUpdated');
});
