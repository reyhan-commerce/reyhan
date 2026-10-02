<?php

declare(strict_types=1);

namespace Reyhan\Core\Policies;

use Reyhan\Core\Models\Address;
use Reyhan\Core\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AddressPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Address $address): bool
    {
        return $user->id === $address->user_id;
    }

    public function update(User $user, Address $address): bool
    {
        return $user->id === $address->user_id;
    }

    public function delete(User $user, Address $address): bool
    {
        return $user->id === $address->user_id;
    }
}
