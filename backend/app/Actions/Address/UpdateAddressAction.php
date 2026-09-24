<?php

declare(strict_types=1);

namespace App\Actions\Address;

use App\Models\Address;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class UpdateAddressAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(User $user, Address $address, array $data): Address
    {
        $isDefault = isset($data['is_default']) ? (bool) $data['is_default'] : null;

        DB::transaction(function () use ($user, $address, $data, $isDefault) {
            if ($isDefault === true) {
                $user->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
            }

            $address->update($data);
        });

        $address->load(['province', 'city']);

        return $address;
    }
}
