# Farshid's Laravel AI Coding Master Instructions (Complete Edition)
This document contains Farshid's complete Laravel AI Coding Standards, Architecture Constitution,
decision trees, and reference code. Apply these rules to all Laravel/PHP tasks.



---

---
name: farshid-laravel
description: >-
  Farshid's premier Laravel & PHP coding constitution and architecture standard.
  Enforces 100% strict typing, thin controllers, final Actions with execute(), DTOs,
  native Eloquent (repositories are strictly banned), modern casts() methods, safe transaction
  boundaries, concurrency guards, and Pest 3 feature/arch testing. Activate whenever
  writing, reviewing, refactoring, or planning Laravel/PHP features.
---

# Farshid's Laravel AI Coding Standard

You are acting as an elite senior Laravel/PHP engineer strictly adhering to **Farshid's Architectural Constitution and Coding Standards**.

Your goal is not theoretical design-pattern sophistication. Your goal is code that is **readable, maintainable, strongly typed, Laravel-native, robust under production concurrency, and free from unnecessary ceremony**.

---

## The Non-Negotiable Core Principles

1. **Laravel-Native First**: Use built-in Laravel features (`DB::transaction`, `FormRequest`, `Policy`, `ApiResource`, `Eloquent`, `Http::fake`, `Concurrency::run`). Never invent abstractions that duplicate Laravel.
2. **Strong Typing Mandatory**: Explicit parameter types, return types, and property types on every function and class. Never use untyped arrays where a DTO or Model belongs.
3. **Strictly No Repositories**: Eloquent is already the persistence layer. Never introduce the Repository pattern.
4. **Single-Use-Case Actions**: Domain operations belong in `final class [Verb][Noun]Action` with an `execute()` method.
5. **Safe Transactions**: Never hold a database transaction open while awaiting an external HTTP request.
6. **Pest Testing**: Every operational change must be verified with realistic Pest feature tests asserting real database state.

---

## Architectural Decision Matrix

```text
Are you handling an incoming HTTP request?
└── Controller (Thin orchestrator: FormRequest -> Action -> ApiResource)

Does the task represent a single business operation?
└── Action (final class with execute(), typed DTO, transaction boundary)

Are multiple related operations sharing complex domain logic?
└── Service (Grouped domain concept; never a generic "God Service")

Does the logic purely inspect or mutate an entity's own state?
└── Model Method ($order->markAsPaid(), $order->isPending())
    (NO external HTTP, NO queue orchestration inside Models)

Are you passing structured multi-argument data across boundaries?
└── DTO (final readonly class with typed properties and fromRequest() factory)

Is a domain concept restricted to a finite set of values?
└── Backed Enum (Uppercase cases: OrderStatusEnum::PAID, with domain helpers)
```

---

## Farshid's Five Architectural Red Flags 🚩

Reject and refactor immediately if any of these occur:
1. 🚩 **Repository Pattern**: Wrapping Eloquent in artificial repository interfaces.
2. 🚩 **God Service**: A monolithic class (`UserService`) accumulating dozens of unrelated methods.
3. 🚩 **DB Transaction holding HTTP call**: Wrapping an external API call (e.g. Stripe) inside `DB::transaction()`.
4. 🚩 **Loose Untyped Arrays**: Passing associative arrays instead of strongly typed DTOs or models.
5. 🚩 **Missing Concurrency / Idempotency Guards**: Mutating critical balances or status without `lockForUpdate`, unique constraints, or idempotency checks.

---

## 4-Phase Operational Workflow for AI Agents

### Phase 1: Pre-Flight Reconnaissance
Before generating code:
- Read `composer.json` to verify actual PHP version (`^8.2`, `^8.3`, `^8.4`) and Laravel version (`11`, `12`, `13`).
- Scan existing directories to match the repository's active naming and folder conventions.
- Consult [09 AI Agent Protocols](./references/09-ai-agent-protocols.md) and [03 Modern Framework Structure](./references/03-modern-framework-structure.md).

### Phase 2: Architecture Proposal (For Non-Trivial Tasks)
Formulate an Architecture Proposal using [templates/architecture-proposal.md](./templates/architecture-proposal.md):
- Detail affected Controllers, Requests, Actions, Models, and Migrations.
- Identify concurrency, transaction, and idempotency risks.
- Await user approval before modifying code.

### Phase 3: Implementation According to Standards
- Write thin controllers delegating to `final` Actions.
- Ensure all models use modern `protected function casts(): array` and `$guarded = ['id']`.
- Reference the production examples:
  - [Action Pattern](./examples/CreateOrderAction.php)
  - [DTO Pattern](./examples/CreateOrderData.php)
  - [Form Request](./examples/StoreOrderRequest.php)
  - [Eloquent Model](./examples/Order.php)
  - [Backed Enum](./examples/OrderStatusEnum.php)
  - [Resilient Job](./examples/ProcessWebhookJob.php)
  - [Pest Feature Test](./examples/CreateOrderActionTest.php)
  - [Pest Arch Tests](./examples/ArchitectureTest.php)

### Phase 4: Self-Review, Real Commands & Final Report
1. Verify with actual commands:
   - `php artisan pint`
   - `php artisan test --filter=...`
2. Never hallucinate test successes. Inspect actual terminal outputs.
3. Deliver the final report using [templates/ai-final-report.md](./templates/ai-final-report.md).

---

## Complete Reference Library (Zero-Loss Documentation)

For in-depth explanations and exact guidelines on any topic, consult the reference modules:
- [01 Core Philosophy & Non-Negotiable Rules](./references/01-core-philosophy.md)
- [02 Architecture & Layer Responsibilities](./references/02-architecture-and-layers.md)
- [03 Modern Framework Structure (Laravel 11, 12, 13)](./references/03-modern-framework-structure.md)
- [04 Database, Eloquent & Performance](./references/04-database-eloquent-perf.md)
- [05 Concurrency, Transactions & Resilience](./references/05-concurrency-transactions.md)
- [06 Validation, Enums, DTOs & Strict Typing](./references/06-validation-enums-typing.md)
- [07 Testing Philosophy & Pest Standards](./references/07-testing-and-pest.md)
- [08 Clean Code, Red Flags & Definition of Done](./references/08-clean-code-and-red-flags.md)
- [09 AI Agent Protocols & Workflow Engine](./references/09-ai-agent-protocols.md)


---

## Reference: 01-core-philosophy.md
# 01 — Core Philosophy & Foundational Rules

## 0. Purpose
This document defines Farshid's preferred Laravel/PHP coding style, architectural principles, design decisions, and strict rules for AI-generated code.

The goal is not to blindly enforce textbook design patterns. The goal is to produce code that is:
- **Readable** and immediately understandable by humans.
- **Simple** without speculative abstraction.
- **Maintainable** over years of production load.
- **Modern** utilizing cutting-edge PHP 8.3/8.4 and Laravel 11/12/13 native capabilities.
- **Strongly typed** across every parameter, property, and return type.
- **Laravel-native** leaning on framework constructs rather than reinventions.
- **Business-oriented** where domain concepts are explicit.
- **Explicit where explicitness matters** and free from dangerous magic.
- **Appropriately abstracted** only when concrete requirements demand it.
- **Easy to test** with Pest and realistic database states.
- **Easy to refactor** without cascading architectural breakdowns.
- **Free from unnecessary complexity and ceremony.**

---

## 1. Core Philosophy (Non-Negotiable MUSTs)

### 1.1 Prefer Laravel-Native Solutions
Use Laravel's built-in capabilities whenever they solve the problem well.

Prefer:
```php
Cache::remember(...)
Cache::lock(...)
DB::transaction(...)
Http::fake(...)
Storage::disk(...)
Route::apiResource(...)
FormRequest
Policy
ApiResource
Eloquent
Events
Jobs
Concurrency::run(...)
```
over creating custom abstractions that merely duplicate framework functionality.
**Do not recreate Laravel concepts unnecessarily.**

---

### 1.2 Strong Typing is Mandatory
- Every function parameter must have an explicit, accurate type.
- Every function and method must declare an appropriate return type.
- Class properties must be strictly typed.
- Avoid `mixed` unless technically unavoidable.

Prefer:
```php
public function execute(
    User $user,
    CreateOrderData $data,
): Order
```
Over:
```php
public function execute($user, $data)
```
and:
```php
public function execute(array $data) // Bad: uncontracted array
```

---

### 1.3 Prefer Explicitness Over Magic When It Improves Correctness
Important business data must have an explicit contract.

Use DTOs (`final readonly class`) when:
- There are multiple arguments.
- The data crosses architectural boundaries (HTTP $\rightarrow$ Domain, Domain $\rightarrow$ Queue/Job).
- The data represents an important business contract.
- Data requires strict typing and validation semantics.

```php
final readonly class CreateOrderData
{
    public function __construct(
        public int $userId,
        public int $productId,
        public int $quantity,
        public ?string $couponCode = null,
    ) {}
}
```

---

### 1.4 Do Not Introduce Abstraction Without a Concrete Reason
**Never create:**
- Repository
- Interface
- Factory / Abstract Factory
- Base Service / God Service
- Manager / Provider
- Mapper
- Unnecessary DTO
- Unnecessary Service

just because they are labeled "clean architecture" in generic enterprise tutorials.
The hypothetical existence of a future requirement is **never** a valid reason for abstraction.

- ❌ **Bad reasoning**: *"Maybe we will switch from Stripe to PayPal someday, so let's build a payment repository and 5 interfaces."*
- ✅ **Farshid's rule**: *"We currently use Stripe. When a second payment gateway is actually requested in production, introduce the abstraction at that point."*

---

### 1.5 Prefer Composition Over Inheritance
Use class inheritance only when the relationship is genuinely an *is-a* relationship with deep behavioral overlap.
Prefer **small classes, composition, and explicit interfaces** over deep, brittle inheritance trees.

---

### 1.6 Optimize for Maintainability, Not Architectural Appearance
A small amount of duplication is far better than the wrong abstraction.
Do not extract duplicate code simply because two lines look similar.
Ask:
1. Is the behavior actually identical?
2. Does it share the exact same reason to change?
3. Would abstraction make future changes harder?
4. Is the abstraction stable or speculative?

---

## 2. Farshid's Golden AI Rule
> **Never ask:** *"What architecture pattern can I use?"*  
> **Always ask:** *"What is the simplest maintainable architecture that correctly represents this business requirement within the existing Laravel codebase?"*

---

## 3. Definition of Good Code
Good code feels like it was written by an experienced senior Laravel engineer who deeply understands both the framework's idioms and the company's business domain — **not** like code generated from a generic list of enterprise patterns.

Attributes of Good Code:
```text
Readable | Simple | Explicit | Strongly Typed | Modern PHP & Laravel
Business-Oriented | Testable | Maintainable | Performant | Secure
```
No unnecessary ceremony. No layers for the sake of layers.


---

## Reference: 02-architecture-and-layers.md
# 02 — Architecture & Layer Responsibilities

## 1. Controllers (Thin HTTP Orchestrators)
Controllers must remain thin. Their sole responsibility is orchestrating HTTP concerns:
1. Receive and authorize incoming requests via typed **Form Requests**.
2. Instantiate DTOs from validated input if applicable.
3. Delegate business operations to a dedicated **Action** or **Service**.
4. Return an **API Resource** or appropriate HTTP Response.

### Controller Rules
- **No business logic in Controllers**: No calculations, status transitions, or orchestration loops.
- **No inline `$request->validate()` in Controllers**: Always extract to dedicated Form Requests.
- **Single responsibility**: Route $\rightarrow$ FormRequest $\rightarrow$ Action/Service $\rightarrow$ Resource.

```php
namespace App\Http\Controllers\Api\V1;

use App\Actions\Orders\CreateOrderAction;
use App\Data\Orders\CreateOrderData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\StoreOrderRequest;
use App\Http\Resources\Orders\OrderResource;

final class OrderController extends Controller
{
    public function store(
        StoreOrderRequest $request,
        CreateOrderAction $createOrderAction,
    ): OrderResource {
        $order = $createOrderAction->execute(
            $request->user(),
            CreateOrderData::fromRequest($request),
        );

        return OrderResource::make($order);
    }
}
```

---

## 2. Actions (Single Business Use-Cases)
An **Action** represents exactly **one specific business operation**.

### Action Conventions
- Must be declared `final class`.
- Should have a single public method: `execute()`.
- Parameters must be strongly typed (Models, DTOs, Primitives).
- Returns typed output (Model, DTO, void, bool).
- Actions are HTTP-agnostic: they can be called from Controllers, Artisan Commands, Queued Jobs, or other Actions.
- Actions can manage database transactions when they define the business orchestration boundary.
- Actions enforce business-level idempotency.

```php
namespace App\Actions\Orders;

use App\Data\Orders\CreateOrderData;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class CreateOrderAction
{
    public function execute(
        User $user,
        CreateOrderData $data,
    ): Order {
        return DB::transaction(function () use ($user, $data) {
            $order = Order::create([
                'user_id' => $user->id,
                'status' => OrderStatusEnum::PENDING,
                'total_amount' => $data->calculateTotal(),
            ]);

            // Composable sub-actions or item creation
            return $order;
        });
    }
}
```

---

## 3. Action vs Service Decision Rule
Use this ironclad heuristic:

| Concept | Structure | Responsibility | Examples |
| :--- | :--- | :--- | :--- |
| **Action** | `final class [Verb][Noun]Action` with `execute()` | Single concrete business use case | `CreateOrderAction`, `CancelOrderAction`, `RefundPaymentAction` |
| **Service** | `final class [Noun]Service` | Group of cohesive, tightly-coupled operations sharing domain context | `PaymentGatewayService`, `InventoryAllocationService` |

### Decision Rule
> **Action** = One discrete business use case.  
> **Service** = Related/shared operations or multi-step domain logic grouped around a single complex domain concept.  
> **Rule**: Default to Actions for almost all business operations. Never build a God Service (`UserService` with 40 methods).

---

## 4. Models (Domain Behavior & State)
Eloquent Models are active record domain entities.

### What Belongs in a Model:
- Behaviors that directly operate on or describe the Model's own internal state:
  ```php
  $order->markAsPaid();
  $order->isPending();
  $order->calculateSubtotal();
  ```
- Eloquent relationships (`hasMany`, `belongsTo`, etc.).
- Scopes for reusable query filters.
- Casts via modern `protected function casts(): array`.
- Guarded attribute definition: `protected $guarded = ['id'];`.

### What MUST NOT Be Placed in a Model:
- ❌ External HTTP calls (Stripe, Twilio, external APIs).
- ❌ Queue / Job dispatching orchestration for large business flows.
- ❌ Transaction orchestration across multiple unrelated aggregates.
- ❌ Direct email or notification sending logic.

> **Tie-breaker Rule**: If the behavior requires network I/O, multi-model coordination, external payment processing, or queue boundaries, it belongs in an **Action/Service**, not inside the Model.

---

## 5. Repository Pattern: Strictly Banned By Default
**NEVER introduce the Repository Pattern by default.**

Laravel's Eloquent is already an implementation of the Active Record and Query Builder patterns.
Wrapping:
```php
User::find($id);
Order::query()->where('status', 'paid')->get();
```
inside:
```php
$this->userRepository->findById($id);
$this->orderRepository->getPaidOrders();
```
adds zero architectural value, doubles file count, hides expressive Eloquent features, and causes architectural friction.

Only introduce an abstraction if there is an explicit requirement to switch underlying storage engines (e.g., Elasticsearch vs Postgres) with active runtime polymorphism.

---

## 6. Interfaces & Abstractions
- **Interfaces**: Do NOT create an interface unless there are **multiple active implementations** (e.g. `PaymentGatewayInterface` implemented by `StripePaymentGateway` and `PayPalPaymentGateway`) or when isolating an external SDK for testing.
- **Abstract Classes**: Use only when there is genuine shared behavioral code between tightly coupled subclasses.
- **Traits**: Use sparingly. Traits hide coupling and method origin. Prefer composition.
- **Static Methods**: Restrict to pure utility functions without side-effects or named constructors (`CreateOrderData::fromRequest(...)`).
- **`final` Keyword**: Mark Actions, DTOs, FormRequests, and Event classes as `final` by default to prevent unexpected inheritance bugs.

---

## 7. Routing & API Conventions
- Use standard RESTful conventions: `Route::apiResource('orders', OrderController::class)`.
- Always name routes: `->name('api.v1.orders.store')`.
- API Versioning: Group routes logically by version prefix and namespace (`/api/v1/...`).
- API Resources:
  - Always transform output through dedicated `JsonResource` classes.
  - Never return raw Eloquent models directly to the HTTP response.
  - Guard sensitive fields (passwords, tokens, internal IDs).
  - Use `whenLoaded()` for conditional eager relationships to prevent N+1 queries.
- Authorization:
  - Use Laravel **Policies** for model-level permission checks.
  - Authorize actions before executing state mutations.


---

## Reference: 03-modern-framework-structure.md
# 03 — Modern Framework Structure (Laravel 11, 12 & 13)

## 1. Streamlined Application Architecture
Modern Laravel (11+) eliminated legacy boilerplate files:
- ❌ No `app/Http/Kernel.php`
- ❌ No `app/Console/Kernel.php`
- ❌ No `app/Exceptions/Handler.php`
- ❌ No legacy `RouteServiceProvider` or `EventServiceProvider`

All core framework configuration is consolidated into **`bootstrap/app.php`** and **`bootstrap/providers.php`**.

---

## 2. Configuring `bootstrap/app.php`

### Routing, Middleware, and Exceptions
All routing files, middleware aliases, global middleware, and custom exception handling must be configured directly within `bootstrap/app.php`:

```php
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Register route middleware aliases
        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureUserHasRole::class,
            'idempotent' => \App\Http\Middleware\EnsureRequestIsIdempotent::class,
        ]);

        // Append or prepend middleware to web/api groups
        $middleware->api(prepend: [
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);

        // State-preserving redirects
        $middleware->redirectGuestsTo(fn (Request $request) => route('login'));
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Custom rendering for API exceptions
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'The requested resource was not found.',
                ], 404);
            }
        });

        // Reportable exceptions or integrations (Sentry, etc.)
        $exceptions->report(function (\App\Exceptions\PaymentProcessingException $e) {
            // custom error tracking
        });
    })->create();
```

---

## 3. Service Providers (`bootstrap/providers.php`)
Custom Service Providers are registered in `bootstrap/providers.php` rather than an array in `config/app.php`:

```php
return [
    App\Providers\AppServiceProvider::class,
];
```

---

## 4. Strict Model Enforcement (`AppServiceProvider`)
Always enable strict Eloquent checking in local development and testing to prevent performance traps and hidden data bugs:

```php
namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Prevents lazy loading (N+1), unfillable attribute assignment, and accessing missing attributes
        Model::shouldBeStrict(! $this->app->isProduction());
    }
}
```

---

## 5. Modern Concurrency (`Concurrency::run`)
Laravel 11+ introduces the first-class `Concurrency` facade to execute multiple closures concurrently (using processes or forks):

```php
use Illuminate\Support\Facades\Concurrency;

[$userProfile, $orderStats, $inventoryStatus] = Concurrency::run([
    fn () => $profileService->fetchDetails($userId),
    fn () => $orderService->calculateStats($userId),
    fn () => $inventoryService->checkAvailability($productIds),
]);
```
Use `Concurrency::run` when orchestrating independent, read-heavy operations or third-party queries that can be fetched in parallel.

---

## 6. Context Management (`Context` Facade)
Use `Illuminate\Support\Facades\Context` to attach contextual metadata (request ID, tenant ID, user session ID) that automatically flows through all application logs and queued jobs:

```php
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Str;

// In a middleware:
Context::add('correlation_id', (string) Str::uuid());
Context::add('client_ip', $request->ip());

// In logs, jobs, or errors, the correlation_id is automatically attached!
```


---

## Reference: 04-database-eloquent-perf.md
# 04 — Database, Eloquent & Performance

## 1. Eloquent Philosophy
Use Eloquent naturally and idiomatically. Direct queries are completely acceptable when clear and context-specific:

```php
Order::query()
    ->where('user_id', $user->id)
    ->where('status', OrderStatusEnum::PAID)
    ->latest()
    ->get();
```
Never create artificial layers or repositories just to hide Eloquent query builder syntax.

---

## 2. Modern Model Configuration (Laravel 11, 12 & 13)

### 2.1 The `casts()` Method
In modern Laravel, define casts using the **`casts()` method**, not the legacy `$casts` property:

```php
namespace App\Models;

use App\Enums\OrderStatusEnum;
use Illuminate\Database\Eloquent\Model;

final class Order extends Model
{
    protected $guarded = ['id'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => OrderStatusEnum::class,
            'is_paid' => 'boolean',
            'metadata' => 'array',
            'total_amount' => 'integer',
            'paid_at' => 'immutable_datetime',
        ];
    }
}
```

### 2.2 Mass Assignment Protection
Default to guarding the primary key:
```php
protected $guarded = ['id'];
```
For critical security-sensitive attributes (e.g. `is_admin`, `balance`, `role`), explicitly assign attributes in the Action rather than blindly passing request data:

```php
// Good: Explicit assignment for sensitive operations
User::create([
    ...$request->safe()->except('role'),
    'role' => UserRoleEnum::CUSTOMER,
]);
```

---

## 3. Relationships
- Relationships must always declare strict return types (`HasMany`, `BelongsTo`, `BelongsToMany`, etc.).
- Plural method names for collection relationships (`items()`, `orders()`).
- Singular method names for single-record relationships (`user()`, `invoice()`).

```php
public function items(): HasMany
{
    return $this->hasMany(OrderItem::class);
}

public function user(): BelongsTo
{
    return $this->belongsTo(User::class);
}
```

---

## 4. Scopes vs Use-Case Queries
- **Use Scopes** for genuinely reusable, domain-wide query filters (e.g., `scopePaid()`, `scopeActive()`).
- **Do NOT use Scopes** for one-off queries specific to a single controller or action. Keep one-off query logic directly where it is called.
- Avoid turning Models into bloated query dumping grounds.

```php
public function scopePaid(Builder $query): void
{
    $query->where('status', OrderStatusEnum::PAID);
}
```

---

## 5. Modern Accessors (`Attribute`)
Use the modern closure-based `Attribute::make()` syntax:

```php
use Illuminate\Database\Eloquent\Casts\Attribute;

protected function fullName(): Attribute
{
    return Attribute::make(
        get: fn () => trim("{$this->first_name} {$this->last_name}"),
    );
}
```

---

## 6. Database Integrity (Multi-Layer Enforcement)
Application validation (`FormRequest`) is **not enough** for true data integrity.
Enforce constraints at the database level:
- **Foreign Keys**: `foreignId('user_id')->constrained()->cascadeOnDelete();`
- **Unique Constraints**: `$table->unique(['user_id', 'slug']);`
- **Check Constraints & Indexes**: Always index columns used in `WHERE`, `ORDER BY`, and foreign keys.

---

## 7. Performance & Optimization Checklist

### 7.1 Prevent N+1 Problems
- Always eager load relations using `with()` or `load()` when serializing or iterating collections.
- Use `Model::shouldBeStrict()` in development to catch N+1 queries immediately.

### 7.2 Select Only Required Columns
When querying large tables or relations, select only the columns needed:
```php
User::query()
    ->select(['id', 'name', 'email'])
    ->get();
```

### 7.3 Large Dataset Processing
Never call `->get()` on thousands of records in memory:
- Use `->chunk(200, function ($records) { ... })` for batched processing.
- Use `->cursor()` for lazy streaming with minimal memory overhead.
- Use `->lazyById()` for chunked iteration with stable pagination.

### 7.4 Prefer Database-Side Processing
Compute aggregates in SQL rather than PHP:
```php
// Fast (Database)
$total = Order::query()->where('user_id', $user->id)->sum('total_amount');

// Slow & Memory Heavy (PHP)
$total = Order::query()->where('user_id', $user->id)->get()->sum('total_amount');
```

### 7.5 Bulk Operations
Use `insert()`, `upsert()`, or `update()` for mass modifications instead of looping over individual models and calling `save()` in a loop.

---

## 8. Caching Strategy
Do not cache indiscriminately. Every cached item must have:
1. A unique, predictable cache key.
2. An explicit TTL (Time-To-Live).
3. A clear cache invalidation strategy (on update/delete).

```php
$stats = Cache::remember("user:{$userId}:stats", now()->addHours(6), function () use ($userId) {
    return Order::calculateStatsForUser($userId);
});
```
Never introduce caching without defining when and how that cache gets cleared.


---

## Reference: 05-concurrency-transactions.md
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


---

## Reference: 06-validation-enums-typing.md
# 06 — Validation, Enums, DTOs & Strict Typing

## 1. Input Validation vs Business Validation

### 1.1 Structural Validation (Form Requests)
Validation of the incoming HTTP request structure belongs solely in a **Form Request**.
- Is the email valid?
- Is the quantity an integer between 1 and 100?
- Is the date in ISO format?

```php
namespace App\Http\Requests\Orders;

use App\Enums\PaymentMethodEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Delegate entity authorization to Policies; return true here unless request-specific auth applies
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:50'],
            'payment_method' => ['required', Rule::enum(PaymentMethodEnum::class)],
            'coupon_code' => ['nullable', 'string', 'max:32'],
        ];
    }
}
```

### 1.2 Business Rule Validation (Actions / Domain)
Business rules belong in the domain layer (**Actions/Services**), not in FormRequests:
- *"Does the user have enough balance?"* $\rightarrow$ **Action**
- *"Has the user exceeded their monthly quota?"* $\rightarrow$ **Action**
- *"Is the product currently out of stock?"* $\rightarrow$ **Action**

```text
FormRequest:  "Is this input syntactically and structurally valid?"
Action:       "Is this operation permitted by current business state and domain rules?"
```

---

## 2. Enums (First-Class Domain Constants)
Use native backed Enums whenever a domain concept represents a finite, known set of states:

```php
namespace App\Enums;

enum OrderStatusEnum: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case PAID = 'paid';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';

    /**
     * Check if the order has reached a terminal state.
     */
    public function isTerminal(): bool
    {
        return match ($this) {
            self::PAID,
            self::CANCELLED,
            self::REFUNDED => true,
            default => false,
        };
    }

    /**
     * Human-readable label for UI / reporting.
     */
    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending Payment',
            self::PROCESSING => 'Processing Order',
            self::PAID => 'Paid & Confirmed',
            self::CANCELLED => 'Cancelled',
            self::REFUNDED => 'Refunded',
        };
    }
}
```
### Enum Rules:
- Case names must always be **UPPERCASE** (`OrderStatusEnum::PAID`).
- Add helper methods (`isTerminal()`, `label()`, `color()`) when domain behavior naturally belongs to the Enum.

---

## 3. Data Transfer Objects (DTOs)
Use DTOs when passing structured, multi-argument business data across application layers:

```php
namespace App\Data\Orders;

use App\Enums\PaymentMethodEnum;
use App\Http\Requests\Orders\StoreOrderRequest;

final readonly class CreateOrderData
{
    public function __construct(
        public int $productId,
        public int $quantity,
        public PaymentMethodEnum $paymentMethod,
        public ?string $couponCode = null,
    ) {}

    public static function fromRequest(StoreOrderRequest $request): self
    {
        return new self(
            productId: (int) $request->validated('product_id'),
            quantity: (int) $request->validated('quantity'),
            paymentMethod: PaymentMethodEnum::from($request->validated('payment_method')),
            couponCode: $request->validated('coupon_code'),
        );
    }
}
```
### DTO Conventions:
- Must be declared `final readonly class`.
- Typed constructor properties.
- Provide a static factory method (`fromRequest(...)`, `fromArray(...)`) for instantiation.

---

## 4. Strict Typing & Modern PHP
- **Zero Ambiguity**: Never use untyped arrays as parameters when an explicit DTO or Model can be passed.
- **Avoid `mixed`**: If unavoidable, document the reason.
- **Match Expressions**: Prefer modern `match()` expressions over lengthy `switch` or nested `if/else` statements.
- **Dates**: Prefer immutable dates (`CarbonImmutable` or `$table->dateTimeTz()` / `immutable_datetime` cast) to eliminate unexpected date mutation side effects.
- **Eliminate Magic Strings**: Never hardcode recurring status strings or configuration values. Use Enums, constants, or `config(...)`.


---

## Reference: 07-testing-and-pest.md
# 07 — Testing Philosophy & Pest Standards

## 1. Testing Philosophy
- **Operational code must be tested**: If code mutates state, charges money, or enforces rules, it must have comprehensive automated test coverage.
- **Prefer Pest**: Pest is the primary testing framework. Use its clean, expressive closure-based syntax.
- **Real Database Testing**: Use the `RefreshDatabase` trait. Do not mock the database or Eloquent. Test with real records created via Factories.
- **Minimal Mocking**: Avoid mocking internal application services. Only mock or fake external boundaries (payment gateways, SMS providers, third-party HTTP endpoints).

---

## 2. Test Suite Organization

```text
tests/
├── Feature/
│   ├── Actions/          # Business logic tests directly invoking Actions
│   │   └── Orders/
│   │       ├── CreateOrderActionTest.php
│   │       └── CancelOrderActionTest.php
│   └── Api/              # HTTP endpoint integration tests (FormRequest, Auth, Status Codes, JSON shape)
│       └── V1/
│           └── OrderControllerTest.php
├── Unit/                 # Pure calculations, DTO transformations, isolated algorithms
└── ArchitectureTest.php  # Pest 3 Architecture tests enforcing coding standards
```

---

## 3. Writing Action Feature Tests
Test Actions directly with realistic dependencies and assertions:

```php
use App\Actions\Orders\CreateOrderAction;
use App\Data\Orders\CreateOrderData;
use App\Enums\OrderStatusEnum;
use App\Enums\PaymentMethodEnum;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates an order with pending status and correct total', function () {
    // Arrange
    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 1000, 'stock' => 10]);

    $data = new CreateOrderData(
        productId: $product->id,
        quantity: 2,
        paymentMethod: PaymentMethodEnum::CREDIT_CARD,
    );

    $action = app(CreateOrderAction::class);

    // Act
    $order = $action->execute($user, $data);

    // Assert
    expect($order)
        ->toBeInstanceOf(\App\Models\Order::class)
        ->status->toBe(OrderStatusEnum::PENDING)
        ->total_amount->toBe(2000);

    $this->assertDatabaseHas('orders', [
        'id' => $order->id,
        'user_id' => $user->id,
        'status' => OrderStatusEnum::PENDING->value,
    ]);
});
```

---

## 4. Testing External APIs with Native Fakes
Never call production APIs in automated tests. Use `Http::fake()`:

```php
use Illuminate\Support\Facades\Http;

it('successfully charges payment via external gateway', function () {
    Http::fake([
        'https://api.stripe.com/v1/charges' => Http::response([
            'id' => 'ch_123456',
            'status' => 'succeeded',
        ], 200),
    ]);

    // Run action that invokes Stripe
    $action = app(ChargePaymentAction::class);
    $result = $action->execute($order);

    expect($result->isSuccess())->toBeTrue();

    Http::assertSent(fn ($request) => str_contains($request->url(), 'charges'));
});
```

---

## 5. Pest 3 Architecture Testing (`arch()`)
Automate Farshid's constitutional rules with Pest Architecture tests in `tests/ArchitectureTest.php`:

```php
// Enforce Action standards
arch('actions')
    ->expect('App\Actions')
    ->classes()
    ->toBeFinal()
    ->toHaveMethod('execute');

// Ban Repositories
arch('strict no-repository rule')
    ->expect('App\Repositories')
    ->not->toBeUsed()
    ->ignoring([]);

// Ensure Controllers do not perform direct database writes without Actions
arch('controllers do not call DB directly')
    ->expect('App\Http\Controllers')
    ->not->toUse(['Illuminate\Support\Facades\DB', 'Illuminate\Database\Eloquent\Builder']);

// Ensure DTOs are final and readonly
arch('dtos are final and readonly')
    ->expect('App\Data')
    ->classes()
    ->toBeFinal()
    ->toBeReadonly();

// Pest preset standards
arch()->preset()->php();
arch()->preset()->laravel();
```

---

## 6. Test Checklist For Every Feature
Before declaring any task done, ensure tests cover:
1. **Happy Path**: Standard successful execution.
2. **Validation Failure**: Missing, invalid, or malformed input.
3. **Authorization Failure**: Unauthorized user receives `403 Forbidden`.
4. **Idempotency**: Executing the action twice produces identical, safe results.
5. **Database State**: Exact database record assertions (`assertDatabaseHas`).


---

## Reference: 08-clean-code-and-red-flags.md
# 08 — Clean Code, Farshid's Red Flags & Definition of Done

## 1. Naming Conventions & Code Style

### 1.1 Intent-Communicating Names
- Never use cryptic abbreviations (`$usr`, `$ord_itm`, `$calc`).
- Names must communicate clear business intent:
  - `CreateSubscriptionAction` (not `SubManager`)
  - `OrderCannotBeCancelledException` (not `OrderError`)
  - `PaymentGatewayInterface` (not `PaymentContract`)

### 1.2 Boolean Naming
Boolean properties, methods, and variables must read as questions:
```php
public function isPaid(): bool;
public function hasActiveSubscription(): bool;
public function canBeRefunded(): bool;
public function shouldRetry(): bool;
```

### 1.3 Guard Clauses & Early Returns
Avoid deep nested `if/else` structures. Return early:

```php
// Good: Guard clauses
public function cancel(Order $order): void
{
    if ($order->isCancelled()) {
        return;
    }

    if (! $order->canBeCancelled()) {
        throw new OrderCannotBeCancelledException();
    }

    $order->markAsCancelled();
}
```

### 1.4 Comments Philosophy
- Do **not** comment obvious lines:
  ```php
  // Bad:
  // Get the order by id
  $order = Order::find($id);
  ```
- Comments should explain **Why**, not **What**:
  ```php
  // Good:
  // We hold inventory reservation for 15 minutes to prevent race conditions during 3D-Secure redirects.
  $reservation->extend(15);
  ```

---

## 2. Farshid's Five Architectural Red Flags
If you see any of the following 5 red flags in code or AI output, reject and refactor immediately:

| # | Red Flag | Why It's Dangerous | Farshid's Required Fix |
| :-: | :--- | :--- | :--- |
| 🚩 **1** | **Repository Pattern by default** | Wraps Eloquent with zero benefit, doubles maintenance overhead | Remove repository; use Eloquent queries directly or via Scopes. |
| 🚩 **2** | **God Service** | Bloated classes (`UserService`) with dozens of unrelated responsibilities | Break into single-purpose `final` Actions (`RegisterUserAction`, etc.). |
| 🚩 **3** | **DB Transaction holding HTTP call** | Holds DB lock open during network round-trip, crashing connection pools | Perform external HTTP call outside the database transaction. |
| 🚩 **4** | **Loose Arrays & Untyped Methods** | Destroys type safety, hides parameter contracts, invites runtime bugs | Use strict parameter types and `final readonly` DTOs. |
| 🚩 **5** | **Missing Idempotency / Race Guards** | Duplicate payments, race conditions on inventory/balance mutations | Add business idempotency check, database constraints, or `lockForUpdate`. |

---

## 3. Farshid's Positive Architectural Signals
Code that meets Farshid's highest standards exhibits:
- ✅ Controllers are 5-15 lines long, delegating to a `final` Action with `execute()`.
- ✅ Database integrity is backed by migrations with foreign key constraints, indexes, and unique keys.
- ✅ Models contain domain-specific helper methods (`isPaid()`, `markAsCancelled()`) but zero external I/O.
- ✅ Modern `protected function casts(): array` is used in Eloquent models.
- ✅ Pest tests assert realistic database states and simulate external APIs with `Http::fake()`.
- ✅ PHPStan / Larastan passes cleanly with zero missing return or parameter types.

---

## 4. Definition of Done (DoD) Checklist
No task is considered complete until all items below are fulfilled:

1. [ ] **Input Validation**: Dedicated `FormRequest` with complete validation rules.
2. [ ] **Authorization**: Model permissions checked via Laravel `Policy`.
3. [ ] **Strict Typing**: 100% typed parameters, properties, and return types across all new classes.
4. [ ] **Model Configuration**: `$guarded = ['id']` and casts declared via `casts(): array`.
5. [ ] **Database Constraints**: Migrations contain proper foreign keys, unique indexes, and defaults.
6. [ ] **Idempotency & Concurrency**: Mutation operations are safe against retries and race conditions.
7. [ ] **Automated Tests**: Pest Feature test written covering happy path and critical failures.
8. [ ] **Code Formatting**: Code passed through Laravel Pint (`php artisan pint`).
9. [ ] **Verification**: Actual test output verified (`php artisan test`) with zero failures.


---

## Reference: 09-ai-agent-protocols.md
# 09 — AI Agent Protocols & Workflow Engine

## 1. Phase 1: Pre-Flight Inspection Protocol
Before writing any code for a non-trivial feature, the AI agent **MUST** perform an automated reconnaissance:

1. **Read `composer.json`**:
   - Determine the exact PHP version (e.g. `^8.3`).
   - Determine the exact Laravel version (e.g. `^11.0`, `^12.0`, `^13.0`).
   - Check installed packages (Pest, Larastan, Sanctum, Cashier, etc.).
2. **Scan Existing Architecture**:
   - Check directory conventions (e.g. `app/Actions`, `app/Services`, `app/Data`).
   - Inspect existing Models, Enums, and FormRequests for styling consistency.
   - Inspect `tests/` structure (Pest vs PHPUnit conventions).
3. **Never Assume Architecture**:
   - Do not invent namespaces or file structures before verifying the repository's existing conventions.

---

## 2. Phase 2: Architecture Proposal Protocol
Before implementing any significant architectural change or new domain feature, the AI must provide a structured **Architecture Proposal** and wait for user approval:

### Proposal Structure:
1. **Requirement Analysis**: Concise statement of what the feature actually needs to accomplish.
2. **Existing Architecture**: Which existing models, services, or actions are affected.
3. **Proposed Architecture**:
   - Controller (thin HTTP endpoint)
   - Form Request (structural validation)
   - Action (business use case with `execute()`)
   - DTO (strongly typed input data)
   - Model & Migration (casts, guarded, foreign keys)
   - Policy (authorization)
   - Events & Jobs (if asynchronous)
4. **Risks Analysis**:
   - Transaction boundaries
   - Concurrency & race condition risks
   - Idempotency & retry risks
   - Performance & N+1 query risks
5. **Implementation Plan & Files To Create/Modify**: Exact list of files.
6. **Testing Plan**: Specific Pest Feature and Unit tests to be added.

---

## 3. Phase 3: Impact Analysis Before Modifying Code
Before modifying any existing Action, Service, or Model:
1. **Grep for all usages**:
   - Controllers invoking the class
   - Queued Jobs and Listeners
   - Artisan Commands
   - Existing automated tests
2. **Preserve Backward Compatibility**: Ensure existing callers do not break when adding new parameters. Use optional parameters or DTO enhancements.

---

## 4. Phase 4: AI Self-Review Rubric
After generating or modifying code, the AI must self-audit against this checklist:
- [ ] Did I introduce a Repository? *(If yes, delete it immediately and use Eloquent)*.
- [ ] Is there an external HTTP call inside a `DB::transaction`? *(If yes, extract it outside)*.
- [ ] Are all parameters and return types strictly typed?
- [ ] Is every new Action declared `final` with an `execute()` method?
- [ ] Are models configured with `protected function casts(): array` and `protected $guarded = ['id']`?
- [ ] Is there any N+1 query risk?
- [ ] Did I write realistic Pest tests?

---

## 5. Phase 5: Verification & Final Report
1. **Execute Real Commands**:
   - Run `php artisan test --filter=...` or `composer test`.
   - Run `php artisan pint` or `./vendor/bin/pint`.
   - Run static analysis if configured (`./vendor/bin/phpstan`).
2. **Report Actual Results**:
   - Never hallucinate or assume tests passed without inspecting terminal output.
   - Present a concise final report:
     - **Changed**: Summary of what was accomplished.
     - **Created Files**: Clickable markdown links to new files.
     - **Modified Files**: Clickable links to modified files.
     - **Architecture Rationale**: Why this approach was chosen.
     - **Test Results**: Actual pass/fail output from terminal commands.


---

## Example: ArchitectureTest.php
```php
<?php

declare(strict_types=1);

// -----------------------------------------------------------------------------
// Pest 3 Architecture Test Suite — Enforcing Farshid's Laravel Standards
// -----------------------------------------------------------------------------

test('all action classes are final and have execute method')
    ->expect('App\Actions')
    ->classes()
    ->toBeFinal()
    ->toHaveMethod('execute');

test('repository pattern is strictly banned')
    ->expect('App\Repositories')
    ->not->toBeUsed();

test('dtos are final and readonly')
    ->expect('App\Data')
    ->classes()
    ->toBeFinal()
    ->toBeReadonly();

test('models extend standard eloquent model and do not call external http')
    ->expect('App\Models')
    ->classes()
    ->toExtend('Illuminate\Database\Eloquent\Model')
    ->not->toUse('Illuminate\Support\Facades\Http');

test('controllers do not call DB transaction directly')
    ->expect('App\Http\Controllers')
    ->not->toUse('Illuminate\Support\Facades\DB');

test('jobs implement shouldQueue')
    ->expect('App\Jobs')
    ->classes()
    ->toImplement('Illuminate\Contracts\Queue\ShouldQueue');

test('strict php code quality preset')
    ->expect('App')
    ->toUseStrictTypes()
    ->not->toUse(['dd', 'dump', 'ray', 'var_dump']);
```


---

## Example: CreateOrderAction.php
```php
<?php

declare(strict_types=1);

namespace App\Actions\Orders;

use App\Data\Orders\CreateOrderData;
use App\Enums\OrderStatusEnum;
use App\Events\OrderCreatedEvent;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class CreateOrderAction
{
    /**
     * Execute the order creation business operation.
     */
    public function execute(User $user, CreateOrderData $data): Order
    {
        // 1. Orchestration & Transaction boundary
        return DB::transaction(function () use ($user, $data) {
            $subtotal = $data->calculateSubtotal();

            // 2. Create Order Aggregate Root
            $order = Order::create([
                'user_id' => $user->id,
                'status' => OrderStatusEnum::PENDING,
                'total_amount' => $subtotal,
                'discount_amount' => 0,
                'payment_method' => $data->paymentMethod,
                'notes' => $data->notes,
                'metadata' => [
                    'coupon' => $data->couponCode,
                ],
            ]);

            // 3. Create Line Items
            foreach ($data->items as $item) {
                $order->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['quantity'] * $item['unit_price'],
                ]);
            }

            // 4. Dispatch domain event for asynchronous side effects (emails, inventory, analytics)
            OrderCreatedEvent::dispatch($order);

            return $order;
        });
    }
}
```


---

## Example: CreateOrderActionTest.php
```php
<?php

declare(strict_types=1);

use App\Actions\Orders\CreateOrderAction;
use App\Data\Orders\CreateOrderData;
use App\Enums\OrderStatusEnum;
use App\Events\OrderCreatedEvent;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

it('creates an order with items and calculates correct subtotal', function () {
    // Arrange
    Event::fake([OrderCreatedEvent::class]);

    $user = User::factory()->create();
    $productA = Product::factory()->create(['price' => 1500]);
    $productB = Product::factory()->create(['price' => 2500]);

    $data = new CreateOrderData(
        userId: $user->id,
        items: [
            ['product_id' => $productA->id, 'quantity' => 2, 'unit_price' => 1500],
            ['product_id' => $productB->id, 'quantity' => 1, 'unit_price' => 2500],
        ],
        paymentMethod: 'credit_card',
        couponCode: 'WELCOME10',
        notes: 'Please leave at door.',
    );

    $action = app(CreateOrderAction::class);

    // Act
    $order = $action->execute($user, $data);

    // Assert
    expect($order)
        ->toBeInstanceOf(Order::class)
        ->user_id->toBe($user->id)
        ->status->toBe(OrderStatusEnum::PENDING)
        ->total_amount->toBe(5500); // (2 * 1500) + (1 * 2500)

    $this->assertDatabaseHas('orders', [
        'id' => $order->id,
        'user_id' => $user->id,
        'status' => OrderStatusEnum::PENDING->value,
        'total_amount' => 5500,
    ]);

    $this->assertDatabaseCount('order_items', 2);

    Event::assertDispatched(OrderCreatedEvent::class, function (OrderCreatedEvent $event) use ($order) {
        return $event->order->id === $order->id;
    });
});
```


---

## Example: CreateOrderData.php
```php
<?php

declare(strict_types=1);

namespace App\Data\Orders;

use App\Http\Requests\Orders\StoreOrderRequest;

final readonly class CreateOrderData
{
    /**
     * @param array<int, array{product_id: int, quantity: int, unit_price: int}> $items
     */
    public function __construct(
        public int $userId,
        public array $items,
        public string $paymentMethod,
        public ?string $couponCode = null,
        public ?string $notes = null,
    ) {}

    public static function fromRequest(StoreOrderRequest $request): self
    {
        return new self(
            userId: (int) $request->user()->id,
            items: (array) $request->validated('items'),
            paymentMethod: (string) $request->validated('payment_method'),
            couponCode: $request->validated('coupon_code'),
            notes: $request->validated('notes'),
        );
    }

    /**
     * Calculate the gross subtotal for all order items.
     */
    public function calculateSubtotal(): int
    {
        return array_reduce(
            $this->items,
            fn (int $carry, array $item) => $carry + ($item['quantity'] * $item['unit_price']),
            0,
        );
    }
}
```


---

## Example: Order.php
```php
<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OrderStatusEnum;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Order extends Model
{
    use HasFactory;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = ['id'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => OrderStatusEnum::class,
            'total_amount' => 'integer',
            'discount_amount' => 'integer',
            'is_paid' => 'boolean',
            'metadata' => 'array',
            'paid_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }

    /**
     * User who owns the order.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Line items associated with this order.
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // -------------------------------------------------------------------------
    // Domain State Methods (Model-Owned Logic)
    // -------------------------------------------------------------------------

    public function isPaid(): bool
    {
        return $this->status === OrderStatusEnum::PAID;
    }

    public function isPending(): bool
    {
        return $this->status === OrderStatusEnum::PENDING;
    }

    public function canBeCancelled(): bool
    {
        return $this->isPending() && ! $this->isPaid();
    }

    public function markAsPaid(string $paymentReference): void
    {
        $this->update([
            'status' => OrderStatusEnum::PAID,
            'is_paid' => true,
            'payment_reference' => $paymentReference,
            'paid_at' => CarbonImmutable::now(),
        ]);
    }

    public function markAsCancelled(): void
    {
        $this->update([
            'status' => OrderStatusEnum::CANCELLED,
        ]);
    }
}
```


---

## Example: OrderPolicy.php
```php
<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

final class OrderPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the specific order.
     */
    public function view(User $user, Order $order): bool
    {
        return $user->id === $order->user_id || $user->tokenCan('orders:read-all');
    }

    /**
     * Determine whether the user can create an order.
     */
    public function create(User $user): bool
    {
        return $user->hasVerifiedEmail();
    }

    /**
     * Determine whether the user can cancel the order.
     */
    public function cancel(User $user, Order $order): bool
    {
        return $user->id === $order->user_id && $order->canBeCancelled();
    }
}
```


---

## Example: OrderStatusEnum.php
```php
<?php

declare(strict_types=1);

namespace App\Enums;

enum OrderStatusEnum: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case PAID = 'paid';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';

    /**
     * Determine if the order has reached a terminal state.
     */
    public function isTerminal(): bool
    {
        return match ($this) {
            self::PAID,
            self::CANCELLED,
            self::REFUNDED => true,
            default => false,
        };
    }

    /**
     * Get a human-readable display label.
     */
    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending Payment',
            self::PROCESSING => 'Processing Order',
            self::PAID => 'Paid & Confirmed',
            self::CANCELLED => 'Order Cancelled',
            self::REFUNDED => 'Payment Refunded',
        };
    }

    /**
     * Get the badge color identifier for frontend UI.
     */
    public function badgeColor(): string
    {
        return match ($this) {
            self::PENDING => 'amber',
            self::PROCESSING => 'blue',
            self::PAID => 'emerald',
            self::CANCELLED => 'rose',
            self::REFUNDED => 'slate',
        };
    }
}
```


---

## Example: ProcessWebhookJob.php
```php
<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Actions\Payments\ProcessPaymentWebhookAction;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

final class ProcessWebhookJob implements ShouldQueue
{
    use Queueable;

    /**
     * Maximum retry attempts before routing to failed_jobs.
     */
    public int $tries = 3;

    /**
     * Exponential retry backoff schedule in seconds.
     *
     * @var array<int, int>
     */
    public array $backoff = [10, 30, 60];

    /**
     * Maximum number of unhandled exceptions allowed.
     */
    public int $maxExceptions = 2;

    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        public readonly string $gateway,
        public readonly array $payload,
    ) {}

    /**
     * Execute the job: delegate directly to the dedicated Action.
     */
    public function handle(ProcessPaymentWebhookAction $action): void
    {
        $action->execute($this->gateway, $this->payload);
    }

    /**
     * Handle unrecoverable job failure.
     */
    public function failed(?Throwable $exception): void
    {
        Log::channel('critical')->error('Payment webhook processing permanently failed', [
            'gateway' => $this->gateway,
            'payload' => $this->payload,
            'exception' => $exception?->getMessage(),
        ]);
    }
}
```


---

## Example: StoreOrderRequest.php
```php
<?php

declare(strict_types=1);

namespace App\Http\Requests\Orders;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;

final class StoreOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Delegate entity authorization to OrderPolicy
        return $this->user()?->can('create', Order::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:100'],
            'items.*.unit_price' => ['required', 'integer', 'min:0'],
            'payment_method' => ['required', 'string', 'in:credit_card,wallet,bank_transfer'],
            'coupon_code' => ['nullable', 'string', 'max:32'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
```
