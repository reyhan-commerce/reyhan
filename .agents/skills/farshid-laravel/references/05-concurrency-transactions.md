# 05 — Concurrency, Transactions & System Resilience

## 1. Database Transactions

### 1.1 Transaction Boundaries
Place `DB::transaction(...)` at the **orchestration boundary** (the top-level Action):

```text
CreateOrderAction (Opens DB::transaction)
   ├── CreateOrderItems
   ├── ReserveInventory
   └── Dispatch OrderCreated Event
```
- **Child Actions should remain composable**: Do not blindly wrap every internal helper Action in its own transaction if it may be called inside a parent transaction.

### 1.2 The Golden Rule of External APIs and Transactions
> ⚠️ **NEVER hold a database transaction open while waiting for an external HTTP request.**

```text
❌ ANTI-PATTERN:
BEGIN TRANSACTION
  ↓ (Database connection locked)
Call Stripe API over HTTP (Takes 800ms - 3000ms, or times out!)
  ↓
Update Database
COMMIT TRANSACTION
```
Holding database locks open during network requests destroys database connection pools and causes widespread cascading timeouts.

### Correct Payment Workflow Pattern:
```text
1. Prepare pending record in DB (COMMIT)
2. Call Stripe API outside any DB transaction
3. On Stripe response / webhook:
   BEGIN TRANSACTION
   Update order status to PAID
   Record payment reference
   COMMIT TRANSACTION
```

---

## 2. Concurrency & Race Conditions
Identify potential race conditions before writing mutations on shared resources (balances, inventory, seat reservations).

### Concurrency Mechanisms:
1. **Pessimistic Locking (`lockForUpdate`)**:
   Use within an active transaction when inspecting and updating a record:
   ```php
   $wallet = Wallet::where('user_id', $user->id)->lockForUpdate()->first();
   if ($wallet->balance < $amount) {
       throw new InsufficientBalanceException();
   }
   $wallet->decrement('balance', $amount);
   ```
2. **Atomic Cache Locks (`Cache::lock`)**:
   Ideal for distributed operations across multiple servers or queue workers:
   ```php
   $lock = Cache::lock("process-order:{$orderId}", 10);
   if (! $lock->get()) {
       throw new OperationAlreadyInProgressException();
   }
   try {
       // execute critical section
   } finally {
       $lock->release();
   }
   ```
3. **Database Unique Constraints**:
   The ultimate defense against duplicate record creation under race conditions.

---

## 3. Idempotency

### 3.1 Business-Level Idempotency
An Action must be safe to execute multiple times without causing duplicate business side-effects:

```php
final class MarkOrderAsPaidAction
{
    public function execute(Order $order, string $paymentReference): void
    {
        // Business Invariant: If already paid, exit cleanly
        if ($order->isPaid()) {
            return;
        }

        $order->update([
            'status' => OrderStatusEnum::PAID,
            'payment_reference' => $paymentReference,
            'paid_at' => now(),
        ]);
    }
}
```

### 3.2 External Idempotency Keys
When calling third-party APIs (Stripe, payment providers), pass unique idempotency keys (e.g. `order_{$order->id}_{$attempt}`) so the gateway never charges a customer twice on network retries.

---

## 4. Background Jobs & Queue Resilience

### 4.1 Jobs Own Infrastructure, Actions Own Business Logic
Jobs should remain thin wrappers that delegate directly to Actions:

```php
namespace App\Jobs;

use App\Actions\Videos\ProcessVideoAction;
use App\Models\Video;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

final class ProcessVideoJob implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var array<int, int>
     */
    public array $backoff = [10, 30, 60];

    /**
     * The maximum number of unhandled exceptions to allow before failing.
     */
    public int $maxExceptions = 2;

    public function __construct(
        public readonly Video $video,
    ) {}

    public function handle(ProcessVideoAction $action): void
    {
        $action->execute($this->video);
    }

    /**
     * Handle a job failure.
     */
    public function failed(?Throwable $exception): void
    {
        logger()->error('Video processing permanently failed', [
            'video_id' => $this->video->id,
            'error' => $exception?->getMessage(),
        ]);

        $this->video->update(['status' => VideoStatusEnum::FAILED]);
    }
}
```

---

## 5. Webhooks & Asynchronous Processing
When receiving webhooks from third-party services (Stripe, GitHub, payment gateways):
1. **Validate Signature**: Verify authenticity using cryptographic secret.
2. **Respond Immediately**: Return HTTP `200 OK` or `202 Accepted` within milliseconds to prevent timeouts.
3. **Dispatch to Queue**: Hand off the payload to an idempotent background Job for actual processing.

```php
final class StripeWebhookController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $this->verifyWebhookSignature($request);

        ProcessStripeWebhookJob::dispatch($request->all());

        return response()->noContent();
    }
}
```

---

## 6. Events & Listeners
- **Events are for side-effects and decoupled reactions**: Notifications, sending emails, updating analytics, pushing telemetry.
- **Do not hide core business logic in Listeners**: The primary business workflow should be explicitly visible in the Action, not hidden across multiple invisible listener classes.
