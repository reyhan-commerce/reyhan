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
