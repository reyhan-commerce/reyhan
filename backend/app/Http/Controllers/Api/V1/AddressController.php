<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\Address\SetDefaultAddressAction;
use App\Actions\Address\StoreAddressAction;
use App\Actions\Address\UpdateAddressAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Address\StoreAddressRequest;
use App\Http\Requests\Api\V1\Address\UpdateAddressRequest;
use App\Http\Resources\V1\AddressResource;
use App\Models\Address;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class AddressController extends Controller
{
    /**
     * List authenticated user's addresses.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        /** @var User $user */
        $user = $request->user();

        $addresses = $user->addresses()
            ->with(['province', 'city'])
            ->orderByDesc('is_default')
            ->latest()
            ->get();

        return AddressResource::collection($addresses);
    }

    /**
     * Store a new address.
     */
    public function store(StoreAddressRequest $request, StoreAddressAction $action): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $address = $action->execute($user, $request->validated());

        return response()->json([
            'success' => true,
            'message' => __('Address created successfully.'),
            'data' => new AddressResource($address),
        ], 201);
    }

    /**
     * Update an address.
     */
    public function update(
        UpdateAddressRequest $request,
        Address $address,
        UpdateAddressAction $action
    ): JsonResponse {
        Gate::authorize('update', $address);

        /** @var User $user */
        $user = $request->user();
        $updatedAddress = $action->execute($user, $address, $request->validated());

        return response()->json([
            'success' => true,
            'message' => __('Address updated successfully.'),
            'data' => new AddressResource($updatedAddress),
        ]);
    }

    /**
     * Delete an address.
     */
    public function destroy(Request $request, Address $address): JsonResponse
    {
        Gate::authorize('delete', $address);

        /** @var User $user */
        $user = $request->user();
        $wasDefault = $address->is_default;
        $address->delete();

        if ($wasDefault) {
            $latest = $user->addresses()->latest()->first();
            $latest?->update(['is_default' => true]);
        }

        return response()->json([
            'success' => true,
            'message' => __('Address deleted successfully.'),
        ]);
    }

    /**
     * Set address as default.
     */
    public function setDefault(
        Request $request,
        Address $address,
        SetDefaultAddressAction $action
    ): JsonResponse {
        Gate::authorize('update', $address);

        /** @var User $user */
        $user = $request->user();
        $updatedAddress = $action->execute($user, $address);

        return response()->json([
            'success' => true,
            'message' => __('Default address set successfully.'),
            'data' => new AddressResource($updatedAddress),
        ]);
    }
}
