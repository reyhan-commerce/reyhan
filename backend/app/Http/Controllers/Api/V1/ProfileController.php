<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\Profile\UpdateProfileAction;
use App\Data\Profile\UpdateProfileData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Profile\UpdateProfileRequest;
use App\Http\Resources\V1\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ProfileController extends Controller
{
    /**
     * Get the authenticated user profile along with summary counts.
     */
    public function show(Request $request): JsonResponse
    {
        /** @var User $user */
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
    public function update(
        UpdateProfileRequest $request,
        UpdateProfileAction $action
    ): JsonResponse {
        /** @var User $user */
        $user = $request->user();
        $updatedUser = $action->execute($user, UpdateProfileData::from($request->validated()));

        return response()->json([
            'success' => true,
            'message' => __('Account details updated successfully.'),
            'data' => new UserResource($updatedUser),
        ]);
    }
}
