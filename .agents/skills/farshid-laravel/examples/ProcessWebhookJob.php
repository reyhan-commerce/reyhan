<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Actions\Payments\ProcessPaymentWebhookAction;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

final class ProcessWebhookJob implements ShouldQueue
{
    use Queueable;

    /**
     * Maximum retry attempts before routing to failed_jobs.
     */
    public int $tries = 3;

    /**
     * Exponential retry backoff schedule in seconds.
     *
     * @var array<int, int>
     */
    public array $backoff = [10, 30, 60];

    /**
     * Maximum number of unhandled exceptions allowed.
     */
    public int $maxExceptions = 2;

    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        public readonly string $gateway,
        public readonly array $payload,
    ) {}

    /**
     * Execute the job: delegate directly to the dedicated Action.
     */
    public function handle(ProcessPaymentWebhookAction $action): void
    {
        $action->execute($this->gateway, $this->payload);
    }

    /**
     * Handle unrecoverable job failure.
     */
    public function failed(?Throwable $exception): void
    {
        Log::channel('critical')->error('Payment webhook processing permanently failed', [
            'gateway' => $this->gateway,
            'payload' => $this->payload,
            'exception' => $exception?->getMessage(),
        ]);
    }
}
