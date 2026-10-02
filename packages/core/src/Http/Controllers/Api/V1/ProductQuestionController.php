<?php

declare(strict_types=1);

namespace Reyhan\Core\Http\Controllers\Api\V1;

use Reyhan\Core\Http\Controllers\Controller;
use Reyhan\Core\Models\Product;
use Reyhan\Core\Models\ProductAnswer;
use Reyhan\Core\Models\ProductQuestion;
use Reyhan\Core\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Morilog\Jalali\Jalalian;

final class ProductQuestionController extends Controller
{
    /**
     * List approved questions and answers for a product.
     */
    public function index(Product $product): JsonResponse
    {
        $questions = $product->questions()
            ->where('is_approved', true)
            ->with([
                'user:id,first_name,last_name',
                'answers' => fn ($q) => $q->where('is_approved', true)->with('user:id,first_name,last_name')->latest(),
            ])
            ->latest()
            ->paginate(15);

        $data = $questions->through(fn (ProductQuestion $q) => [
            'id' => $q->id,
            'question' => $q->question,
            'author_name' => $q->user ? trim(($q->user->first_name ?? '').' '.($q->user->last_name ?? '')) : 'کاربر مهمان',
            'likes_count' => $q->likes_count,
            'created_at' => $q->created_at->toIso8601String(),
            'created_at_jalali' => Jalalian::fromCarbon($q->created_at)->format('Y/m/d'),
            'answers' => $q->answers->map(fn (ProductAnswer $a) => [
                'id' => $a->id,
                'answer' => $a->answer,
                'author_name' => $a->is_staff ? 'کارشناس فروشگاه' : ($a->user ? trim(($a->user->first_name ?? '').' '.($a->user->last_name ?? '')) : 'کاربر'),
                'is_staff' => $a->is_staff,
                'likes_count' => $a->likes_count,
                'created_at' => $a->created_at->toIso8601String(),
                'created_at_jalali' => Jalalian::fromCarbon($a->created_at)->format('Y/m/d'),
            ]),
        ]);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Post a question for a product.
     */
    public function store(Request $request, Product $product): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'question' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        $question = $product->questions()->create([
            'user_id' => $user->id,
            'question' => $validated['question'],
            'is_approved' => false, // Requires admin moderation
        ]);

        return response()->json([
            'success' => true,
            'message' => __('messages.questions.submitted_success'),
            'data' => [
                'id' => $question->id,
                'question' => $question->question,
                'is_approved' => $question->is_approved,
            ],
        ], 201);
    }

    /**
     * Submit an answer to a question.
     */
    public function storeAnswer(Request $request, ProductQuestion $question): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'answer' => ['required', 'string', 'min:3', 'max:1000'],
        ]);

        $answer = $question->answers()->create([
            'user_id' => $user->id,
            'answer' => $validated['answer'],
            'is_approved' => false,
            'is_staff' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => __('messages.questions.answer_submitted_success'),
            'data' => [
                'id' => $answer->id,
                'answer' => $answer->answer,
            ],
        ], 201);
    }

    /**
     * Like a question.
     */
    public function like(ProductQuestion $question): JsonResponse
    {
        $question->increment('likes_count');

        return response()->json([
            'success' => true,
            'data' => [
                'likes_count' => $question->likes_count,
            ],
        ]);
    }
}
