<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\AddressResource;
use App\Models\Address;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

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
    public function store(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'province_id' => ['required', 'integer', 'exists:provinces,id'],
            'city_id' => ['required', 'integer', 'exists:cities,id'],
            'recipient_name' => ['required', 'string', 'max:100'],
            'recipient_mobile' => ['required', 'string', 'regex:/^09[0-9]{9}$/'],
            'postal_code' => ['required', 'string', 'digits:10'],
            'address_line' => ['required', 'string', 'max:500'],
            'building_number' => ['nullable', 'string', 'max:20'],
            'unit' => ['nullable', 'string', 'max:20'],
            'is_default' => ['nullable', 'boolean'],
        ], [
            'province_id.required' => 'انتخاب استان الزامی است.',
            'city_id.required' => 'انتخاب شهر الزامی است.',
            'recipient_name.required' => 'نام گیرنده الزامی است.',
            'recipient_mobile.required' => 'شماره موبایل گیرنده الزامی است.',
            'recipient_mobile.regex' => 'فرمت شماره موبایل نامعتبر است (مثال: 09123456789).',
            'postal_code.required' => 'کد پستی ۱۰ رقمی الزامی است.',
            'postal_code.digits' => 'کد پستی باید دقیقاً ۱۰ رقم باشد.',
            'address_line.required' => 'نشانی پستی الزامی است.',
        ]);

        $isDefault = (bool) ($validated['is_default'] ?? false);
        $hasExisting = $user->addresses()->exists();

        // If it's the first address, make it default automatically
        if (! $hasExisting) {
            $isDefault = true;
        }

        $address = DB::transaction(function () use ($user, $validated, $isDefault) {
            if ($isDefault) {
                $user->addresses()->update(['is_default' => false]);
            }

            return $user->addresses()->create([
                ...$validated,
                'is_default' => $isDefault,
            ]);
        });

        $address->load(['province', 'city']);

        return response()->json([
            'success' => true,
            'message' => 'آدرس با موفقیت ثبت شد.',
            'data' => new AddressResource($address),
        ], 201);
    }

    /**
     * Update an address.
     */
    public function update(Request $request, Address $address): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($address->user_id !== $user->id) {
            abort(403, 'دسترسی غیرمجاز.');
        }

        $validated = $request->validate([
            'province_id' => ['sometimes', 'integer', 'exists:provinces,id'],
            'city_id' => ['sometimes', 'integer', 'exists:cities,id'],
            'recipient_name' => ['sometimes', 'string', 'max:100'],
            'recipient_mobile' => ['sometimes', 'string', 'regex:/^09[0-9]{9}$/'],
            'postal_code' => ['sometimes', 'string', 'digits:10'],
            'address_line' => ['sometimes', 'string', 'max:500'],
            'building_number' => ['nullable', 'string', 'max:20'],
            'unit' => ['nullable', 'string', 'max:20'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $isDefault = isset($validated['is_default']) ? (bool) $validated['is_default'] : null;

        DB::transaction(function () use ($user, $address, $validated, $isDefault) {
            if ($isDefault === true) {
                $user->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
            }

            $address->update($validated);
        });

        $address->load(['province', 'city']);

        return response()->json([
            'success' => true,
            'message' => 'آدرس با موفقیت به‌روزرسانی شد.',
            'data' => new AddressResource($address),
        ]);
    }

    /**
     * Delete an address.
     */
    public function destroy(Request $request, Address $address): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($address->user_id !== $user->id) {
            abort(403, 'دسترسی غیرمجاز.');
        }

        $wasDefault = $address->is_default;
        $address->delete();

        // If deleted address was default, set the latest remaining as default
        if ($wasDefault) {
            $latest = $user->addresses()->latest()->first();
            $latest?->update(['is_default' => true]);
        }

        return response()->json([
            'success' => true,
            'message' => 'آدرس با موفقیت حذف شد.',
        ]);
    }

    /**
     * Set address as default.
     */
    public function setDefault(Request $request, Address $address): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($address->user_id !== $user->id) {
            abort(403, 'دسترسی غیرمجاز.');
        }

        DB::transaction(function () use ($user, $address) {
            $user->addresses()->update(['is_default' => false]);
            $address->update(['is_default' => true]);
        });

        return response()->json([
            'success' => true,
            'message' => 'آدرس پیش‌فرض با موفقیت تنظیم شد.',
            'data' => new AddressResource($address->fresh(['province', 'city'])),
        ]);
    }
}
