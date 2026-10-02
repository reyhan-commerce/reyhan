<?php

declare(strict_types=1);

namespace Reyhan\Core\Actions\Address;

use Reyhan\Core\Data\Address\UpdateAddressData;
use Reyhan\Core\Models\Address;
use Reyhan\Core\Models\User;
use Illuminate\Support\Facades\DB;

final class UpdateAddressAction
{
    public function execute(User $user, Address $address, UpdateAddressData $data): Address
    {
        $isDefault = $data->isDefault;

        DB::transaction(function () use ($user, $address, $data, $isDefault) {
            if ($isDefault === true) {
                $user->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
            }

            $updatePayload = array_filter([
                'province_id' => $data->provinceId,
                'city_id' => $data->cityId,
                'recipient_name' => $data->recipientName,
                'recipient_mobile' => $data->recipientMobile,
                'postal_code' => $data->postalCode,
                'address_line' => $data->addressLine,
                'building_number' => $data->buildingNumber,
                'unit' => $data->unit,
                'is_default' => $data->isDefault,
            ], fn ($val) => $val !== null);

            $address->update($updatePayload);
        });

        $address->load(['province', 'city']);

        return $address;
    }
}
