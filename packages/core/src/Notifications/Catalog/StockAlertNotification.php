<?php

declare(strict_types=1);

namespace Reyhan\Core\Notifications\Catalog;

use Reyhan\Core\Models\StockAlert;
use Reyhan\Core\Notifications\Channels\SmsChannel;
use Reyhan\Core\Notifications\Messages\SmsMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class StockAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public StockAlert $stockAlert
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, class-string>
     */
    public function via(mixed $notifiable): array
    {
        return [SmsChannel::class];
    }

    /**
     * Get the SMS representation of the notification.
     */
    public function toSms(mixed $notifiable): SmsMessage
    {
        $variant = $this->stockAlert->variant;
        $product = $variant?->product;

        $productName = $product?->name ?? '';
        $variantTitle = $variant?->title ?? '';

        $content = __('messages.catalog.stock_alert_sms', [
            'product' => $productName,
            'variant' => $variantTitle,
        ]);

        return (new SmsMessage)->content($content);
    }
}
