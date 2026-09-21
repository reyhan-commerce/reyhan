<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\AddressController;
use App\Http\Controllers\Api\V1\AppSettingController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CaptchaController;
use App\Http\Controllers\Api\V1\CartController;
use App\Http\Controllers\Api\V1\CartCouponController;
use App\Http\Controllers\Api\V1\CartItemController;
use App\Http\Controllers\Api\V1\CartSyncController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CategoryTreeController;
use App\Http\Controllers\Api\V1\CheckoutController;
use App\Http\Controllers\Api\V1\GeoController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\PageController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\ReviewController;
use App\Http\Controllers\Api\V1\WishlistController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API V1 Routes
|--------------------------------------------------------------------------
|
| Prefix: /api/v1
| All routes in this file are versioned RESTful endpoints.
|
*/

// Public Store Settings
Route::get('/app/settings', [AppSettingController::class, 'index'])->name('app.settings');

// Captcha
Route::get('/captcha/generate', [CaptchaController::class, 'generate'])->name('captcha.generate');
Route::post('/captcha/solve', [CaptchaController::class, 'solve'])->name('captcha.solve');

// Customer Authentication
Route::prefix('auth')->name('auth.')->group(function () {
    Route::post('/otp/request', [AuthController::class, 'requestOtp'])->name('otp.request');
    Route::post('/otp/verify', [AuthController::class, 'verifyOtp'])->name('otp.verify');

    // Authenticated User Endpoints
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/me', [AuthController::class, 'me'])->name('me');
    });
});

// Product Catalog & Category Taxonomy
Route::get('/categories/tree', CategoryTreeController::class)->name('categories.tree');
Route::apiResource('categories', CategoryController::class)->only(['index', 'show']);
Route::apiResource('products', ProductController::class)->only(['index', 'show']);

// Shopping Cart
Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::delete('/', [CartController::class, 'destroy'])->name('destroy');
    Route::post('/items', [CartItemController::class, 'store'])->name('items.store');
    Route::put('/items/{cartItem}', [CartItemController::class, 'update'])->name('items.update');
    Route::delete('/items/{cartItem}', [CartItemController::class, 'destroy'])->name('items.destroy');
    Route::post('/coupon', [CartCouponController::class, 'store'])->name('coupon.store');
    Route::delete('/coupon', [CartCouponController::class, 'destroy'])->name('coupon.destroy');
    Route::middleware('auth:sanctum')->post('/sync', CartSyncController::class)->name('sync');
});

// Iranian Geographical Data
Route::prefix('geo')->name('geo.')->group(function () {
    Route::get('/provinces', [GeoController::class, 'provinces'])->name('provinces');
    Route::get('/provinces/{province}/cities', [GeoController::class, 'cities'])->name('cities');
});

// Payment Gateways & Callback (Public callback from bank)
Route::prefix('payment')->name('payment.')->group(function () {
    Route::get('/gateways', [PaymentController::class, 'gateways'])->name('gateways');
    Route::post('/verify', [PaymentController::class, 'verify'])->name('verify');
    Route::get('/verify', [PaymentController::class, 'verify'])->name('verify.get');
});

// Authenticated Customer Operations: Addresses, Checkout & Orders
Route::middleware('auth:sanctum')->group(function () {
    // User Addresses
    Route::apiResource('addresses', AddressController::class);
    Route::patch('/addresses/{address}/default', [AddressController::class, 'setDefault'])->name('addresses.default');

    // Checkout
    Route::prefix('checkout')->name('checkout.')->group(function () {
        Route::get('/preview', [CheckoutController::class, 'preview'])->name('preview');
        Route::post('/create-order', [CheckoutController::class, 'createOrder'])->name('create-order');
    });

    // Orders History
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{orderNumber}', [OrderController::class, 'show'])->name('orders.show');

    // Customer Profile
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Customer Wishlist
    Route::prefix('wishlist')->name('wishlist.')->group(function () {
        Route::get('/', [WishlistController::class, 'index'])->name('index');
        Route::get('/ids', [WishlistController::class, 'ids'])->name('ids');
        Route::post('/{product}/toggle', [WishlistController::class, 'toggle'])->name('toggle');
    });

    // Submit Product Review
    Route::post('/products/{product}/reviews', [ReviewController::class, 'store'])->name('reviews.store');
});

// Product Reviews (Public List)
Route::get('/products/{product}/reviews', [ReviewController::class, 'index'])->name('reviews.index');

// Static CMS Pages & Dynamic Sitemap
Route::get('/pages/{slug}', [PageController::class, 'show'])->name('pages.show');
Route::get('/sitemap/urls', [PageController::class, 'sitemap'])->name('sitemap.urls');
