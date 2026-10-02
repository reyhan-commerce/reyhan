<?php

declare(strict_types=1);

namespace Reyhan\Core\Actions\Address;

use Reyhan\Core\Models\Address;
use Reyhan\Core\Models\User;
use Illuminate\Support\Facades\DB;

final class SetDefaultAddressAction
{
    public function execute(User $user, Address $address): Address
    {
        DB::transaction(function () use ($user, $address) {
            $user->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
            $address->update(['is_default' => true]);
        });

        $address->load(['province', 'city']);

        return $address;
    }
}
