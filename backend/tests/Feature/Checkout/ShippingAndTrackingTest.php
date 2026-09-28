<?php

declare(strict_types=1);

use App\Enums\OrderStatus;
use App\Enums\PaymentGateway;
use App\Models\Address;
use App\Models\Brand;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\City;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Province;
use App\Models\ShippingMethod;
use App\Models\User;
use Database\Seeders\ShippingMethodSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;

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
    ]);

    $this->address = Address::factory()->create([
        'user_id' => $this->user->id,
        'province_id' => $this->province->id,
        'city_id' => $this->city->id,
        'recipient_name' => 'علی تهرانی',
        'recipient_mobile' => '09123456789',
        'postal_code' => '1234567890',
        'address_line' => 'خیابان آزادی، کوچه شهید فلانی، پلاک ۱۰',
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
        'price' => 1000000, // 100,000 Toman
        'is_active' => true,
    ]);

    $this->cart = Cart::factory()->create(['user_id' => $this->user->id]);
    CartItem::factory()->create([
        'cart_id' => $this->cart->id,
        'product_variant_id' => $this->variant->id,
        'quantity' => 2,
    ]);

    ShippingMethod::where('slug', 'express_courier')->update([
        'supported_provinces' => [$this->province->id],
        'free_shipping_threshold' => 50000000,
    ]);
});

it('lists available shipping methods with fees and time slots for checkout', function (): void {
    $response = $this->actingAs($this->user)
        ->getJson(route('checkout.shipping-methods', ['address_id' => $this->address->id]));

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonStructure([
            'success',
            'data' => [
                'methods' => [
                    '*' => [
                        'id',
                        'name',
                        'slug',
                        'shipping_fee',
                        'is_free',
                        'estimated_delivery_days',
                        'requires_time_slot',
                        'time_slots',
                    ],
                ],
            ],
        ]);

    $methods = $response->json('data.methods');
    expect(count($methods))->toBeGreaterThanOrEqual(2);

    $express = collect($methods)->firstWhere('slug', 'express_courier');
    expect($express)->not->toBeNull();
    expect($express['requires_time_slot'])->toBeTrue();
    expect($express['time_slots'])->not->toBeEmpty();
});

it('previews checkout pricing using selected shipping method', function (): void {
    $expressMethod = ShippingMethod::where('slug', 'express_courier')->firstOrFail();

    $response = $this->actingAs($this->user)
        ->getJson(route('checkout.preview', [
            'address_id' => $this->address->id,
            'shipping_method_id' => $expressMethod->id,
        ]));

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.pricing.shipping_method_id', $expressMethod->id)
        ->assertJsonPath('data.pricing.shipping_fee', $expressMethod->base_cost);
});

it('creates an order with chosen shipping method and delivery time slot', function (): void {
    Http::fake([
        '*' => Http::response([
            'data' => [
                'code' => 100,
                'authority' => 'A00000000000000000000000000123456789',
                'fee' => 0,
            ],
            'errors' => [],
        ], 200),
    ]);

    $expressMethod = ShippingMethod::where('slug', 'express_courier')->firstOrFail();
    $tomorrowDate = now()->addDay()->format('Y-m-d');

    $payload = [
        'address_id' => $this->address->id,
        'shipping_method_id' => $expressMethod->id,
        'delivery_date' => $tomorrowDate,
        'delivery_time_slot' => '16:00 - 20:00 (عصر)',
        'gateway' => PaymentGateway::Sandbox->value,
        'callback_url' => 'http://localhost:3000/checkout/callback',
        'notes' => 'لطفاً قبل از حرکت زنگ بزنید',
    ];

    $response = $this->actingAs($this->user)
        ->postJson(route('checkout.create-order'), $payload);

    $response->assertStatus(201)
        ->assertJsonPath('success', true);

    $order = Order::latest('id')->first();
    expect($order)->not->toBeNull();
    expect($order->shipping_method_id)->toBe($expressMethod->id);
    expect($order->delivery_date->format('Y-m-d'))->toBe($tomorrowDate);
    expect($order->delivery_time_slot)->toBe('16:00 - 20:00 (عصر)');
    expect($order->shipping_fee)->toBe($expressMethod->base_cost);

    // Order detail API includes logistics data
    $detailResponse = $this->actingAs($this->user)
        ->getJson(route('orders.show', ['orderNumber' => $order->order_number]));

    $detailResponse->assertOk()
        ->assertJsonPath('data.shipping_method_id', $expressMethod->id)
        ->assertJsonPath('data.delivery_time_slot', '16:00 - 20:00 (عصر)');
});

it('allows order to have tracking code and tracking url stored upon shipping', function (): void {
    $order = Order::factory()->create([
        'order_number' => Order::generateOrderNumber(),
        'user_id' => $this->user->id,
        'status' => OrderStatus::Processing,
        'shipping_method' => 'pishtaz',
        'shipping_address' => [
            'recipient_name' => 'رضا حسینی',
            'recipient_mobile' => '09129876543',
            'city_name' => 'مشهد',
            'full_address' => 'خیابان امام رضا',
        ],
        'items_subtotal' => 2000000,
        'shipping_fee' => 650000,
        'final_payable' => 2650000,
    ]);

    $order->update([
        'status' => OrderStatus::Shipped,
        'tracking_code' => '123456789012345678901234',
        'tracking_url' => 'https://tracking.post.ir/?id=123456789012345678901234',
        'shipped_at' => now(),
    ]);

    $response = $this->actingAs($this->user)
        ->getJson(route('orders.show', ['orderNumber' => $order->order_number]));

    $response->assertOk()
        ->assertJsonPath('data.status', 'shipped')
        ->assertJsonPath('data.tracking_code', '123456789012345678901234')
        ->assertJsonPath('data.tracking_url', 'https://tracking.post.ir/?id=123456789012345678901234');
});
