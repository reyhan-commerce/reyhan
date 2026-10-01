<?php

declare(strict_types=1);

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Userland Custom API Routes
|--------------------------------------------------------------------------
|
| The Reyhan Core engine provides all standard e-commerce endpoints at /api/v1/*.
| You can register custom API endpoints, webhooks, or 3rd-party integrations here.
|
*/

Route::get('/ping', fn (): JsonResponse => response()->json(['status' => 'pong', 'shop' => config('app.name')]));
