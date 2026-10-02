<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\URL;
use Laravel\Sanctum\Sanctum;
use Reyhan\Core\Enums\OrderStatus;
use Reyhan\Core\Enums\ShippingMethod;
use Reyhan\Core\Http\Controllers\Api\V1\OrderInvoiceController;
use Reyhan\Core\Models\Brand;
use Reyhan\Core\Models\Category;
use Reyhan\Core\Models\Order;
use Reyhan\Core\Models\OrderItem;
use Reyhan\Core\Models\Product;
use Reyhan\Core\Models\ProductVariant;
use Reyhan\Core\Models\User;

uses(DatabaseTransactions::class);

beforeEach(function (): void {
    $this->user = User::factory()->create([
        'first_name' => 'علی',
        'last_name' => 'محمدی',
        'mobile' => '09121112233',
        'is_active' => true,
    ]);

    $this->otherUser = User::factory()->create([
        'first_name' => 'رضا',
        'last_name' => 'حسینی',
        'mobile' => '09124445566',
        'is_active' => true,
    ]);

    $category = Category::factory()->create();
    $brand = Brand::factory()->create();

    $this->product = Product::factory()->create([
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'name' => 'کرم مرطوب کننده صورت',
    ]);

    $this->variant = ProductVariant::factory()->create([
        'product_id' => $this->product->id,
        'price' => 350000,
        'stock' => 10,
    ]);

    $this->order = Order::factory()->create([
        'user_id' => $this->user->id,
        'order_number' => Order::generateOrderNumber(),
        'status' => OrderStatus::Processing,
        'shipping_method' => ShippingMethod::Express,
        'shipping_address' => [
            'recipient_name' => 'علی محمدی',
            'mobile' => '09121112233',
            'city_name' => 'تهران',
            'province_name' => 'تهران',
            'full_address' => 'تهران، خیابان آزادی، پلاک ۱',
            'postal_code' => '1234567890',
        ],
        'items_subtotal' => 350000,
        'discount_amount' => 0,
        'coupon_discount' => 0,
        'shipping_fee' => 45000,
        'final_payable' => 395000,
        'paid_at' => now(),
    ]);

    OrderItem::factory()->create([
        'order_id' => $this->order->id,
        'product_id' => $this->product->id,
        'product_variant_id' => $this->variant->id,
        'product_name' => $this->product->name,
        'variant_title' => $this->variant->title,
        'sku' => $this->variant->sku,
        'unit_price' => 350000,
        'discount_amount' => 0,
        'final_price' => 350000,
        'quantity' => 1,
        'total_price' => 350000,
    ]);
});

test('authenticated customer can fetch their own order invoice', function (): void {
    Sanctum::actingAs($this->user);

    $response = $this->getJson("/api/v1/orders/{$this->order->order_number}/invoice");

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.order_number', $this->order->order_number)
        ->assertJsonPath('data.final_payable', 395000)
        ->assertJsonPath('data.user.name', 'علی محمدی')
        ->assertJsonPath('data.items.0.product_name', 'کرم مرطوب کننده صورت');
});

test('unauthenticated request to customer invoice returns 401', function (): void {
    $response = $this->getJson("/api/v1/orders/{$this->order->order_number}/invoice");

    $response->assertUnauthorized();
});

test('customer cannot view another customers order invoice', function (): void {
    Sanctum::actingAs($this->otherUser);

    $response = $this->getJson("/api/v1/orders/{$this->order->order_number}/invoice");

    $response->assertNotFound();
});

test('valid signed invoice url allows access without user authentication', function (): void {
    $signedUrl = URL::temporarySignedRoute(
        'orders.invoice.signed',
        now()->addMinutes(30),
        ['orderNumber' => $this->order->order_number],
        false
    );

    $response = $this->getJson($signedUrl);

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.order_number', $this->order->order_number)
        ->assertJsonPath('data.final_payable', 395000)
        ->assertJsonPath('data.user.name', 'علی محمدی');
});

test('tampered signature url returns 403 forbidden', function (): void {
    $signedUrl = URL::temporarySignedRoute(
        'orders.invoice.signed',
        now()->addMinutes(30),
        ['orderNumber' => $this->order->order_number],
        false
    );

    // Tamper with signature
    $tamperedUrl = $signedUrl.'&tampered=1';

    $response = $this->getJson($tamperedUrl);

    $response->assertForbidden();
});

test('generateAdminInvoiceUrl creates valid frontend invoice link with valid signature', function (): void {
    $frontendUrl = OrderInvoiceController::generateAdminInvoiceUrl($this->order);

    expect($frontendUrl)->toContain('/invoice/'.$this->order->order_number);
    expect($frontendUrl)->toContain('signature=');
    expect($frontendUrl)->toContain('expires=');

    // Extract query string and verify it works against the signed API route
    $query = parse_url($frontendUrl, PHP_URL_QUERY);
    $apiUrl = "/api/v1/orders/{$this->order->order_number}/invoice/signed?{$query}";

    $response = $this->getJson($apiUrl);
    $response->assertOk()
        ->assertJsonPath('data.order_number', $this->order->order_number);
});
