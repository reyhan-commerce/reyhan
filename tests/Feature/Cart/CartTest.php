<?php

declare(strict_types=1);

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;

uses(DatabaseTransactions::class);

beforeEach(function (): void {
    $this->category = Category::create([
        'name' => 'مراقبت پوست',
        'slug' => 'skin-care-'.Str::random(6),
        'is_active' => true,
    ]);

    $this->brand = Brand::create([
        'name' => 'لاروش پوزای',
        'slug' => 'laroche-'.Str::random(6),
        'is_active' => true,
    ]);

    $this->product = Product::create([
        'category_id' => $this->category->id,
        'brand_id' => $this->brand->id,
        'name' => 'ژل شستشو افاکلار لاروش پوزای',
        'slug' => 'effaclar-gel-'.Str::random(6),
        'is_active' => true,
        'published_at' => now()->subMinute(),
    ]);

    $this->variant = ProductVariant::create([
        'product_id' => $this->product->id,
        'sku' => 'LRP-EFF-200',
        'title' => 'حجم ۲۰۰ میلی‌لیتر',
        'price' => 12500000,
        'compare_at_price' => 14000000,
        'stock' => 15,
        'weight' => 250,
        'is_active' => true,
    ]);
});

it('resolves a guest cart with session header', function (): void {
    $sessionId = (string) Str::uuid();

    $response = $this->withHeader('X-Cart-Session', $sessionId)
        ->getJson('/api/v1/cart');

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'data' => [
                'items_count' => 0,
                'items' => [],
            ],
        ])
        ->assertHeader('X-Cart-Session', $sessionId);
});

it('can add an item to the guest cart', function (): void {
    $sessionId = (string) Str::uuid();

    $response = $this->withHeader('X-Cart-Session', $sessionId)
        ->postJson('/api/v1/cart/items', [
            'variant_id' => $this->variant->id,
            'quantity' => 2,
        ]);

    $response->assertCreated()
        ->assertJson([
            'success' => true,
            'data' => [
                'items_count' => 2,
                'items' => [
                    [
                        'quantity' => 2,
                        'unit_price' => 12500000,
                        'subtotal' => 25000000,
                        'variant' => [
                            'id' => $this->variant->id,
                            'sku' => 'LRP-EFF-200',
                        ],
                    ],
                ],
            ],
        ]);
});

it('cannot add out of stock variant to cart', function (): void {
    $outOfStockVariant = ProductVariant::create([
        'product_id' => $this->product->id,
        'sku' => 'LRP-EFF-OOS',
        'title' => 'ناموجود',
        'price' => 10000000,
        'stock' => 0,
        'is_active' => true,
    ]);

    $sessionId = (string) Str::uuid();

    $response = $this->withHeader('X-Cart-Session', $sessionId)
        ->postJson('/api/v1/cart/items', [
            'variant_id' => $outOfStockVariant->id,
            'quantity' => 1,
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['variant_id']);
});

it('can update item quantity and remove item when quantity is 0', function (): void {
    $sessionId = (string) Str::uuid();

    // Add 2 items
    $addResponse = $this->withHeader('X-Cart-Session', $sessionId)
        ->postJson('/api/v1/cart/items', [
            'variant_id' => $this->variant->id,
            'quantity' => 2,
        ]);

    $itemId = $addResponse->json('data.items.0.id');

    // Update to 4
    $updateResponse = $this->withHeader('X-Cart-Session', $sessionId)
        ->putJson("/api/v1/cart/items/{$itemId}", [
            'quantity' => 4,
        ]);

    $updateResponse->assertOk()
        ->assertJsonPath('data.items.0.quantity', 4)
        ->assertJsonPath('data.items_count', 4);

    // Update to 0 (should remove)
    $removeResponse = $this->withHeader('X-Cart-Session', $sessionId)
        ->putJson("/api/v1/cart/items/{$itemId}", [
            'quantity' => 0,
        ]);

    $removeResponse->assertOk()
        ->assertJsonPath('data.items_count', 0)
        ->assertJsonPath('data.items', []);
});

it('can delete an item directly and clear the cart', function (): void {
    $sessionId = (string) Str::uuid();

    $addResponse = $this->withHeader('X-Cart-Session', $sessionId)
        ->postJson('/api/v1/cart/items', [
            'variant_id' => $this->variant->id,
            'quantity' => 2,
        ]);

    $itemId = $addResponse->json('data.items.0.id');

    // Delete single item
    $this->withHeader('X-Cart-Session', $sessionId)
        ->deleteJson("/api/v1/cart/items/{$itemId}")
        ->assertOk()
        ->assertJsonPath('data.items_count', 0);

    // Add again and clear entire cart
    $this->withHeader('X-Cart-Session', $sessionId)
        ->postJson('/api/v1/cart/items', [
            'variant_id' => $this->variant->id,
            'quantity' => 3,
        ]);

    $this->withHeader('X-Cart-Session', $sessionId)
        ->deleteJson('/api/v1/cart')
        ->assertOk()
        ->assertJsonPath('data.items_count', 0)
        ->assertJsonPath('data.items', []);
});

it('syncs guest cart to user upon login', function (): void {
    $user = User::factory()->create();
    $sessionId = (string) Str::uuid();

    // Guest adds items
    $this->withHeader('X-Cart-Session', $sessionId)
        ->postJson('/api/v1/cart/items', [
            'variant_id' => $this->variant->id,
            'quantity' => 3,
        ])->assertCreated();

    // User logs in and syncs
    $syncResponse = $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/cart/sync', [
            'session_id' => $sessionId,
        ]);

    $syncResponse->assertOk()
        ->assertJsonPath('data.items_count', 3)
        ->assertJsonPath('data.items.0.quantity', 3);

    // Verify guest cart has been cleaned up
    $this->assertDatabaseMissing('carts', [
        'session_id' => $sessionId,
        'user_id' => null,
    ]);
});
