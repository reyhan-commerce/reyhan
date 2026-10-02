<?php

declare(strict_types=1);

namespace Reyhan\Core\Actions\Address;

use Reyhan\Core\Models\Address;
use Reyhan\Core\Models\User;
use Illuminate\Support\Facades\DB;

final class DeleteAddressAction
{
    public function execute(User $user, Address $address): void
    {
        DB::transaction(function () use ($user, $address): void {
            $wasDefault = $address->is_default;
            $address->delete();

            if ($wasDefault) {
                $latest = $user->addresses()->latest()->first();
                $latest?->update(['is_default' => true]);
            }
        });
    }
}
