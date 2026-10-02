<?php

declare(strict_types=1);

namespace Reyhan\Core\Notifications\Orders;

use Reyhan\Core\Models\Order;
use Reyhan\Core\Notifications\Channels\SmsChannel;
use Reyhan\Core\Notifications\Messages\SmsMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class OrderPaidNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Order $order,
        public ?string $trackingCode = null
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
        $tracking = $this->trackingCode
            ?? $this->order->successfulPayment?->tracking_code
            ?? $this->order->tracking_code
            ?? '';

        $content = __('messages.orders.paid_sms', [
            'order_number' => $this->order->order_number,
            'tracking_code' => $tracking,
        ]);

        return (new SmsMessage)->content($content);
    }
}
