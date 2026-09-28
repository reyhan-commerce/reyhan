<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\StockAlert;
use App\Services\Sms\Contracts\SmsDriverInterface;
use App\Services\Sms\SmsManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendStockAlertSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public StockAlert $stockAlert,
    ) {}

    public function handle(SmsManager $smsManager): void
    {
        if ($this->stockAlert->status !== 'pending') {
            return;
        }

        $variant = $this->stockAlert->variant;
        $product = $variant->product;

        if (! $product) {
            return;
        }

        $message = "کاربر گرامی، کالای «{$product->name}» ({$variant->title}) در فروشگاه موجود شد. جهت مشاهده و خرید سریع به سایت مراجعه فرمایید.";

        try {
            /** @var SmsDriverInterface $driver */
            $driver = $smsManager->driver();
            $driver->send(
                $this->stockAlert->mobile,
                $message
            );

            $this->stockAlert->update([
                'status' => 'sent',
                'notified_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error("Failed to send stock alert SMS to {$this->stockAlert->mobile}: {$e->getMessage()}");
        }
    }
}
