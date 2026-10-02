<?php

declare(strict_types=1);

namespace Reyhan\Core\Notifications\Channels;

use Reyhan\Core\Notifications\Messages\SmsMessage;
use Reyhan\Core\Services\Sms\Contracts\SmsDriverInterface;
use Reyhan\Core\Services\Sms\SmsManager;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class SmsChannel
{
    public function __construct(
        protected SmsManager $smsManager
    ) {}

    /**
     * Send the given notification with automatic failover driver support.
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

            $this->sendWithFailover(function (SmsDriverInterface $driver) use ($recipient, $message) {
                if ($message->isOtp()) {
                    $driver->sendOtp(
                        to: $recipient,
                        code: (string) $message->otpCode,
                        tokens: $message->tokens
                    );
                } else {
                    $driver->send(
                        to: $recipient,
                        message: (string) ($message->content ?? '')
                    );
                }
            });
        } elseif (is_string($message)) {
            if ($to === null || $to === '') {
                return;
            }

            $this->sendWithFailover(function (SmsDriverInterface $driver) use ($to, $message) {
                $driver->send(
                    to: $to,
                    message: $message
                );
            });
        }
    }

    /**
     * Attempt sending via active driver, falling back to other configured drivers if failure occurs.
     *
     * @param  callable(SmsDriverInterface): void  $callback
     */
    protected function sendWithFailover(callable $callback): void
    {
        $defaultDriver = $this->smsManager->getDefaultDriver();
        $fallbackChain = array_unique([$defaultDriver, 'kavenegar', 'farazsms', 'ghasedak', 'log']);

        $lastException = null;

        foreach ($fallbackChain as $driverName) {
            try {
                /** @var SmsDriverInterface $driver */
                $driver = $this->smsManager->driver($driverName);
                $callback($driver);

                if ($driverName !== $defaultDriver) {
                    Log::warning("Primary SMS driver [{$defaultDriver}] failed; successfully sent via fallback driver [{$driverName}].");
                }

                return;
            } catch (Throwable $e) {
                $lastException = $e;
                Log::error("SMS sending failed with driver [{$driverName}]: {$e->getMessage()}");
            }
        }

        if ($lastException !== null) {
            Log::critical('All SMS drivers failed in fallback chain.', [
                'error' => $lastException->getMessage(),
            ]);
        }
    }
}
