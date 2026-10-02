<?php

declare(strict_types=1);

namespace Reyhan\Core\Http\Controllers\Api\V1\Integrations;

use Reyhan\Core\Http\Controllers\Controller;
use Reyhan\Core\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class EmallsProductFeedController extends Controller
{
    /**
     * Provide standardized Emalls JSON product feed.
     *
     * @see https://emalls.ir
     */
    public function __invoke(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->query('page', 1));
        $perPage = min(100, max(10, (int) $request->query('per_page', 50)));

        $query = Product::query()
            ->where('is_active', true)
            ->with(['variants' => function ($q): void {
                $q->where('is_active', true)->orderBy('price', 'asc');
            }, 'category', 'brand', 'media']);

        $total = $query->count();
        $products = $query->forPage($page, $perPage)->get();
        $storefrontUrl = rtrim((string) config('app.frontend_url', config('app.url', 'https://reyhan.ir')), '/');

        $items = [];

        foreach ($products as $product) {
            $variant = $product->variants->first();
            if (! $variant) {
                continue;
            }

            $priceRial = $variant->price;
            $oldPriceRial = $variant->compare_at_price ?? $priceRial;
            $priceToman = (int) ($priceRial / 10);
            $oldPriceToman = (int) ($oldPriceRial / 10);

            $primaryImage = $product->getFirstMediaUrl('featured_image') ?: $product->getFirstMediaUrl('gallery');

            $items[] = [
                'id' => (string) $product->id,
                'title' => $product->name,
                'subtitle' => $variant->title,
                'url' => "{$storefrontUrl}/products/{$product->slug}",
                'price' => $priceToman,
                'old_price' => $oldPriceToman > $priceToman ? $oldPriceToman : null,
                'is_available' => $variant->stock > 0,
                'image' => $primaryImage ?: null,
                'category' => $product->category?->name,
                'brand' => $product->brand?->name,
                'warranty' => $variant->guarantee ?? 'گارانتی اصالت و سلامت فیزیکی',
            ];
        }

        return response()->json([
            'total' => $total,
            'pages' => (int) ceil($total / $perPage),
            'page' => $page,
            'items' => $items,
        ]);
    }
}
