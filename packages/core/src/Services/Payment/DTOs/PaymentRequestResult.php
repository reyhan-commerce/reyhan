<?php

declare(strict_types=1);

namespace Reyhan\Core\Services\Payment\DTOs;

readonly class PaymentRequestResult
{
    public function __construct(
        public bool $success,
        public ?string $authority = null,
        public ?string $redirectUrl = null,
        public ?string $errorMessage = null,
    ) {}
}
