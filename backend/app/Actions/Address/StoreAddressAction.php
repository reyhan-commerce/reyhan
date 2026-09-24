<?php

declare(strict_types=1);

namespace App\Actions\Address;

use App\Models\Address;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class StoreAddressAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(User $user, array $data): Address
    {
        $isDefault = (bool) ($data['is_default'] ?? false);
        $hasExisting = $user->addresses()->exists();

        if (! $hasExisting) {
            $isDefault = true;
        }

        /** @var Address $address */
        $address = DB::transaction(function () use ($user, $data, $isDefault): Address {
            if ($isDefault) {
                $user->addresses()->update(['is_default' => false]);
            }

            /** @var Address $newAddress */
            $newAddress = $user->addresses()->create([
                ...$data,
                'is_default' => $isDefault,
            ]);

            return $newAddress;
        });

        $address->load(['province', 'city']);

        return $address;
    }
}
