<?php

declare(strict_types=1);

use Reyhan\Core\Actions\Payment\VerifyPaymentAction;
use Reyhan\Core\Console\Commands\RecoverAbandonedCartsCommand;
use Reyhan\Core\Enums\PaymentGateway;
use Reyhan\Core\Enums\PaymentStatus;
use Reyhan\Core\Events\Catalog\ProductRestockedEvent;
use Reyhan\Core\Listeners\Catalog\SendProductRestockAlertsListener;
use Reyhan\Core\Models\Cart;
use Reyhan\Core\Models\CartItem;
use Reyhan\Core\Models\Order;
use Reyhan\Core\Models\Payment;
use Reyhan\Core\Models\Product;
use Reyhan\Core\Models\ProductVariant;
use Reyhan\Core\Models\StockAlert;
use Reyhan\Core\Models\User;
use Reyhan\Core\Notifications\Catalog\StockAlertNotification;
use Reyhan\Core\Notifications\Channels\SmsChannel;
use Reyhan\Core\Notifications\Marketing\AbandonedCartReminderNotification;
use Reyhan\Core\Notifications\Orders\OrderPaidNotification;
use Reyhan\Core\Notifications\Orders\OrderShippedNotification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;

uses(DatabaseTransactions::class);

test('user model routes notification for sms using mobile attribute', function () {
    $user = User::factory()->create(['mobile' => '09123456789']);

    expect($user->routeNotificationFor('sms'))->toBe('09123456789')
        ->and($user->routeNotificationForSms())->toBe('09123456789');
});

test('order paid notification builds formatted sms message with order number and tracking code', function () {
    $user = User::factory()->create();
    $order = Order::factory()->for($user)->create([
        'order_number' => 'ORD-100200',
        'tracking_code' => 'TRK-998877',
    ]);

    $notification = new OrderPaidNotification($order);

    expect($notification->via($user))->toBe([SmsChannel::class]);

    $message = $notification->toSms($user);
    expect($message->content)->toContain('ORD-100200')
        ->and($message->content)->toContain('TRK-998877');
});

test('order shipped notification builds formatted sms message with tracking url', function () {
    $user = User::factory()->create();
    $order = Order::factory()->for($user)->create([
        'order_number' => 'ORD-554433',
        'tracking_code' => 'POST123456789',
        'tracking_url' => 'https://tracking.post.ir/?id=POST123456789',
    ]);

    $notification = new OrderShippedNotification($order);

    expect($notification->via($user))->toBe([SmsChannel::class]);

    $message = $notification->toSms($user);
    expect($message->content)->toContain('ORD-554433')
        ->and($message->content)->toContain('POST123456789')
        ->and($message->content)->toContain('https://tracking.post.ir/?id=POST123456789');
});

test('abandoned cart reminder notification builds formatted sms message with user name and url', function () {
    $user = User::factory()->create([
        'first_name' => 'سارا',
        'last_name' => 'محمدی',
    ]);
    $cart = Cart::factory()->for($user)->create();

    $notification = new AbandonedCartReminderNotification($cart);

    expect($notification->via($user))->toBe([SmsChannel::class]);

    $message = $notification->toSms($user);
    expect($message->content)->toContain('سارا محمدی')
        ->and($message->content)->toContain('/cart');
});

test('stock alert notification builds formatted sms message with product and variant title', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['name' => 'کرم پودر شیسیدو']);
    $variant = ProductVariant::factory()->for($product)->create();
    $alert = StockAlert::factory()->create([
        'user_id' => $user->id,
        'product_variant_id' => $variant->id,
        'mobile' => '09351112233',
        'status' => 'pending',
    ]);

    $notification = new StockAlertNotification($alert);

    expect($notification->via($user))->toBe([SmsChannel::class]);

    $message = $notification->toSms($user);
    expect($message->content)->toContain('کرم پودر شیسیدو');
});

test('verify payment action dispatches order paid notification upon success', function () {
    Notification::fake();

    $user = User::factory()->create(['mobile' => '09121112233']);
    $order = Order::factory()->for($user)->create([
        'order_number' => 'ORD-NOTIF-1',
    ]);
    Payment::factory()->create([
        'order_id' => $order->id,
        'user_id' => $user->id,
        'gateway' => PaymentGateway::Sandbox,
        'status' => PaymentStatus::Pending,
        'authority' => 'AUTH_NOTIF_TEST',
        'amount' => 500000,
    ]);

    /** @var VerifyPaymentAction $action */
    $action = app(VerifyPaymentAction::class);
    $result = $action->execute('AUTH_NOTIF_TEST', ['Status' => 'OK']);

    expect($result->success)->toBeTrue();

    Notification::assertSentTo(
        $user,
        OrderPaidNotification::class,
        function (OrderPaidNotification $notification) use ($order) {
            return $notification->order->id === $order->id;
        }
    );
});

test('recover abandoned carts command dispatches abandoned cart notification to inactive users', function () {
    Notification::fake();

    $user = User::factory()->create(['mobile' => '09129998877']);
    $cart = Cart::factory()->for($user)->create([
        'updated_at' => now()->subHours(3),
    ]);
    $variant = ProductVariant::factory()->create();
    CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_variant_id' => $variant->id,
        'quantity' => 1,
    ]);

    $this->artisan(RecoverAbandonedCartsCommand::class, ['--hours' => 2])
        ->assertSuccessful();

    Notification::assertSentTo(
        $user,
        AbandonedCartReminderNotification::class,
        function (AbandonedCartReminderNotification $notification) use ($cart) {
            return $notification->cart->id === $cart->id;
        }
    );
});

test('variant restock dispatches product restocked event and listener notifies subscribers', function () {
    Event::fake([ProductRestockedEvent::class]);

    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->for($product)->create(['stock' => 0]);

    $variant->update(['stock' => 10]);

    Event::assertDispatched(
        ProductRestockedEvent::class,
        function (ProductRestockedEvent $event) use ($variant) {
            return $event->variant->id === $variant->id
                && $event->oldStock === 0
                && $event->newStock === 10;
        }
    );
});

test('send product restock alerts listener notifies subscribers and marks alerts sent', function () {
    Notification::fake();

    $user = User::factory()->create(['mobile' => '09127776655']);
    $product = Product::factory()->create(['name' => 'سرم ویتامین سی']);
    $variant = ProductVariant::factory()->for($product)->create(['stock' => 5]);
    $alert = StockAlert::factory()->create([
        'user_id' => $user->id,
        'product_variant_id' => $variant->id,
        'mobile' => $user->mobile,
        'status' => 'pending',
    ]);

    $event = new ProductRestockedEvent($variant, 0, 5);
    $listener = new SendProductRestockAlertsListener;
    $listener->handle($event);

    Notification::assertSentTo(
        $user,
        StockAlertNotification::class,
        function (StockAlertNotification $notification) use ($alert) {
            return $notification->stockAlert->id === $alert->id;
        }
    );

    expect($alert->fresh()->status)->toBe('sent');
});
