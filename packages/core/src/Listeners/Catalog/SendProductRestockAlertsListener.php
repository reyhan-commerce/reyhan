<?php

declare(strict_types=1);

namespace Reyhan\Core\Listeners\Catalog;

use Reyhan\Core\Events\Catalog\ProductRestockedEvent;
use Reyhan\Core\Models\StockAlert;
use Reyhan\Core\Notifications\Catalog\StockAlertNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Queue\Attributes\Queue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Throwable;

#[Queue('notifications')]
final class SendProductRestockAlertsListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Ensure the listener only executes after the database transaction has committed.
     */
    public bool $afterCommit = true;

    /**
     * Handle the event.
     */
    public function handle(ProductRestockedEvent $event): void
    {
        // Only trigger if variant was out of stock and is now restocked
        if ($event->oldStock > 0 || $event->newStock <= 0) {
            return;
        }

        $variant = $event->variant;

        /** @var Collection<int, StockAlert> $alerts */
        $alerts = $variant->stockAlerts()
            ->where('status', 'pending')
            ->with(['user', 'variant.product'])
            ->get();

        foreach ($alerts as $alert) {
            try {
                $recipient = $alert->user ?? Notification::route('sms', $alert->mobile);
                $recipient->notify(new StockAlertNotification($alert));

                $alert->update([
                    'status' => 'sent',
                    'notified_at' => now(),
                ]);
            } catch (Throwable $e) {
                Log::error("Failed sending restock notification for alert #{$alert->id}: {$e->getMessage()}");
            }
        }
    }
}
