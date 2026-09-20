<?php

declare(strict_types=1);

namespace App\Notifications\Channels;

use App\Notifications\Messages\SmsMessage;
use App\Services\Sms\SmsManager;
use Illuminate\Notifications\Notification;
use RuntimeException;

class SmsChannel
{
    public function __construct(
        protected SmsManager $smsManager
    ) {}

    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     */
    public function send($notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toSms')) {
            throw new RuntimeException('Notification does not implement toSms method.');
        }

        /** @var SmsMessage|string $message */
        $message = $notification->toSms($notifiable);

        $to = null;
        if (is_object($notifiable) && method_exists($notifiable, 'routeNotificationFor')) {
            /** @var string|null $to */
            $to = $notifiable->routeNotificationFor('sms', $notification);
        }

        if ($to === null && is_object($notifiable) && isset($notifiable->mobile)) {
            /** @var string $to */
            $to = (string) $notifiable->mobile;
        }

        if ($message instanceof SmsMessage) {
            $recipient = $message->to ?? $to;

            if ($recipient === null || $recipient === '') {
                return;
            }

            if ($message->isOtp()) {
                $this->smsManager->driver()->sendOtp(
                    to: $recipient,
                    code: (string) $message->otpCode,
                    tokens: $message->tokens
                );
            } else {
                $this->smsManager->driver()->send(
                    to: $recipient,
                    message: (string) ($message->content ?? '')
                );
            }
        } elseif (is_string($message)) {
            if ($to === null || $to === '') {
                return;
            }

            $this->smsManager->driver()->send(
                to: $to,
                message: $message
            );
        }
    }
}
