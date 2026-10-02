<?php

declare(strict_types=1);

namespace Reyhan\Core\Services\Payment\DTOs;

readonly class PaymentVerifyResult
{
    /**
     * @param  array<string, mixed>  $rawResponse
     */
    public function __construct(
        public bool $success,
        public ?string $referenceId = null,
        public ?string $trackingCode = null,
        public ?string $cardPan = null,
        public ?string $errorMessage = null,
        public array $rawResponse = [],
    ) {}
}
