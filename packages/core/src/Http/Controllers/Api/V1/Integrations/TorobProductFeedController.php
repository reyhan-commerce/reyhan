<?php

declare(strict_types=1);

namespace Reyhan\Core\Http\Controllers\Api\V1\Integrations;

use Reyhan\Core\Http\Controllers\Controller;
use Reyhan\Core\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class TorobProductFeedController extends Controller
{
    /**
     * Provide standardized Torob JSON product feed.
     *
     * @see https://torob.com
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
                'product_id' => (string) $product->id,
                'page_unique_code' => "prod_{$product->id}_var_{$variant->id}",
                'title' => $product->name.($variant->title ? " - {$variant->title}" : ''),
                'page_url' => "{$storefrontUrl}/products/{$product->slug}",
                'price' => $priceToman,
                'old_price' => $oldPriceToman > $priceToman ? $oldPriceToman : null,
                'availability' => $variant->stock > 0 ? 'instock' : 'outofstock',
                'image_link' => $primaryImage ?: null,
                'category_name' => $product->category?->name,
                'brand_name' => $product->brand?->name,
                'guarantee' => $variant->guarantee ?? 'ضمانت اصالت و سلامت فیزیکی کالا',
                'delivery_time' => 'ارسال طی ۱ تا ۲ روز کاری',
            ];
        }

        return response()->json([
            'count' => $total,
            'max_pages' => (int) ceil($total / $perPage),
            'current_page' => $page,
            'products' => $items,
        ]);
    }
}
