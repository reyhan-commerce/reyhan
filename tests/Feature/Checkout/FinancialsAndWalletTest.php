<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Validation\ValidationException;
use Reyhan\Core\Database\Seeders\ShippingMethodSeeder;
use Reyhan\Core\Enums\OrderStatus;
use Reyhan\Core\Enums\PaymentGateway;
use Reyhan\Core\Enums\WalletTransactionType;
use Reyhan\Core\Models\Address;
use Reyhan\Core\Models\Brand;
use Reyhan\Core\Models\Cart;
use Reyhan\Core\Models\CartItem;
use Reyhan\Core\Models\Category;
use Reyhan\Core\Models\City;
use Reyhan\Core\Models\Order;
use Reyhan\Core\Models\Product;
use Reyhan\Core\Models\ProductVariant;
use Reyhan\Core\Models\Province;
use Reyhan\Core\Models\ShippingMethod;
use Reyhan\Core\Models\User;
use Reyhan\Core\Services\Wallet\WalletService;

uses(DatabaseTransactions::class);

beforeEach(function (): void {
    if (ShippingMethod::count() === 0) {
        $this->seed(ShippingMethodSeeder::class);
    }

    $this->province = Province::firstOrCreate(
        ['name' => 'تهران'],
        ['slug' => 'tehran-'.uniqid()]
    );
    $this->city = City::firstOrCreate(
        ['province_id' => $this->province->id, 'name' => 'تهران'],
        ['slug' => 'tehran-city-'.uniqid()]
    );

    $this->user = User::factory()->create([
        'mobile' => '09123456789',
        'is_active' => true,
        'wallet_balance' => 0,
    ]);

    $this->address = Address::factory()->create([
        'user_id' => $this->user->id,
        'province_id' => $this->province->id,
        'city_id' => $this->city->id,
        'recipient_name' => 'فرشید رضایی',
        'recipient_mobile' => '09123456789',
        'postal_code' => '1234567890',
        'address_line' => 'خیابان ونک، برج نگار، طبقه ۱۰',
        'is_default' => true,
    ]);

    $category = Category::factory()->create();
    $brand = Brand::factory()->create();

    $this->product = Product::factory()->create([
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'is_active' => true,
    ]);

    $this->variant = ProductVariant::factory()->create([
        'product_id' => $this->product->id,
        'stock' => 10,
        'price' => 3000000, // 300,000 Toman (3M Rials)
        'is_active' => true,
    ]);
});

test('wallet service deposit, withdraw and refund work with pessimistic locking', function (): void {
    /** @var WalletService $walletService */
    $walletService = app(WalletService::class);

    // Deposit 1,000,000 Rials
    $tx1 = $walletService->deposit($this->user, 1000000, 'شارژ حساب تستی');
    expect($tx1->type)->toBe(WalletTransactionType::Deposit)
        ->and($tx1->amount)->toBe(1000000)
        ->and($tx1->balance_after)->toBe(1000000)
        ->and($walletService->getBalance($this->user))->toBe(1000000);

    // Withdraw 400,000 Rials
    $tx2 = $walletService->withdraw($this->user, 400000, 'خرید تستی');
    expect($tx2->type)->toBe(WalletTransactionType::Withdraw)
        ->and($tx2->amount)->toBe(400000)
        ->and($tx2->balance_after)->toBe(600000)
        ->and($walletService->getBalance($this->user))->toBe(600000);

    // Refund with an Order
    $order = Order::factory()->create([
        'user_id' => $this->user->id,
        'final_payable' => 200000,
    ]);
    $tx3 = $walletService->refund($order, 200000, 'مرجوعی کالا');
    expect($tx3->type)->toBe(WalletTransactionType::Refund)
        ->and($tx3->balance_after)->toBe(800000)
        ->and($walletService->getBalance($this->user))->toBe(800000);

    // Over-withdrawing throws ValidationException
    expect(fn () => $walletService->withdraw($this->user, 900000, 'برداشت مازاد'))
        ->toThrow(ValidationException::class);
});

test('api can fetch user wallet and top up', function (): void {
    /** @var WalletService $walletService */
    $walletService = app(WalletService::class);
    $walletService->deposit($this->user, 500000, 'شارژ اولیه');

    $response = $this->actingAs($this->user)
        ->getJson('/api/v1/wallet');

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.balance', 500000)
        ->assertJsonCount(1, 'data.transactions.data');

    $topUpResponse = $this->actingAs($this->user)
        ->postJson('/api/v1/wallet/top-up', [
            'amount' => 1000000,
            'callback_url' => 'http://localhost:3000/profile/wallet',
        ]);

    $topUpResponse->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonStructure(['data' => ['redirect_url']]);
});

test('checkout fully covered by wallet marks order as processing immediately without gateway redirect', function (): void {
    /** @var WalletService $walletService */
    $walletService = app(WalletService::class);
    // Deposit 10,000,000 Rials (plenty to cover product 3M + shipping fee)
    $walletService->deposit($this->user, 10000000, 'شارژ کیف پول برای تست');

    $cart = Cart::factory()->create(['user_id' => $this->user->id]);
    CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_variant_id' => $this->variant->id,
        'quantity' => 1,
    ]);

    $shippingMethod = ShippingMethod::where('slug', 'pishtaz')->first() ?? ShippingMethod::first();

    $response = $this->actingAs($this->user)
        ->postJson('/api/v1/checkout/create-order', [
            'address_id' => $this->address->id,
            'shipping_method_id' => $shippingMethod->id,
            'gateway' => PaymentGateway::Sandbox->value,
            'callback_url' => 'http://localhost:3000/checkout/callback',
            'use_wallet' => true,
        ]);

    $response->assertStatus(201)
        ->assertJsonPath('success', true);

    $redirectUrl = $response->json('data.redirect_url');
    expect($redirectUrl)->toStartWith('http://localhost:3000/checkout/callback')
        ->and($redirectUrl)->toContain('Status=OK');

    $orderNumber = $response->json('data.order_number');
    $this->assertDatabaseHas('orders', [
        'order_number' => $orderNumber,
        'status' => OrderStatus::Processing->value,
    ]);

    $order = Order::where('order_number', $orderNumber)->first();
    expect($order)->not->toBeNull()
        ->and($order->wallet_paid_amount)->toBeGreaterThan(0)
        ->and($order->paid_at)->not->toBeNull();
});

test('checkout creates card to card receipt and keeps order in pending payment', function (): void {
    $cart = Cart::factory()->create(['user_id' => $this->user->id]);
    CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_variant_id' => $this->variant->id,
        'quantity' => 1,
    ]);

    $shippingMethod = ShippingMethod::where('slug', 'pishtaz')->first() ?? ShippingMethod::first();

    $response = $this->actingAs($this->user)
        ->postJson('/api/v1/checkout/create-order', [
            'address_id' => $this->address->id,
            'shipping_method_id' => $shippingMethod->id,
            'gateway' => PaymentGateway::CardToCard->value,
            'callback_url' => 'http://localhost:3000/checkout/callback',
            'card_tracking_number' => 'REF-987654321',
            'card_source_number' => '6037991199999990',
        ]);

    $response->assertStatus(201)
        ->assertJsonPath('success', true);

    $orderNumber = $response->json('data.order_number');
    $order = Order::where('order_number', $orderNumber)->first();
    expect($order)->not->toBeNull()
        ->and($order->status)->toBe(OrderStatus::PendingPayment);

    $this->assertDatabaseHas('card_transfer_receipts', [
        'order_id' => $order->id,
        'tracking_number' => 'REF-987654321',
        'status' => 'pending',
    ]);
});

test('checkout saves corporate tax invoice data', function (): void {
    $cart = Cart::factory()->create(['user_id' => $this->user->id]);
    CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_variant_id' => $this->variant->id,
        'quantity' => 1,
    ]);

    $shippingMethod = ShippingMethod::where('slug', 'pishtaz')->first() ?? ShippingMethod::first();

    $response = $this->actingAs($this->user)
        ->postJson('/api/v1/checkout/create-order', [
            'address_id' => $this->address->id,
            'shipping_method_id' => $shippingMethod->id,
            'gateway' => PaymentGateway::Sandbox->value,
            'callback_url' => 'http://localhost:3000/checkout/callback',
            'is_corporate_invoice' => true,
            'corporate_data' => [
                'company_name' => 'شرکت توسعه پایدار فناوران',
                'national_id' => '10861676731',
                'economic_code' => '411485297531',
                'registration_number' => '582140',
                'phone' => '02188776655',
            ],
        ]);

    $response->assertStatus(201)
        ->assertJsonPath('success', true);

    $orderNumber = $response->json('data.order_number');
    $order = Order::where('order_number', $orderNumber)->first();
    expect($order)->not->toBeNull()
        ->and($order->is_corporate_invoice)->toBeTrue()
        ->and($order->corporate_data['company_name'])->toBe('شرکت توسعه پایدار فناوران')
        ->and($order->corporate_data['national_id'])->toBe('10861676731');
});
