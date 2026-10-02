<?php

declare(strict_types=1);

namespace Reyhan\Core\Notifications\Marketing;

use Reyhan\Core\Models\Cart;
use Reyhan\Core\Notifications\Channels\SmsChannel;
use Reyhan\Core\Notifications\Messages\SmsMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class AbandonedCartReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Cart $cart,
        public ?string $recoveryUrl = null
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
        $frontendUrl = rtrim((string) config('app.frontend_url', 'http://localhost:3000'), '/');
        $url = $this->recoveryUrl ?? "{$frontendUrl}/cart";

        $name = is_object($notifiable) && isset($notifiable->full_name)
            ? (string) $notifiable->full_name
            : ($this->cart->user?->full_name ?? __('Dear Customer'));

        $content = __('messages.cart.abandoned_reminder_sms', [
            'name' => $name,
            'url' => $url,
        ]);

        return (new SmsMessage)->content($content);
    }
}
