<?php

declare(strict_types=1);

use Reyhan\Core\Http\Controllers\OrderShippingLabelController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function (Request $request): JsonResponse|RedirectResponse {
    if ($request->expectsJson()) {
        return response()->json([
            'name' => config('app.name'),
            'status' => 'healthy',
            'version' => 'v1',
        ]);
    }

    return redirect('/admin');
});

Route::get('/orders/{order}/shipping-label', [OrderShippingLabelController::class, 'show'])
    ->name('orders.shipping-label')
    ->middleware('signed');
