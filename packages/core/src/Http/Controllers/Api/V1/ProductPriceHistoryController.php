<?php

declare(strict_types=1);

namespace Reyhan\Core\Http\Controllers\Api\V1;

use Reyhan\Core\Http\Controllers\Controller;
use Reyhan\Core\Models\Product;
use Illuminate\Http\JsonResponse;
use Morilog\Jalali\Jalalian;

final class ProductPriceHistoryController extends Controller
{
    /**
     * Get price history points and stats for a product.
     */
    public function show(Product $product): JsonResponse
    {
        $histories = $product->priceHistories()
            ->where('recorded_at', '>=', now()->subDays(90))
            ->orderBy('recorded_at', 'asc')
            ->get();

        $currentPrice = $product->price ?? $product->activeVariants()->min('price') ?? 0;

        if ($histories->isEmpty() && $currentPrice > 0) {
            // Provide at least initial data point
            $points = [
                [
                    'date' => now()->subDays(30)->toDateString(),
                    'date_jalali' => Jalalian::fromCarbon(now()->subDays(30))->format('m/d'),
                    'price' => $currentPrice,
                    'price_toman' => (int) ($currentPrice / 10),
                ],
                [
                    'date' => now()->toDateString(),
                    'date_jalali' => Jalalian::fromCarbon(now())->format('m/d'),
                    'price' => $currentPrice,
                    'price_toman' => (int) ($currentPrice / 10),
                ],
            ];
            $minPrice = $currentPrice;
            $maxPrice = $currentPrice;
        } else {
            $points = $histories->map(fn ($h) => [
                'date' => $h->recorded_at->toDateString(),
                'date_jalali' => Jalalian::fromCarbon($h->recorded_at)->format('m/d'),
                'price' => $h->price,
                'price_toman' => (int) ($h->price / 10),
            ])->values()->all();

            $prices = $histories->pluck('price')->push($currentPrice);
            $minPrice = $prices->min();
            $maxPrice = $prices->max();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'current_price' => $currentPrice,
                'current_price_toman' => (int) ($currentPrice / 10),
                'min_price' => $minPrice,
                'min_price_toman' => (int) ($minPrice / 10),
                'max_price' => $maxPrice,
                'max_price_toman' => (int) ($maxPrice / 10),
                'points' => $points,
            ],
        ]);
    }
}
