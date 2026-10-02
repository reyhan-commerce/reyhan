<?php

declare(strict_types=1);

use Reyhan\Core\Contracts\Models\UserContract;
use Reyhan\Core\Facades\Cart;
use Reyhan\Core\Facades\Checkout;
use Reyhan\Core\Facades\Inventory;
use Reyhan\Core\Facades\Pricing;
use Reyhan\Core\Facades\Reyhan;
use Reyhan\Core\Models\Cart as CartModel;
use Reyhan\Core\Models\Product;
use Reyhan\Core\Models\ProductVariant;
use Reyhan\Core\Models\User;
use Reyhan\Core\Services\Cart\CartService;
use Reyhan\Core\Services\Checkout\CheckoutService;
use Reyhan\Core\Services\Inventory\StockReservationService;
use Reyhan\Core\Services\Pricing\PricingService;
use Illuminate\Support\Str;

afterEach(function () {
    \Reyhan\Core\Pipelines\Checkout\OrderCreationPipeline::resetPipes();
    \Reyhan\Core\Pipelines\Cart\CartCalculationPipeline::resetPipes();
});

test('first-class facades resolve expected services from container', function () {
    expect(app(CartService::class))->toBeInstanceOf(CartService::class)
        ->and(app('reyhan.cart'))->toBeInstanceOf(CartService::class)
        ->and(Reyhan::cart())->toBeInstanceOf(CartService::class)
        ->and(app(StockReservationService::class))->toBeInstanceOf(StockReservationService::class)
        ->and(app('reyhan.inventory'))->toBeInstanceOf(StockReservationService::class)
        ->and(Reyhan::inventory())->toBeInstanceOf(StockReservationService::class)
        ->and(app(PricingService::class))->toBeInstanceOf(PricingService::class)
        ->and(app('reyhan.pricing'))->toBeInstanceOf(PricingService::class)
        ->and(Reyhan::pricing())->toBeInstanceOf(PricingService::class)
        ->and(app(CheckoutService::class))->toBeInstanceOf(CheckoutService::class)
        ->and(app('reyhan.checkout'))->toBeInstanceOf(CheckoutService::class)
        ->and(Reyhan::checkout())->toBeInstanceOf(CheckoutService::class);
});

test('Cart facade operations work seamlessly', function () {
    /** @var User $user */
    $user = User::factory()->create();

    // 1. Resolve cart
    $cart = Cart::resolveCart($user);
    expect($cart)->toBeInstanceOf(CartModel::class)
        ->and($cart->user_id)->toBe($user->id);

    // 2. Add item
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'stock' => 15,
        'price' => 250_000,
    ]);

    $item = Cart::addItem($cart, $variant->id, 2);
    expect($item->quantity)->toBe(2)
        ->and($item->product_variant_id)->toBe($variant->id);

    // 3. Update quantity
    $updated = Cart::updateQuantity($item, 4);
    expect($updated->quantity)->toBe(4);

    // 4. Remove item
    Cart::removeItem($item);
    expect($cart->fresh()->items)->toHaveCount(0);
});

test('Inventory facade handles atomic stock reservation and release', function () {
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'stock' => 20,
    ]);

    $resId = (string) Str::uuid();

    // Available stock should be equal to initial DB stock
    expect(Inventory::getAvailableStock($variant))->toBe(20);

    // Reserve 5 units
    $reserved = Inventory::reserve($variant->id, 5, $resId);
    expect($reserved)->toBeTrue()
        ->and(Inventory::getAvailableStock($variant))->toBe(15);

    // Over-reservation should fail
    $overReserve = Inventory::reserve($variant->id, 16, (string) Str::uuid());
    expect($overReserve)->toBeFalse();

    // Release reservation
    Inventory::release($variant->id, 5, $resId);
    expect(Inventory::getAvailableStock($variant))->toBe(20);
});

test('Pricing facade calculates cart breakdown accurately', function () {
    /** @var User $user */
    $user = User::factory()->create();
    $cart = Cart::resolveCart($user);

    $product = Product::factory()->create(['is_tax_exempt' => false]);
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'stock' => 10,
        'price' => 500_000,
        'compare_at_price' => 600_000,
    ]);

    Cart::addItem($cart, $variant->id, 2);

    $pricing = Pricing::calculateCart($cart);

    expect($pricing->itemsSubtotal)->toBe(1_000_000)
        ->and($pricing->originalItemsSubtotal)->toBe(1_200_000)
        ->and($pricing->catalogDiscount)->toBe(200_000)
        ->and($pricing->totalItemsCount)->toBe(2);
});

test('Checkout facade exposes pipeline and extension hooks', function () {
    expect(Checkout::pipeline())->toBeInstanceOf(\Reyhan\Core\Pipelines\Checkout\OrderCreationPipeline::class);

    $initialPipesCount = count(\Reyhan\Core\Pipelines\Checkout\OrderCreationPipeline::getPipes());

    // Test pipe hooking
    $dummyPipe = 'DummyCustomPipeClass';
    Checkout::appendPipe($dummyPipe);

    $pipes = \Reyhan\Core\Pipelines\Checkout\OrderCreationPipeline::getPipes();
    expect($pipes)
        ->toHaveCount($initialPipesCount + 1)
        ->and(end($pipes))->toBe($dummyPipe);
});

test('Pricing facade exposes CartCalculationPipeline and supports custom pipes', function () {
    expect(Pricing::pipeline())->toBeInstanceOf(\Reyhan\Core\Pipelines\Cart\CartCalculationPipeline::class);

    $initialPipesCount = count(\Reyhan\Core\Pipelines\Cart\CartCalculationPipeline::getPipes());

    $dummyPricingPipe = 'DummyPricingCustomPipe';
    Pricing::appendPipe($dummyPricingPipe);

    $pipes = \Reyhan\Core\Pipelines\Cart\CartCalculationPipeline::getPipes();
    expect($pipes)
        ->toHaveCount($initialPipesCount + 1)
        ->and(end($pipes))->toBe($dummyPricingPipe);
});

