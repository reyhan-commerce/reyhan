<?php

declare(strict_types=1);

namespace Reyhan\Core\Events\Auth;

use Reyhan\Core\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class CustomerRegistered
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public User $user,
    ) {}
}
