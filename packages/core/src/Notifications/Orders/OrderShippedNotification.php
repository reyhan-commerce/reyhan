<?php

declare(strict_types=1);

namespace Reyhan\Core\Notifications\Orders;

use Reyhan\Core\Models\Order;
use Reyhan\Core\Notifications\Channels\SmsChannel;
use Reyhan\Core\Notifications\Messages\SmsMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class OrderShippedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Order $order,
        public ?string $trackingCode = null,
        public ?string $trackingUrl = null
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
        $code = $this->trackingCode ?? $this->order->tracking_code ?? '';
        $url = $this->trackingUrl ?? $this->order->tracking_url;
        $urlPart = $url ? "\nرهگیری آنلاین: {$url}" : '';

        $content = __('messages.orders.shipped_sms', [
            'order_number' => $this->order->order_number,
            'tracking_code' => $code,
            'tracking_url' => $urlPart,
        ]);

        return (new SmsMessage)->content($content);
    }
}
