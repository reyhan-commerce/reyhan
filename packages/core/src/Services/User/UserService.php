<?php

declare(strict_types=1);

namespace Reyhan\Core\Services\User;

use Reyhan\Core\Exceptions\User\UserDeactivatedException;
use Reyhan\Core\Models\User;

class UserService
{
    /**
     * Find an existing user by mobile phone.
     */
    public function findByMobile(string $mobile): ?User
    {
        return User::where('mobile', $mobile)->first();
    }

    /**
     * Create a new customer with verified mobile status.
     */
    public function createCustomer(string $mobile): User
    {
        return User::create([
            'mobile' => $mobile,
            'is_active' => true,
            'mobile_verified_at' => now(),
        ]);
    }

    /**
     * Ensure the user account is active and not suspended.
     *
     * @throws UserDeactivatedException
     */
    public function ensureIsActive(User $user): void
    {
        if (! $user->is_active) {
            throw new UserDeactivatedException;
        }
    }

    /**
     * Mark customer's mobile as verified if not previously verified.
     */
    public function markMobileAsVerified(User $user): User
    {
        if ($user->mobile_verified_at === null) {
            $user->forceFill(['mobile_verified_at' => now()])->save();
        }

        return $user;
    }

    /**
     * Orchestrate finding or creating customer and preparing verified active instance.
     *
     * @throws UserDeactivatedException
     */
    public function getOrCreateActiveCustomer(string $mobile): User
    {
        $user = $this->findByMobile($mobile);

        if (! $user) {
            return $this->createCustomer($mobile);
        }

        $this->ensureIsActive($user);
        $this->markMobileAsVerified($user);

        return $user;
    }

    /**
     * Authenticate customer and issue Sanctum personal access token.
     *
     * @return array{user: User, token: string}
     *
     * @throws UserDeactivatedException
     */
    public function authenticateWithOtp(string $mobile, string $deviceName = 'web-client'): array
    {
        $user = $this->getOrCreateActiveCustomer($mobile);
        $token = $user->createToken($deviceName)->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }
}
