<?php

declare(strict_types=1);

use Reyhan\Core\Http\Controllers\Api\V1\AddressController;
use Reyhan\Core\Http\Controllers\Api\V1\AppFeaturesController;
use Reyhan\Core\Http\Controllers\Api\V1\AppSettingController;
use Reyhan\Core\Http\Controllers\Api\V1\AuthController;
use Reyhan\Core\Http\Controllers\Api\V1\BannerController;
use Reyhan\Core\Http\Controllers\Api\V1\BlogController;
use Reyhan\Core\Http\Controllers\Api\V1\CaptchaController;
use Reyhan\Core\Http\Controllers\Api\V1\CartController;
use Reyhan\Core\Http\Controllers\Api\V1\CartCouponController;
use Reyhan\Core\Http\Controllers\Api\V1\CartItemController;
use Reyhan\Core\Http\Controllers\Api\V1\CartSyncController;
use Reyhan\Core\Http\Controllers\Api\V1\CatalogFiltersController;
use Reyhan\Core\Http\Controllers\Api\V1\CategoryController;
use Reyhan\Core\Http\Controllers\Api\V1\CategoryTreeController;
use Reyhan\Core\Http\Controllers\Api\V1\CheckoutController;
use Reyhan\Core\Http\Controllers\Api\V1\CompareProductsController;
use Reyhan\Core\Http\Controllers\Api\V1\ContactMessageController;
use Reyhan\Core\Http\Controllers\Api\V1\FaqController;
use Reyhan\Core\Http\Controllers\Api\V1\GeoController;
use Reyhan\Core\Http\Controllers\Api\V1\LoyaltyController;
use Reyhan\Core\Http\Controllers\Api\V1\OrderController;
use Reyhan\Core\Http\Controllers\Api\V1\OrderInvoiceController;
use Reyhan\Core\Http\Controllers\Api\V1\OrderReturnController;
use Reyhan\Core\Http\Controllers\Api\V1\PageController;
use Reyhan\Core\Http\Controllers\Api\V1\PaymentController;
use Reyhan\Core\Http\Controllers\Api\V1\ProductController;
use Reyhan\Core\Http\Controllers\Api\V1\ProductPriceHistoryController;
use Reyhan\Core\Http\Controllers\Api\V1\ProductQuestionController;
use Reyhan\Core\Http\Controllers\Api\V1\ProfileController;
use Reyhan\Core\Http\Controllers\Api\V1\ReferralController;
use Reyhan\Core\Http\Controllers\Api\V1\ReviewController;
use Reyhan\Core\Http\Controllers\Api\V1\SearchSuggestionController;
use Reyhan\Core\Http\Controllers\Api\V1\StockAlertController;
use Reyhan\Core\Http\Controllers\Api\V1\SupportTicketController;
use Reyhan\Core\Http\Controllers\Api\V1\WalletController;
use Reyhan\Core\Http\Controllers\Api\V1\WishlistController;
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

// Public Store Settings & Features
Route::get('/app/settings', [AppSettingController::class, 'index'])->name('app.settings');
Route::get('/app/theme', [AppSettingController::class, 'theme'])->name('app.theme');
Route::get('/app/features', [AppFeaturesController::class, 'index'])->name('app.features');

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
Route::get('/search/suggestions', [SearchSuggestionController::class, 'index'])->name('search.suggestions');
Route::get('/catalog/filters', CatalogFiltersController::class)->name('catalog.filters');
Route::post('/catalog/stock-alerts', [StockAlertController::class, 'store'])->name('catalog.stock-alerts');
Route::post('/products/compare', CompareProductsController::class)->name('products.compare');
Route::get('/products/{product}/related', [ProductController::class, 'related'])->name('products.related');
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
        Route::get('/shipping-methods', [CheckoutController::class, 'shippingMethods'])->name('shipping-methods');
        Route::post('/create-order', [CheckoutController::class, 'createOrder'])->name('create-order');
    });

    // Orders History & Invoice
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{orderNumber}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('/orders/{orderNumber}/invoice', [OrderInvoiceController::class, 'show'])->name('orders.invoice');

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

    // Customer Loyalty Club (VIP)
    Route::prefix('loyalty')->name('loyalty.')->group(function () {
        Route::get('/summary', [LoyaltyController::class, 'summary'])->name('summary');
        Route::get('/transactions', [LoyaltyController::class, 'transactions'])->name('transactions');
        Route::post('/redeem', [LoyaltyController::class, 'redeem'])->name('redeem');
    });

    // Customer Wallet
    Route::prefix('wallet')->name('wallet.')->group(function () {
        Route::get('/', [WalletController::class, 'index'])->name('index');
        Route::post('/top-up', [WalletController::class, 'topUp'])->name('top-up');
    });

    // 7-Day Online Order Returns (RMA)
    Route::get('/profile/returns', [OrderReturnController::class, 'index'])->name('profile.returns.index');
    Route::get('/profile/returns/{returnNumber}', [OrderReturnController::class, 'show'])->name('profile.returns.show');
    Route::post('/orders/{orderNumber}/returns', [OrderReturnController::class, 'store'])->name('orders.returns.store');

    // Support Tickets & Helpdesk
    Route::prefix('tickets')->name('tickets.')->group(function () {
        Route::get('/', [SupportTicketController::class, 'index'])->name('index');
        Route::post('/', [SupportTicketController::class, 'store'])->name('store');
        Route::get('/{ticketNumber}', [SupportTicketController::class, 'show'])->name('show');
        Route::post('/{ticketNumber}/messages', [SupportTicketController::class, 'reply'])->name('reply');
        Route::put('/{ticketNumber}/close', [SupportTicketController::class, 'close'])->name('close');
    });

    // Customer Referral Program
    Route::prefix('referral')->name('referral.')->group(function () {
        Route::get('/', [ReferralController::class, 'index'])->name('index');
        Route::post('/claim', [ReferralController::class, 'claim'])->name('claim');
    });
    Route::prefix('profile/referral')->name('profile.referral.')->group(function () {
        Route::get('/', [ReferralController::class, 'index'])->name('profile.index');
        Route::post('/claim', [ReferralController::class, 'claim'])->name('profile.claim');
    });

    // Product Questions & Answers (Interactive)
    Route::post('/products/{product}/questions', [ProductQuestionController::class, 'store'])->name('products.questions.store');
    Route::post('/questions/{question}/answers', [ProductQuestionController::class, 'storeAnswer'])->name('questions.answers.store');
    Route::post('/questions/{question}/like', [ProductQuestionController::class, 'like'])->name('questions.like');
});

// Promotional Banners & Sliders (Public)
Route::get('/banners', [BannerController::class, 'index'])->name('banners.index');

// Product Questions (Public List)
Route::get('/products/{product}/questions', [ProductQuestionController::class, 'index'])->name('products.questions.index');

// Product Price History (Public Chart)
Route::get('/products/{product}/price-history', [ProductPriceHistoryController::class, 'show'])->name('products.price-history');

// Signed Order Invoice (Admin / Shareable Print Link)
Route::get('/orders/{orderNumber}/invoice/signed', [OrderInvoiceController::class, 'showSigned'])
    ->name('orders.invoice.signed')
    ->middleware('signed:relative');

// Customer Loyalty Club Tiers (Public)
Route::get('/loyalty/tiers', [LoyaltyController::class, 'tiers'])->name('loyalty.tiers');

// Product Reviews (Public List)
Route::get('/products/{product}/reviews', [ReviewController::class, 'index'])->name('reviews.index');

// Static CMS Pages & Dynamic Sitemap
Route::get('/pages/{slug}', [PageController::class, 'show'])->name('pages.show');
Route::get('/sitemap/urls', [PageController::class, 'sitemap'])->name('sitemap.urls');

// Frequently Asked Questions (FAQ)
Route::get('/faqs', [FaqController::class, 'index'])->name('faqs.index');

// Customer Contact Inquiries
Route::post('/contact', [ContactMessageController::class, 'store'])->name('contact.store');

// Blog & Magazine
Route::prefix('blog')->name('blog.')->group(function () {
    Route::get('/categories', [BlogController::class, 'categories'])->name('categories');
    Route::get('/featured', [BlogController::class, 'featured'])->name('featured');
    Route::get('/posts', [BlogController::class, 'index'])->name('posts.index');
    Route::get('/posts/{slug}', [BlogController::class, 'show'])->name('posts.show');
});

// Marketplace Product Feeds (Torob & Emalls)
Route::prefix('integrations')->name('integrations.')->group(function () {
    Route::get('/torob/products', \Reyhan\Core\Http\Controllers\Api\V1\Integrations\TorobProductFeedController::class)->name('torob.products');
    Route::get('/emalls/products', \Reyhan\Core\Http\Controllers\Api\V1\Integrations\EmallsProductFeedController::class)->name('emalls.products');
});

