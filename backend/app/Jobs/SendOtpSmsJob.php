<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\Sms\SmsManager;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendOtpSmsJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     *
     * @param  array<string, string>  $tokens
     */
    public function __construct(
        public string $mobile,
        public string $code,
        public array $tokens = []
    ) {}

    /**
     * Execute the job.
     */
    public function handle(SmsManager $smsManager): void
    {
        $smsManager->driver()->sendOtp(
            to: $this->mobile,
            code: $this->code,
            tokens: $this->tokens
        );
    }
}
