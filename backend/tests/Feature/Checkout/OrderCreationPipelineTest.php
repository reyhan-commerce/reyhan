<?php

declare(strict_types=1);

namespace Tests\Feature\Checkout;

use Reyhan\Core\Actions\Checkout\CreateOrderAction;
use Reyhan\Core\Data\Checkout\CreateOrderData;
use Reyhan\Core\Enums\PaymentGateway;
use Reyhan\Core\Events\Orders\OrderCreated;
use Reyhan\Core\Events\Orders\OrderPaid;
use Reyhan\Core\Models\Address;
use Reyhan\Core\Models\Cart;
use Reyhan\Core\Models\CartItem;
use Reyhan\Core\Models\City;
use Reyhan\Core\Models\Product;
use Reyhan\Core\Models\ProductVariant;
use Reyhan\Core\Models\Province;
use Reyhan\Core\Models\User;
use Reyhan\Core\Pipelines\Checkout\OrderCreationContext;
use Reyhan\Core\Pipelines\Checkout\OrderCreationPipeline;
use Closure;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;

uses(DatabaseTransactions::class);

class TestCustomAuditPipe
{
    public static bool $called = false;

    public function handle(OrderCreationContext $context, Closure $next): mixed
    {
        self::$called = true;
        $context->customData['interception_tag'] = 'audit_passed';

        return $next($context);
    }
}

beforeEach(function () {
    $this->user = User::factory()->create(['mobile' => '09123456789']);
    $this->province = Province::create(['name' => 'تهران', 'slug' => 'tehran-'.uniqid()]);
    $this->city = City::create(['province_id' => $this->province->id, 'name' => 'تهران', 'slug' => 'tehran-'.uniqid()]);

    $this->address = Address::create([
        'user_id' => $this->user->id,
        'title' => 'Home',
        'recipient_name' => 'Farshid',
        'recipient_mobile' => '09123456789',
        'province_id' => $this->province->id,
        'city_id' => $this->city->id,
        'postal_code' => '1234567890',
        'address_line' => 'Valiasr St',
        'is_default' => true,
    ]);

    $this->product = Product::factory()->create([
        'name' => 'تست پایپ‌لاین ریحان',
        'is_active' => true,
    ]);

    $this->variant = ProductVariant::create([
        'product_id' => $this->product->id,
        'sku' => 'PIPE-VAR-'.uniqid(),
        'title' => 'قرمز',
        'price' => 2000000,
        'stock' => 10,
        'is_active' => true,
    ]);

    $this->cart = Cart::create(['user_id' => $this->user->id]);
    CartItem::create([
        'cart_id' => $this->cart->id,
        'product_variant_id' => $this->variant->id,
        'quantity' => 2,
    ]);
});

test('order creation pipeline executes and dispatches OrderCreated event', function () {
    Event::fake([OrderCreated::class]);

    $action = app(CreateOrderAction::class);
    $data = new CreateOrderData(
        addressId: $this->address->id,
        gateway: PaymentGateway::Sandbox,
        shippingMethod: 'pishtaz',
        callbackUrl: 'https://myshop.ir/checkout/callback',
    );

    $result = $action->execute($this->user, $data);

    expect($result->order)->not->toBeNull()
        ->and($result->order->order_number)->toBeString()
        ->and($result->payment)->not->toBeNull()
        ->and($result->payment->authority)->toStartWith('SB-');

    Event::assertDispatched(OrderCreated::class, fn (OrderCreated $e) => $e->order->id === $result->order->id);
});

test('order creation pipeline fires OrderPaid event when 100% covered by wallet', function () {
    Event::fake([OrderCreated::class, OrderPaid::class]);

    $this->user->wallet_balance = 10000000;
    $this->user->save();

    $action = app(CreateOrderAction::class);
    $data = new CreateOrderData(
        addressId: $this->address->id,
        gateway: PaymentGateway::Sandbox,
        shippingMethod: 'pishtaz',
        useWallet: true,
        callbackUrl: 'https://myshop.ir/checkout/callback',
    );

    $result = $action->execute($this->user, $data);

    expect($result->order)->not->toBeNull()
        ->and($result->order->wallet_paid_amount)->toBeGreaterThan(0)
        ->and($result->payment->gateway)->toBe(PaymentGateway::Wallet);

    Event::assertDispatched(OrderCreated::class);
    Event::assertDispatched(OrderPaid::class);
});

test('order creation pipeline allows dynamic custom pipe registration', function () {
    OrderCreationPipeline::appendPipe(TestCustomAuditPipe::class);

    $action = app(CreateOrderAction::class);
    $data = new CreateOrderData(
        addressId: $this->address->id,
        gateway: PaymentGateway::Sandbox,
        shippingMethod: 'pishtaz',
        callbackUrl: 'https://myshop.ir/checkout/callback',
    );

    $result = $action->execute($this->user, $data);

    expect($result->order)->not->toBeNull()
        ->and(TestCustomAuditPipe::$called)->toBeTrue();
});
