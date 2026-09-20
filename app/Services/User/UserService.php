<?php

declare(strict_types=1);

namespace App\Services\User;

use App\Exceptions\User\UserDeactivatedException;
use App\Models\User;

class UserService
{
    /**
     * Find existing customer or register new customer by mobile, ensuring active status and verified timestamp.
     *
     * @throws UserDeactivatedException
     */
    public function findOrCreateCustomerByMobile(string $mobile): User
    {
        $user = User::firstOrCreate(
            ['mobile' => $mobile],
            ['is_active' => true]
        );

        if (!$user->is_active) {
            throw new UserDeactivatedException;
        }

        if (!$user->mobile_verified_at) {
            $user->forceFill(['mobile_verified_at' => now()])->save();
        }

        return $user;
    }

    /**
     * Authenticate customer and issue Sanctum personal access token.
     *
     * @throws UserDeactivatedException
     */
    public function authenticateWithOtp(string $mobile, string $deviceName = 'web-client'): array
    {
        $user = $this->findOrCreateCustomerByMobile($mobile);
        $token = $user->createToken($deviceName)->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }
}
