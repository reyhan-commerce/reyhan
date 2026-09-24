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
