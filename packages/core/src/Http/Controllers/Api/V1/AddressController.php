<?php

declare(strict_types=1);

namespace Reyhan\Core\Http\Controllers\Api\V1;

use Reyhan\Core\Actions\Address\DeleteAddressAction;
use Reyhan\Core\Actions\Address\SetDefaultAddressAction;
use Reyhan\Core\Actions\Address\StoreAddressAction;
use Reyhan\Core\Actions\Address\UpdateAddressAction;
use Reyhan\Core\Data\Address\StoreAddressData;
use Reyhan\Core\Data\Address\UpdateAddressData;
use Reyhan\Core\Http\Controllers\Controller;
use Reyhan\Core\Http\Requests\Api\V1\Address\StoreAddressRequest;
use Reyhan\Core\Http\Requests\Api\V1\Address\UpdateAddressRequest;
use Reyhan\Core\Http\Resources\V1\AddressResource;
use Reyhan\Core\Models\Address;
use Reyhan\Core\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

final class AddressController extends Controller
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
        $address = $action->execute($user, StoreAddressData::from($request->validated()));

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
        $updatedAddress = $action->execute($user, $address, UpdateAddressData::from($request->validated()));

        return response()->json([
            'success' => true,
            'message' => __('Address updated successfully.'),
            'data' => new AddressResource($updatedAddress),
        ]);
    }

    /**
     * Delete an address.
     */
    public function destroy(
        Request $request,
        Address $address,
        DeleteAddressAction $action
    ): JsonResponse {
        Gate::authorize('delete', $address);

        /** @var User $user */
        $user = $request->user();
        $action->execute($user, $address);

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
