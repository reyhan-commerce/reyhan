<?php

declare(strict_types=1);

namespace Reyhan\Core\Data\Profile;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;

final class UpdateProfileData extends Data
{
    public function __construct(
        #[MapName('first_name')]
        public ?string $firstName = null,

        #[MapName('last_name')]
        public ?string $lastName = null,

        #[MapName('national_code')]
        public ?string $nationalCode = null,

        public ?string $email = null,
    ) {}
}
