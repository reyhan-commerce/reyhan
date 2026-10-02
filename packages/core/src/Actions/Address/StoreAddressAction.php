<?php

declare(strict_types=1);

namespace Reyhan\Core\Actions\Address;

use Reyhan\Core\Data\Address\StoreAddressData;
use Reyhan\Core\Models\Address;
use Reyhan\Core\Models\User;
use Illuminate\Support\Facades\DB;

final class StoreAddressAction
{
    public function execute(User $user, StoreAddressData $data): Address
    {
        $isDefault = (bool) ($data->isDefault ?? false);
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
                'province_id' => $data->provinceId,
                'city_id' => $data->cityId,
                'recipient_name' => $data->recipientName,
                'recipient_mobile' => $data->recipientMobile,
                'postal_code' => $data->postalCode,
                'address_line' => $data->addressLine,
                'building_number' => $data->buildingNumber,
                'unit' => $data->unit,
                'is_default' => $isDefault,
            ]);

            return $newAddress;
        });

        $address->load(['province', 'city']);

        return $address;
    }
}
