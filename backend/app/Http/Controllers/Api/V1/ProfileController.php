<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Get the authenticated user profile along with summary counts.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => new UserResource($user),
                'counts' => [
                    'orders' => $user->orders()->count(),
                    'wishlist' => $user->wishlists()->count(),
                    'addresses' => $user->addresses()->count(),
                    'reviews' => $user->reviews()->count(),
                ],
            ],
        ]);
    }

    /**
     * Update customer profile details.
     */
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'first_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'national_code' => ['nullable', 'digits:10', Rule::unique('users', 'national_code')->ignore($user->id)],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'اطلاعات حساب کاربری با موفقیت ویرایش شد.',
            'data' => new UserResource($user->fresh()),
        ]);
    }
}
