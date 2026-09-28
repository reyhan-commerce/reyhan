<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\AbandonedCartLog;
use App\Models\Cart;
use App\Services\Sms\SmsManager;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

final class RecoverAbandonedCartsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cart:recover-abandoned {--hours=2 : Hours of cart inactivity}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Find inactive customer carts and send recovery reminder SMS';

    /**
     * Execute the console command.
     */
    public function handle(SmsManager $smsManager): int
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
                $frontendUrl = config('app.frontend_url', 'http://localhost:3000');
                $message = __('messages.cart.abandoned_reminder_sms', [
                    'name' => $user->full_name,
                    'url' => "{$frontendUrl}/cart",
                ]);

                $smsManager->send($user->mobile, $message);

                AbandonedCartLog::create([
                    'cart_id' => $cart->id,
                    'user_id' => $user->id,
                    'notified_at' => now(),
                    'status' => 'sent',
                ]);

                $sentCount++;
            } catch (\Throwable $e) {
                $this->error("Failed sending SMS to {$user->mobile}: {$e->getMessage()}");
            }
        }

        $this->info("Successfully sent {$sentCount} recovery reminders.");

        return self::SUCCESS;
    }
}
