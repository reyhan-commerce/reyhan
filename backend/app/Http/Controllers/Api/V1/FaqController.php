<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class FaqController extends Controller
{
    /**
     * Get active FAQs ordered by sorting rank.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Faq::query()->active();

        if ($category = $request->query('category')) {
            $query->where('category', $category);
        }

        $faqs = $query->orderBy('order')
            ->orderBy('id')
            ->get(['id', 'question', 'answer', 'category', 'order']);

        $categories = Faq::query()
            ->active()
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category');

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $faqs,
                'categories' => $categories,
            ],
        ]);
    }
}
