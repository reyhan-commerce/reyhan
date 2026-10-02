<?php

declare(strict_types=1);

namespace Reyhan\Core\Notifications\Auth;

use Reyhan\Core\Notifications\Channels\SmsChannel;
use Reyhan\Core\Notifications\Messages\SmsMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class SendOtpNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  array<string, string>  $tokens
     */
    public function __construct(
        public string $code,
        public array $tokens = []
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
        return (new SmsMessage)
            ->otp($this->code, $this->tokens);
    }
}
