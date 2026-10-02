<?php

declare(strict_types=1);

namespace Reyhan\Core\Actions\Profile;

use Reyhan\Core\Data\Profile\UpdateProfileData;
use Reyhan\Core\Models\User;

final class UpdateProfileAction
{
    public function execute(User $user, UpdateProfileData $data): User
    {
        $payload = array_filter([
            'first_name' => $data->firstName,
            'last_name' => $data->lastName,
            'national_code' => $data->nationalCode,
            'email' => $data->email,
        ], fn ($value) => $value !== null);

        $user->update($payload);

        return $user->refresh();
    }
}
