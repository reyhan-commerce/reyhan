<?php

declare(strict_types=1);

use Reyhan\Core\Models\Address;
use Reyhan\Core\Models\Brand;
use Reyhan\Core\Models\Cart;
use Reyhan\Core\Models\Category;
use Reyhan\Core\Models\City;
use Reyhan\Core\Models\Product;
use Reyhan\Core\Models\ProductVariant;
use Reyhan\Core\Models\Province;
use Reyhan\Core\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;

uses(DatabaseTransactions::class);

beforeEach(function (): void {
    $this->user = User::factory()->create([
        'mobile' => '09121112233',
        'is_active' => true,
    ]);

    $this->province = Province::factory()->create([
        'name' => 'تهران',
        'slug' => 'tehran-order-'.uniqid(),
        'order' => 1,
    ]);

    $this->city = City::factory()->create([
        'province_id' => $this->province->id,
        'name' => 'تهران',
        'slug' => 'tehran-city-order-'.uniqid(),
        'order' => 1,
    ]);

    $this->address = Address::factory()->default()->create([
        'user_id' => $this->user->id,
        'province_id' => $this->province->id,
        'city_id' => $this->city->id,
        'recipient_name' => 'خریدار نمونه',
        'recipient_mobile' => '09121112233',
        'postal_code' => '1234567890',
        'address_line' => 'خیابان تست، پلاک ۱',
    ]);

    $category = Category::factory()->create([
        'name' => 'پوست',
        'slug' => 'skin-'.uniqid(),
    ]);

    $brand = Brand::factory()->create([
        'name' => 'برند تست',
        'slug' => 'brand-'.uniqid(),
    ]);

    $this->product = Product::factory()->create([
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'name' => 'کرم مرطوب کننده سفارش',
        'slug' => 'product-'.uniqid(),
    ]);

    $this->variant = ProductVariant::factory()->create([
        'product_id' => $this->product->id,
        'sku' => 'SKU-'.strtoupper(uniqid()),
        'price' => 5000000,
        'compare_at_price' => 6000000,
        'stock' => 10,
    ]);
});

it('previews checkout pricing and shipping fee', function (): void {
    Sanctum::actingAs($this->user);

    $cart = Cart::factory()->create(['user_id' => $this->user->id]);
    $cart->items()->create([
        'product_variant_id' => $this->variant->id,
        'quantity' => 2,
    ]);

    $response = $this->getJson(route('checkout.preview', ['address_id' => $this->address->id]));

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.pricing.original_items_subtotal', 12000000)
        ->assertJsonPath('data.pricing.items_subtotal', 10000000)
        ->assertJsonPath('data.items_count', 2);
});

it('creates order from cart, reserves stock and generates payment redirect', function (): void {
    Sanctum::actingAs($this->user);

    $cart = Cart::factory()->create(['user_id' => $this->user->id]);
    $cart->items()->create([
        'product_variant_id' => $this->variant->id,
        'quantity' => 2,
    ]);

    $payload = [
        'address_id' => $this->address->id,
        'shipping_method' => 'pishtaz',
        'gateway' => 'sandbox',
        'callback_url' => 'http://localhost:3000/checkout/callback',
        'notes' => 'لطفاً عصر تحویل دهید.',
    ];

    $response = $this->postJson(route('checkout.create-order'), $payload);

    $response->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonStructure([
            'data' => [
                'order_id',
                'order_number',
                'final_payable',
                'authority',
                'redirect_url',
            ],
        ]);

    $orderId = $response->json('data.order_id');

    $this->assertDatabaseHas('orders', [
        'id' => $orderId,
        'user_id' => $this->user->id,
        'status' => 'pending_payment',
    ]);

    $this->assertDatabaseHas('order_items', [
        'order_id' => $orderId,
        'product_variant_id' => $this->variant->id,
        'quantity' => 2,
        'final_price' => 5000000,
    ]);

    $this->assertDatabaseHas('payments', [
        'order_id' => $orderId,
        'gateway' => 'sandbox',
        'status' => 'pending',
    ]);
});
