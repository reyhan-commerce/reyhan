<?php

declare(strict_types=1);

namespace Reyhan\Core\Console\Commands;

use Reyhan\Core\Models\AbandonedCartLog;
use Reyhan\Core\Models\Cart;
use Reyhan\Core\Notifications\Marketing\AbandonedCartReminderNotification;
use Carbon\Carbon;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

#[Signature('cart:recover-abandoned {--hours=2 : Hours of cart inactivity}')]
#[Description('Find inactive customer carts and send recovery reminder SMS')]
final class RecoverAbandonedCartsCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $hours = (int) $this->option('hours');
        $cutoff = Carbon::now()->subHours($hours);

        /** @var Collection<int, Cart> $abandonedCarts */
        $abandonedCarts = Cart::query()
            ->whereNotNull('user_id')
            ->where('updated_at', '<=', $cutoff)
            ->whereHas('items')
            ->whereDoesntHave('user.orders', function (Builder $query) use ($cutoff): void {
                $query->where('created_at', '>=', $cutoff);
            })
            ->whereDoesntHave('user.abandonedCartLogs', function (Builder $logQuery): void {
                $logQuery->where('notified_at', '>=', now()->subHours(24));
            })
            ->with(['user', 'items.variant.product'])
            ->get();

        $this->info("Found {$abandonedCarts->count()} abandoned carts to process.");

        $sentCount = 0;
        foreach ($abandonedCarts as $cart) {
            $user = $cart->user;
            if (! $user || ! $user->mobile) {
                continue;
            }

            try {
                $user->notify(new AbandonedCartReminderNotification($cart));

                AbandonedCartLog::create([
                    'cart_id' => $cart->id,
                    'user_id' => $user->id,
                    'notified_at' => now(),
                    'status' => 'sent',
                ]);

                $sentCount++;
            } catch (\Throwable $e) {
                $this->error("Failed sending notification to {$user->mobile}: {$e->getMessage()}");
            }
        }

        $this->info("Successfully sent {$sentCount} recovery reminders.");

        return self::SUCCESS;
    }
}
