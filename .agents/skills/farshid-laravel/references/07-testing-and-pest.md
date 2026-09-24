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
